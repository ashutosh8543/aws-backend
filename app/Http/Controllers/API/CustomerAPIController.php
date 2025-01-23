<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerCollection;
use App\Http\Resources\CustomerResource;
use App\Imports\CustomerImport;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Suppervisor;
use App\Models\SalesPayment;
use App\Repositories\CustomerRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Validator\Exceptions\ValidatorException;
use Auth;
use App\Models\AssignCustomersList;
/**
 * Class CustomerAPIController
 */
class CustomerAPIController extends AppBaseController
{
    /** @var CustomerRepository */
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    // public function index(Request $request): CustomerCollection
    // {
    //     $perPage = getPageSize($request);
    //     $user_details=Auth::user();
    //     $perPage = getPageSize($request);
    //     $datefilter=$request->get('filterDate');
    //     $customerslist="";
    //     if($datefilter){
    //         $customerslist = AssignCustomersList::whereDate('assigned_date',$datefilter)->pluck('customer_id')->toArray();
    //     }
    //     if($user_details->role_id==1 || $user_details->role_id==2){
    //        $customers = $this->customerRepository->paginate($perPage);
    //     }
    //     if($user_details->role_id==3){
    //         $customers = $this->customerRepository->where('user_id',$user_details->id)->paginate($perPage);
    //     }else{
    //         $customers = $this->customerRepository->paginate($perPage);
    //     }

    //     if($customerslist){
    //         $customers = $this->customerRepository->whereNotIn('id',$customerslist)->paginate($perPage);
    //     }

    //     CustomerResource::usingWithCollection();

    //     return new CustomerCollection($customers);
    // }


    public function index(Request $request): CustomerCollection
    {
        $perPage = getPageSize($request);
        $user_details = Auth::user();
        $loginUserId = Auth::id();
        $datefilter = $request->get('filterDate');
        $customerslist = [];

        if ($datefilter) {
            $customerslist = AssignCustomersList::whereDate('assigned_date', $datefilter)->pluck('customer_id')->toArray();
        }

        $country = $user_details->country;
        // dd($country);

        $query = $this->customerRepository->with(['areaDetails.region', 'countryDetails']);

        // $countryCodes = [
        //     'Taiwan' => 214,
        //     'Indonesia' => 102,
        // ];


        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $query = $query->where('country', $country );
        }

        if ($user_details->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if($distributor){
                $distributor_id = $distributor->id;
                $country = $distributor->country;
                $query = $query->where('user_id', $distributor_id )
                ->where('country', $country );
            }

        }

        if ($user_details->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $area_id = $warehouse->area;
                $countryCode = $warehouse->country;
                // dd($countryCode);

                $customerIds = Customer::where('area_id', $area_id)
                ->where('country', $country)
                ->pluck('id');
            }

            if ($customerIds->isNotEmpty()) {
                $customers = $query->whereIn('id', $customerIds)->latest()->paginate($perPage);
            }
        }



        if ($user_details->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            // dd($supervisor);
            if($supervisor){
             $ware_id = $supervisor->ware_id;
             $country = $supervisor->country;

            $warehouse = Warehouse::where('ware_id', $ware_id)
            ->where('country', $country)
            ->first();

            if($warehouse){
                $area_id = $warehouse->area;
                $country = $warehouse->country;

                $customerIds = Customer::where('area_id', $area_id)
                ->where('country', $country)
                ->pluck('id');
                // dd($customerIds);
                }
            }

            if($customerIds-> isNotEmpty()){
              $customers = $query->whereIn('id', $customerIds)->latest()->paginate($perPage);
            }
        }

        if ($customerslist) {
            $query->whereNotIn('id', $customerslist);
        }

        $customers = $query->paginate($perPage);

        CustomerResource::usingWithCollection();

        return new CustomerCollection($customers);
    }



    public function customerList(Request $request): CustomerCollection
    {
        $user_details = Auth::user();
        $loginUserId = Auth::id();
        $datefilter = $request->get('filterDate');
        $customerslist = [];

        if ($datefilter) {
            $customerslist = AssignCustomersList::whereDate('assigned_date', $datefilter)->pluck('customer_id')->toArray();
        }

        $country = $user_details->country;
        // dd($country);

        $query = $this->customerRepository->with(['areaDetails.region', 'countryDetails','assignCustomerList'])
        ->where('status', 1);

        // $countryCodes = [
        //     'Taiwan' => 214,
        //     'Indonesia' => 102,
        // ];


        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $query = $query->where('country', $country );
        } elseif ($user_details->role_id == 3) {
          $query = $query->where('country', $country);
        }

        if ($user_details->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $area_id = $warehouse->area;
                $countryCode = $warehouse->country;
                // dd($countryCode);

                $customerIds = Customer::where('area_id', $area_id)
                ->where('country', $country)
                ->pluck('id');
                // dd($customerIds);
            }

            if ($customerIds->isNotEmpty()) {
                $customers = $query->whereIn('id', $customerIds)->latest()->get();
            }
        }



        if ($user_details->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            // dd($supervisor);
            if($supervisor){
             $ware_id = $supervisor->ware_id;
             $country = $supervisor->country;

            $warehouse = Warehouse::where('ware_id', $ware_id)
            ->where('country', $country)
            ->first();

            if($warehouse){
                $area_id = $warehouse->area;
                $country = $warehouse->country;

                $customerIds = Customer::where('area_id', $area_id)
                ->where('country', $country)
                ->pluck('id');
                // dd($customerIds);
                }
            }

            if($customerIds-> isNotEmpty()){
              $customers = $query->whereIn('id', $customerIds)->latest()->get();
            }
        }

        if ($customerslist) {
            $query->whereNotIn('id', $customerslist);
        }

        $customers = $query->get();

        CustomerResource::usingWithCollection();

        return new CustomerCollection($customers);
    }




    /**
     * @throws ValidatorException
     */
    public function store(CreateCustomerRequest $request): CustomerResource
    {
        $input = $request->all();

        $input['added_by']=Auth::user()->id;


        if($input['image']){

            $extension = explode('/', mime_content_type($input['image']))[1];


            $image =$input['image'];  // your base64 encoded
            $image = str_replace('data:image/'.$extension.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = rand().'.'.$extension;
            $destinationPath = 'outlets';
            $input['image']=url('public/outlets'.'/'. $imageName);
            \File::put(public_path($destinationPath).'/' . $imageName, base64_decode($image));
       }

        if (! empty($input['dob'])) {
            $input['dob'] = $input['dob'] ?? date('Y/m/d');
        }

        // Debugging: Log the full input data before creating the customer
        // \Log::info('Creating Customer with data:', $input);

        $customer = $this->customerRepository->create($input);

        if(!empty($customer)){
            $customer->update(['unique_code'=>"CUS#".$customer->id]);
        }

        return new CustomerResource($customer);
    }

    // public function show($id): CustomerResource
    // {
    //     $customer = $this->customerRepository->find($id);

    //     return new CustomerResource($customer);
    // }

    public function show($id): CustomerResource
    {
    $customer = $this->customerRepository->find($id)->load(['distributor', 'warehouse', 'regionDetails']);

    return new CustomerResource($customer);
   }


    /**
     * @throws ValidatorException
     */
    public function update(UpdateCustomerRequest $request, $id): CustomerResource
    {
        $input = $request->all();
        if($input['image']){

            $extension = explode('/', mime_content_type($input['image']))[1];
            $image =$input['image'];  // your base64 encoded
            $image = str_replace('data:image/'.$extension.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = rand().'.'.$extension;
            $destinationPath = 'outlets';
            $input['image']=url('public/outlets'.'/'. $imageName);
            \File::put(public_path($destinationPath).'/' . $imageName, base64_decode($image));
       }

        if (! empty($input['dob'])) {
            $input['dob'] = $input['dob'] ?? date('Y/m/d');
        }
        $customer = $this->customerRepository->update($input, $id);

        return new CustomerResource($customer);
    }

    public function destroy($id): JsonResponse
    {
        if (getSettingValue('default_customer') == $id) {
            return $this->SendError('Default customer can\'t be deleted');
        }
        $this->customerRepository->delete($id);

        return $this->sendSuccess('Customer deleted successfully');
    }

    public function bestCustomersPdfDownload(): JsonResponse
    {
        $month = Carbon::now()->month;
        $topCustomers = Customer::leftJoin('sales', 'customers.id', '=', 'sales.customer_id')
            ->whereMonth('date', $month)
            ->select('customers.*', DB::raw('sum(sales.grand_total) as grand_total'))
            ->groupBy('customers.id')
            ->orderBy('grand_total', 'desc')
            ->latest()
            ->take(5)
            ->withCount('sales')
            ->get();

        $data = [];

        if (Storage::exists('pdf/best-customers.pdf')) {
            Storage::delete('pdf/best-customers.pdf');
        }

        $companyLogo = getLogoUrl();

        // $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        $pdf = PDF::loadView('pdf.best-customers-pdf', compact('topCustomers', 'companyLogo'))->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        Storage::disk(config('app.media_disc'))->put('pdf/best-customers.pdf', $pdf->output());
        $data['best_customers_pdf_url'] = Storage::url('pdf/best-customers.pdf');

        return $this->sendResponse($data, 'pdf retrieved Successfully');
    }

    public function pdfDownload(Customer $customer): JsonResponse
    {
        $customer = $customer->load('sales.payments');

        $salesData = [];

        $salesData['totalSale'] = $customer->sales->count();

        $salesData['totalAmount'] = $customer->sales->sum('grand_total');

        $salesData['totalPaid'] = 0;

        foreach ($customer->sales as $sale) {
            $salesData['totalPaid'] = $salesData['totalPaid'] + $sale->payments->sum('amount');
        }

        $salesData['totalSalesDue'] = $salesData['totalAmount'] - $salesData['totalPaid'];

        $data = [];

        if (Storage::exists('pdf/customers-report-'.$customer->id.'.pdf')) {
            Storage::delete('pdf/customers-report-'.$customer->id.'.pdf');
        }

        $companyLogo = getLogoUrl();

        // $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        $pdf = PDF::loadView('pdf.customers-report-pdf', compact('customer', 'companyLogo', 'salesData'))->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        Storage::disk(config('app.media_disc'))->put('pdf/customers-report-'.$customer->id.'.pdf', $pdf->output());
        $data['customers_report_pdf_url'] = Storage::url('pdf/customers-report-'.$customer->id.'.pdf');

        return $this->sendResponse($data, 'pdf retrieved Successfully');
    }

    public function customerSalesPdfDownload(Customer $customer): JsonResponse
    {
        $customer = $customer->load('sales.payments');

        $data = [];

        if (Storage::exists('pdf/customer-sales-'.$customer->id.'.pdf')) {
            Storage::delete('pdf/customer-sales-'.$customer->id.'.pdf');
        }

        $companyLogo = getLogoUrl();

        // $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        $pdf = PDF::loadView('pdf.customer-sales-pdf', compact('customer', 'companyLogo'))->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        Storage::disk(config('app.media_disc'))->put('pdf/customer-sales-'.$customer->id.'.pdf', $pdf->output());
        $data['customers_sales_pdf_url'] = Storage::url('pdf/customer-sales-'.$customer->id.'.pdf');

        return $this->sendResponse($data, 'pdf retrieved Successfully');
    }

    public function customerQuotationsPdfDownload(Customer $customer): JsonResponse
    {
        $customer = $customer->load('quotations');

        $data = [];

        if (Storage::exists('pdf/customer-quotations-'.$customer->id.'.pdf')) {
            Storage::delete('pdf/customer-quotations-'.$customer->id.'.pdf');
        }

        $companyLogo = getLogoUrl();

        // $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        $pdf = PDF::loadView('pdf.customer-quotations-pdf', compact('customer', 'companyLogo'))->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        Storage::disk(config('app.media_disc'))->put('pdf/customer-quotations-'.$customer->id.'.pdf', $pdf->output());
        $data['customers_quotations_pdf_url'] = Storage::url('pdf/customer-quotations-'.$customer->id.'.pdf');

        return $this->sendResponse($data, 'pdf retrieved Successfully');
    }

    public function customerReturnsPdfDownload(Customer $customer): JsonResponse
    {
        $customer = $customer->load('salesReturns');

        $data = [];

        if (Storage::exists('pdf/customer-returns-'.$customer->id.'.pdf')) {
            Storage::delete('pdf/customer-returns-'.$customer->id.'.pdf');
        }

        $companyLogo = getLogoUrl();

        // $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        $pdf = PDF::loadView('pdf.customer-returns-pdf', compact('customer', 'companyLogo'))->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        Storage::disk(config('app.media_disc'))->put('pdf/customer-returns-'.$customer->id.'.pdf', $pdf->output());
        $data['customers_returns_pdf_url'] = Storage::url('pdf/customer-returns-'.$customer->id.'.pdf');

        return $this->sendResponse($data, 'pdf retrieved Successfully');
    }

    public function customerPaymentsPdfDownload($id): JsonResponse
    {
        $saleIds = [];

        $sales = Sale::whereCustomerId($id)->get();

        foreach ($sales as $sale) {
            $saleIds[] = $sale->id;
        }

        $payments = SalesPayment::whereIn('sale_id', $saleIds)->with('sale')->get();

        $data = [];

        if (Storage::exists('pdf/customer-payments-'.$id.'.pdf')) {
            Storage::delete('pdf/customer-payments-'.$id.'.pdf');
        }

        $companyLogo = getLogoUrl();

        // $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        $pdf = PDF::loadView('pdf.customer-payments-pdf', compact('payments', 'companyLogo'))->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        Storage::disk(config('app.media_disc'))->put('pdf/customer-payments-'.$id.'.pdf', $pdf->output());
        $data['customers_payments_pdf_url'] = Storage::url('pdf/customer-payments-'.$id.'.pdf');

        return $this->sendResponse($data, 'pdf retrieved Successfully');
    }

    public function importCustomers(Request $request)
    {
        Excel::import(new CustomerImport(), request()->file('file'));

        return $this->sendSuccess('Customers imported successfully');
    }


    public function changeActiveStatus($id): UserResource
    {
     $users = User::findOrFail($id);
     $userDetails = Auth::user();

     // Toggle the user's status
     $status = !$users->status;
     $users->update(['status' => $status]);

     if ($users->role_id == 4) {
         Warehouse::where('ware_id', $users->id)->update(['status' => $status]);
     } elseif ($users->role_id == 5) {
         Suppervisor::where('supervisor_id', $users->id)->update(['status' => $status]);
     } elseif ($users->role_id == 6) {
         Salesman::where('salesman_id', $users->id)->update(['status' => $status]);
     }

     return new UserResource($users);
 }
}
