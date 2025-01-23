<?php

namespace App\Http\Controllers\API\M1;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleCollection;
use App\Http\Resources\SaleResource;
use App\Models\Customer;
use App\Models\Hold;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Repositories\SaleRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Validator;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\SaleReturn;
use App\Models\Salesman;
use App\Models\GiftSubmit;
use App\Models\GiftItems;

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
        $customer_id= $request->get('outlet');
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
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

        if($customer_id){
            $sales->where('customer_id',$customer_id);
        }

        $sales = $sales->paginate($perPage);


        SaleResource::usingWithCollection();

        return new SaleCollection($sales);
    }

    // public function store(CreateSaleRequest $request): SaleResource
    // {
    //     if (isset($request->hold_ref_no)) {
    //         $holdExist = Hold::whereReferenceCode($request->hold_ref_no)->first();
    //         if (! empty($holdExist)) {
    //             $holdExist->delete();

    //         }
    //     }
    //     $input = $request->all();

    //     $input['shipping']=0;

    //     $sale = $this->saleRepository->storeSale($input);

    //     return new SaleResource($sale);
    // }


    public function store(CreateSaleRequest $request): SaleResource
   {

    if (isset($request->hold_ref_no)){
        $holdExist = Hold::whereReferenceCode($request->hold_ref_no)->first();
        if (!empty($holdExist)) {
            $holdExist->delete();
        }
        $input = $request->all();
        
        if ($request->has('salesman_id')) {
            $input['salesman_id'] = $request->salesman_id;
        }       
        $input['shipping']=0;
        $input['country']=0;
        $sale = $this->saleRepository->storeSale($input);
        return new SaleResource($sale);
    }
    $input = $request->all();

    if($input['image']){  
               
        $extension = explode('/', mime_content_type($input['image']))[1];               


        $image =$input['image'];  // your base64 encoded
        $image = str_replace('data:image/'.$extension.';base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = rand().'.'.$extension;
        $destinationPath = '/uploads/sales';    
        $input['image']=url('/uploads/sales'.'/'. $imageName);

        \File::put(public_path($destinationPath).'/' . $imageName, base64_decode($image));                

   }

    // Set default shipping value
    $input['shipping'] = 0;
    if (isset($request->salesman_id)) {
        $input['salesman_id'] = $request->salesman_id;
        $input['country']=Salesman::where('salesman_id',$request->salesman_id)->first()->country??'';
    }
    $sale = $this->saleRepository->storeSale($input);
    return new SaleResource($sale);
   }



    // public function show($id): SaleResource
    // {
    //     $sale = $this->saleRepository->find($id);
    //     return new SaleResource($sale);
    // }


    public function show($id): SaleResource
    {
    $sale = $this->saleRepository->find($id);

    $sale->load('salesman');

    return new SaleResource($sale);
   }

   public function getSalesBySalesman(Request $request ,$salesman_id= null)
   {
    $customer_id= $request->get('outlet');
    $perPage = getPageSize($request);   
    $search = $request->filter['search'] ?? '';
    $customer = (Customer::where('name', 'LIKE', "%$search%")->get()->count() != 0);
    $warehouse = (Warehouse::where('name', 'LIKE', "%$search%")->get()->count() != 0);
    $number=$request->get('page')??1;
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

    if($customer_id){
        $sales->where('customer_id',$customer_id);
    }

    if($salesman_id){
        $sales->where('salesman_id',$salesman_id);
    }

    $sales = $sales->latest()->paginate($perPage*$number);
    
   

    SaleResource::usingWithCollection();

    return new SaleCollection($sales);
   }
  
   public function edit(Sale $sale): SaleResource
   {
       $sale = $sale->load('saleItems.product.stocks', 'warehouse');

       return new SaleResource($sale);
   }

   public function getDetailsForInvoice(Request $request)
   {
    
    $validator = Validator::make($request->all(),[
        'id' => 'required',
        'type'=>'required',
    ]);
    if($validator->fails()){
        return response()->json([
            'errors' => $validator->errors()->first(),
            'success' => false,
        ], 422);
    }

    $type=$request->type;
    $id=$request->id;
    if($type=="sale"){
    $sale = Sale::with(['salesmanDetails'=>function($query){
        $query->select(['id','first_name','last_name','email','phone','distributor_id']);
    },'customer'=>function($query){
        $query->select(['id','name','email','phone','address','city','credit_limit']);
    }])
    ->find($id)->toArray();
    
    $saleitems=SaleItem::where('sale_id',$sale['id'])->get();
}
    if($type=="return"){      
        
        $sale = SaleReturn::with(['salesmanDetails'=>function($query){
            $query->select(['id','first_name','last_name','email','phone','distributor_id']);
        },'customer'=>function($query){
            $query->select(['id','name','email','phone','address','city','credit_limit']);
        }])
        ->find($id)->toArray();        
        $saleitems=SaleReturnItem::where('sale_return_id',$sale['id'])->get();  

     }
     if($type=="gift"){
        $sale=GiftSubmit::with(['salesman_details','customer'])->where('id',$id)->first();
        $saledistributor=Salesman::with(['salesmanDistributor'])->where('salesman_id',$sale['sales_man_id'])->first();

     }

    if($type=="sale" || $type=="return"){
         $saledistributor=Salesman::with(['salesmanDistributor'])->where('salesman_id',$sale['salesman_id'])->first();
    }


    $data['distributor_name']=$saledistributor['salesmanDistributor']['first_name'].' '.$saledistributor['salesmanDistributor']['last_name'];
    $data['distributor_address']=$saledistributor['salesmanDistributor']['address'];
    
    $products=[];
    if($type!="gift"){
    foreach($saleitems as $k=>$value){
        $products[$k]['name']=$value['product_id'][0]['name'];
        $products[$k]['cn_name']=$value['product_id'][0]['cn_name'];
        $products[$k]['bn_name']=$value['product_id'][0]['bn_name'];
        $products[$k]['product_unit_name']=$value['product_id'][0]['product_unit_name']['name'];
        $products[$k]['quantity']=$value['quantity'];
        $products[$k]['product_price']=number_format($value['product_price'],2);
        $products[$k]['sub_total']=number_format($value['sub_total'],2);
        $products[$k]['discount_amount']=number_format($value['discount_amount'],2);
    }
    $payment_type="";
    if($sale['payment_type'] == 1){
        $payment_type="Cash";
    }
    if($sale['payment_type'] == 2){
        $payment_type="Cheque";
    }
    if($sale['payment_type'] == 3){
        $payment_type="Bank transfer";
    }
    if($sale['payment_type'] == 4){
        $payment_type="Other";
    }
    if($sale['payment_type'] == 5){
        $payment_type="Credit Limit";
    }
    
         $payment_status="";
      if($type=="sale"){
            if($sale['payment_status']==1){
                $payment_status="Paid";
            }

            if($sale['payment_status']==1){
                $payment_status="Unpaid";
            }
        }
        $data['reference_code']=$sale['reference_code'];
   
    
    $data['id']=$sale['id'];
    $data['date']=date('d-m-Y',strtotime($sale['date']));
    $data['coupon_code']="";
    $data['discount']=number_format($sale['discount'],2);
    $data['grand_total']=number_format($sale['grand_total'],2);
    $data['paid_amount']=number_format($sale['paid_amount'],2);
    $data['payment_type']= $payment_type;
    if($type=="sale"){
      $data['payment_status']=$payment_status;
    }
}else{
    $data['reference_code']=$sale['unique_id'];
    $data['date']=$sale['uploaded_date'];
    $giftItem=GiftItems::where('submited_gift_id',$sale->id)->get();
    
    foreach($giftItem as $key=>$gift){
               $gifts=$gift->gift_details;
            $products[$key]['name']=$gifts['title']??'';
            $products[$key]["cn_name"]= $gifts['cn_name']??'';
            $products[$key]["bn_name"]= $gifts['bn_name']??'';
            $products[$key]['quantity']=$gift->quantity;
    }

    
}     

    $data['salesman_name']=$sale['salesman_details']['first_name'].' '.$sale['salesman_details']['last_name'];
    $data['salesman_email']=$sale['salesman_details']['email'];
    $data['salesman_phone']=$sale['salesman_details']['phone'];
    $data['customer_name']=$sale['customer']['name'];
    $data['customer_phone']=$sale['customer']['phone'];
    $data['customer_email']=$sale['customer']['email'];
    $data['customer_address']=$sale['customer']['address'];
    $data['customer_credit_limit']=number_format($sale['customer']['credit_limit'],2);
    $data['salesitem']=$products;
    return $this->sendResponse($data,"Details fetch Successfully");
    
   }

}
