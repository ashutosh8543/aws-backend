<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleCollection;
use App\Http\Resources\SaleResource;
use App\Models\Customer;
use App\Models\Hold;
use App\Models\Sale;
use App\Models\User;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Models\Suppervisor;
use App\Models\Salesman;
use App\Repositories\SaleRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class SaleAPIController
 */
class SaleAPIController extends AppBaseController
{
    /** @var saleRepository */
    private $saleRepository;

    public function __construct(SaleRepository $saleRepository)
    {
        $this->saleRepository = $saleRepository;
    }

    public function index(Request $request): SaleCollection
    {
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
        $loginUserId = Auth::id();
        $user_details = Auth::user();


        $customer = (Customer::where('name', 'LIKE', "%$search%")->get()->count() != 0);
        $warehouse = (Warehouse::where('name', 'LIKE', "%$search%")->get()->count() != 0);

        $sales = $this->saleRepository;
        if ($customer || $warehouse) {
            $sales->whereHas('customer', function (Builder $q) use ($search, $customer) {
                if ($customer) {
                    $q->where('name', 'LIKE', "%$search%");
                }
            })->whereHas('warehouse', function (Builder $q) use ($search, $warehouse) {
                if ($warehouse) {
                    $q->where('name', 'LIKE', "%$search%");
                }
            });
        }



         // Role-based filtering
        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $country = $user_details->country;
            $sales->where('country', $country);

        } elseif ($user_details->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if($distributor){
               $distributor_id = $distributor->id;
               $country = $distributor->country;
               $salesmanIds = Salesman::where('distributor_id', $distributor_id)
                ->where('distributor_id', $distributor_id)
                ->where('country', $country)
                ->pluck('salesman_id');
                $sales->whereIn('salesman_id',  $salesmanIds);
            }
        } elseif ($user_details->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if($warehouse){
             $warehouse_id = $warehouse->id;
             $country = $warehouse->country;
             $sales->where('warehouse_id',  $warehouse_id);
            }

        }elseif($user_details->role_id == 5){
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if($supervisor){
                $country = $supervisor->country;
                $ware_id = $supervisor->ware_id;
                // dd($ware_id);
            $warehouse = Warehouse::where('ware_id', $ware_id)
            ->where('country', $country)
            ->first();

            $warehouse_id = $warehouse->id;
            $sales->where('warehouse_id',  $warehouse_id);

            }

        }
         else {
            $sales->where('user_id', $user_details->id);
        }

        if ($request->get('start_date') && $request->get('end_date')) {
            $sales->whereBetween('date', [$request->get('start_date'), $request->get('end_date')]);
        }

        if ($request->get('warehouse_id')) {
            $sales->where('warehouse_id', $request->get('warehouse_id'));
        }

        if ($request->get('customer_id')) {
            $sales->where('customer_id', $request->get('customer_id'));
        }

        if ($request->get('status') && $request->get('status') != 'null') {
            $sales->Where('status', $request->get('status'));
        }

        if ($request->get('payment_status') && $request->get('payment_status') != 'null') {
            $sales->where('payment_status', $request->get('payment_status'));
        }

        if ($request->get('payment_type') && $request->get('payment_type') != 'null') {
            $sales->where('payment_type', $request->get('payment_type'));
        }

        $sales = $sales->paginate($perPage);

        SaleResource::usingWithCollection();

        return new SaleCollection($sales);
    }



    public function store(CreateSaleRequest $request): SaleResource
    {
        if (isset($request->hold_ref_no)) {
            $holdExist = Hold::whereReferenceCode($request->hold_ref_no)->first();
            if (!empty($holdExist)) {
                $holdExist->delete();
            }
        }
        $input = $request->all();

        $sale = $this->saleRepository->storeSale($input);

        return new SaleResource($sale);
    }

    public function show($id): SaleResource
    {
        $sale = $this->saleRepository->find($id);

        return new SaleResource($sale);
    }

    public function edit(Sale $sale): SaleResource
    {
        // try{
        $sale = $sale->load('saleItems.product', 'warehouse');

        return new SaleResource($sale);
        // }catch(\Exception $e){
        //     dd($e->getMessage().$e->getLine());
        // }
    }

    public function update(UpdateSaleRequest $request, $id): SaleResource
    {
        $input = $request->all();
        $sale = $this->saleRepository->updateSale($input, $id);

        return new SaleResource($sale);
    }

    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $sale = $this->saleRepository->with('saleItems')->where('id', $id)->first();
            foreach ($sale->saleItems as $saleItem) {
                manageStock($sale->warehouse_id, $saleItem['product_id'], $saleItem['quantity']);
            }
            if (File::exists(Storage::path('sales/barcode-' . $sale->reference_code . '.png'))) {
                File::delete(Storage::path('sales/barcode-' . $sale->reference_code . '.png'));
            }
            $this->saleRepository->delete($id);
            DB::commit();

            return $this->sendSuccess('Sale Deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function pdfDownload(Sale $sale): JsonResponse
    {
        $sale = $sale->load('customer', 'saleItems.product', 'payments');
        $data = [];
        if (Storage::exists('pdf/Sale-' . $sale->reference_code . '.pdf')) {
            Storage::delete('pdf/Sale-' . $sale->reference_code . '.pdf');
        }
        $companyLogo = getLogoUrl();

        $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        $pdf = PDF::loadView('pdf.sale-pdf', compact('sale', 'companyLogo'))->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        Storage::disk(config('app.media_disc'))->put('pdf/Sale-' . $sale->reference_code . '.pdf', $pdf->output());
        $data['sale_pdf_url'] = Storage::url('pdf/Sale-' . $sale->reference_code . '.pdf');

        return $this->sendResponse($data, 'pdf retrieved Successfully');
    }

    public function saleInfo(Sale $sale): JsonResponse
    {
        try{
        $sale = $sale->load('saleItems', 'warehouse', 'customer','customer.channelDetails','salesmanDetails','salesmanDetails.areaDetails');
        $keyName = [
            'email', 'company_name', 'phone', 'address',
        ];
        $sale['company_info'] = Setting::whereIn('key', $keyName)->pluck('value', 'key')->toArray();

        return $this->sendResponse($sale, 'Sale information retrieved successfully');
       }catch(\Exception $e){
        throw new UnprocessableEntityHttpException($e->getMessage());

       }
    }
    public function getSaleProductReport(Request $request): SaleCollection
    {
        $perPage = getPageSize($request);
        $productId = $request->get('product_id');
        $warehouse_id = $request->get('warehouse_id');
        $sales = $this->saleRepository->whereHas('saleItems', function ($q) use ($productId,$warehouse_id) {
            $q->where('product_id', '=', $productId)
             ->where('warehouse_id',$warehouse_id);
        })->with(['saleItems', 'warehouse', 'customer','salesmanDetails','salesmanDetails.areaDetails']);

        $sales = $sales->paginate($perPage);

        SaleResource::usingWithCollection();

        return new SaleCollection($sales);
    }

    public function getProductSalesReport(Request $request): SaleCollection
    {
        $perPage = getPageSize($request);
        $productId = $request->get('product_id');
        $warehouse_id = $request->get('warehouse_id');
        $sales = $this->saleRepository->whereHas('saleItems', function ($q) use ($productId) {
            $q->where('product_id', '=', $productId);
        })->with(['saleItems', 'warehouse', 'customer','salesmanDetails','salesmanDetails.areaDetails']);

        $sales = $sales->paginate($perPage);

        SaleResource::usingWithCollection();

        return new SaleCollection($sales);
    }
}
