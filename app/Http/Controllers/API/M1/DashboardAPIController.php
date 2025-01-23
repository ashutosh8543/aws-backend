<?php

namespace App\Http\Controllers\API\M1;

use App\Http\Controllers\AppBaseController;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ManageStock;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SalesPayment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\OpeningAndClosing;
use Illuminate\Http\Request;
use App\Models\AssignCustomer;
use App\Models\AssignCustomersList;
use App\Models\Notification;
use App\Models\LoadProductToSaleman;
use App\Models\UnloadProductFromSaleman;
use App\Models\Salesman;
use App\Models\CreditLimit;
use App\Http\Resources\SaleReturnCollection;
use App\Http\Resources\SaleReturnResource;
use App\Repositories\ProductRepository;
use App\Repositories\SaleReturnRepository;
use App\Models\CheckIn;
use App\Models\CreditCollection;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\GiftSubmit;
use Validator;



class DashboardAPIController extends AppBaseController
{


    public  $productRepository;
    public $saleReturnRepository;


    public function __construct(ProductRepository $productRepository,SaleReturnRepository $saleReturnRepository)
    {
        $this->productRepository = $productRepository;
        $this->saleReturnRepository = $saleReturnRepository;
    }


    public function index(Request $request): JsonResponse
    {

        $data = [];
        $today = Carbon::today();
        $sales_man_id=$request->get("user_id");

        // $data['sales'] = (float) Sale::sum('grand_total');
        // $data['purchases'] = (float) Purchase::sum('grand_total');
        // $data['sale_returns'] = (float) SaleReturn::sum('grand_total');
        // $data['purchase_returns'] = (float) PurchaseReturn::sum('grand_total');
        $data['today_sales'] = (float) Sale::where("user_id",$sales_man_id)->where('date', $today)->sum('grand_total');
        $data['total_sales'] = (float) Sale::where('user_id',$sales_man_id)->sum('grand_total');
        $data['today_cash_recieved'] = (float) Sale::where("payment_type",1)->where("user_id",$sales_man_id)->where('date', $today)->sum('grand_total');

        // $data['today_sales_received'] = (float) SalesPayment::where('payment_date', $today)->sum('amount');
        // $data['today_purchases'] = (float) PurchaseReturn::where('date', $today)->sum('grand_total');
        // $data['today_expenses'] = (float) Expense::where('date', $today)->sum('amount');
        $openingcashdata=OpeningAndClosing::where('sales_man_id',$sales_man_id)->where('type','opening')->whereDate('created_at',$today)->sum('cash');
        $closingcashdata=OpeningAndClosing::where('sales_man_id',$sales_man_id)->where('type','closing')->whereDate('created_at',$today)->sum('cash');
        $data['opening_cash']=$openingcashdata??0;
        $data['closing_cash']=$closingcashdata??0;
         // Get the count of assigned customers
         $today=Carbon::today()->format('Y-m-d');
         //  $todattrip=AssignCustomersList::where('salesman_id',$sales_man_id)->where('status','<',2)->whereDate('assigned_date','<=',$today)->orWhereDate('assigned_date',$today)->count();        
                   
        $todattrip = AssignCustomersList::where('salesman_id',$sales_man_id)
        ->where(function ($query) use ($today) {
         $query->where(function ($subQuery) use ($today) {
           $subQuery->whereDate('assigned_date', $today);
                })->orWhere(function ($subQuery) use ($today) {
                    $subQuery->whereDate('assigned_date', '<', $today)
                                ->where('status', '<', 2);
                });
            })
            ->count();
   


         $data['today_trip'] =$todattrip;

        return $this->sendResponse($data, 'Dashboard data Retrieved Successfully');
    }

    public function getWeekSalePurchases(): JsonResponse
    {
        $count = 7;
        $days = [];
        $date = Carbon::tomorrow();
        for ($i = 0; $i < $count; $i++) {
            $days[] = $date->subDay()->format('Y-m-d');
        }
        $day['days'] = array_reverse($days);
        $sales = Sale::whereBetween('date', [$day['days'][0], $day['days'][6]])
            ->orderBy('date', 'desc')
            ->groupBy('date')
            ->get([
                DB::raw('DATE_FORMAT(date,"%Y-%m-%d") as week'),
                DB::raw('SUM(grand_total) as grand_total'),
            ])->keyBy('week');
        $period = CarbonPeriod::create($day['days'][0], $day['days'][6]);
        $data['dates'] = array_map(function ($datePeriod) {
            return $datePeriod->format('Y-m-d');
        }, iterator_to_array($period));

        $data['sales'] = array_map(function ($datePeriod) use ($sales) {
            $week = $datePeriod->format('Y-m-d');

            return $sales->has($week) ? $sales->get($week)->grand_total : 0;
        }, iterator_to_array($period));

        $purchases = Purchase::whereBetween('date', [$day['days'][0], $day['days'][6]])
            ->orderBy('date', 'desc')
            ->groupBy('date')
            ->get([
                DB::raw('DATE_FORMAT(date,"%Y-%m-%d") as week'),
                DB::raw('SUM(grand_total) as grand_total'),
            ])->keyBy('week');
        $data['purchases'] = array_map(function ($datePeriod) use ($purchases) {
            $week = $datePeriod->format('Y-m-d');

            return $purchases->has($week) ? $purchases->get($week)->grand_total : 0;
        }, iterator_to_array($period));

        for ($x = 0; $x < 7; $x++) {
            $newData[] = [
                'dates' => $data['dates'][$x],
                'sales' => $data['sales'][$x],
                'purchases' => $data['purchases'][$x],
            ];
        }

        return $this->sendResponse($newData, 'Week of Sales Purchase Retrieved Successfully');
    }

    public function getYearlyTopSelling(): JsonResponse
    {
        $year = Carbon::now()->year;
        $topSellings = Product::leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->whereYear('sale_items.created_at', $year)
            ->selectRaw('products.*, COALESCE(sum(sale_items.sub_total),0) grand_total')
            ->selectRaw('products.*, COALESCE(sum(sale_items.quantity),0) total_quantity')
            ->groupBy('products.id')
            ->orderBy('total_quantity', 'desc')
            ->take(5)
            ->get();
        $data = [];
        foreach ($topSellings as $topSelling) {
            $data[] = [
                'name' => $topSelling->name,
                'total_quantity' => $topSelling->total_quantity,
            ];
        }

        return $this->sendResponse($data, 'Yearly TopSelling Products Retrieved Successfully');
    }

    public function getTopSellingProducts(): JsonResponse
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $topSellings = Product::leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->whereMonth('sale_items.created_at', $month)
            ->whereYear('sale_items.created_at', $year)
            ->selectRaw('products.*, COALESCE(sum(sale_items.sub_total),0) grand_total')
            ->selectRaw('products.*, COALESCE(sum(sale_items.quantity),0) total_quantity')
            ->groupBy('products.id')
            ->orderBy('total_quantity', 'desc')
            ->latest()
            ->take(5)
            ->get();
        $data = [];
        foreach ($topSellings as $topSelling) {
            $data[] = $topSelling->prepareTopSelling();
        }

        return $this->sendResponse($data, 'Top Selling Products Retrieved Successfully');
    }


     public function getProductsDetails($id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        $data = $product->prepareProducts();

        return $this->sendResponse($data, 'Product Detail Retrieved Successfully');
    }




    public function getTopCustomer(): JsonResponse
    {
        $month = Carbon::now()->month;
        $topCustomers = Customer::leftJoin('sales', 'customers.id', '=', 'sales.customer_id')
            ->whereMonth('date', $month)
            ->select('customers.*', DB::raw('sum(sales.grand_total) as grand_total'))
            ->groupBy('customers.id')
            ->orderBy('grand_total', 'desc')
            ->latest()
            ->take(5)
            ->get();
        $data = [];
        foreach ($topCustomers as $topCustomer) {
            $data[] = [
                'name' => $topCustomer->name,
                'grand_total' => $topCustomer->grand_total,
            ];
        }

        return $this->sendResponse($data, 'Top Customers Retrieved Successfully');
    }

    public function getRecentSales()
    {
        $recentSales = Sale::latest()->take(5)->get();
        $data = [];
        foreach ($recentSales as $sales) {
            $data[] = $sales->prepareRecentSelling();
        }

        return $this->sendResponse($data, 'Recent Selling Products Retrieved Successfully');
    }

    public function stockAlerts(): JsonResponse
    {
        $manageStocks = ManageStock::with('warehouse')->where('alert', true)->limit(10)->latest()->get();

        $productResponse = [];
        foreach ($manageStocks as $stocks) {
            $productResponse[] = $stocks->prepareStockAlerts();
        }

        return $this->sendResponse($productResponse, 'Stocks retrieved successfully');
    }

    public function Notifications(Request $request,$id=null){

        $data = Notification::where('salesman_id',$id)->get();
        $total_unread=Notification::where('salesman_id',$id)->where('is_read',0)->count();
        return response()->json([
            'success' => true,
            'data' =>$data,
            'total_unread'=>$total_unread,
            'message' => 'notificans fetch in successfully.',
        ]);
    }

    public function readNotifications(Request $request,$id=null){
        try{
        Notification::where('id',$id)->update(['is_read'=>1]);
        return response()->json([
            'success' => true,
            'message' => 'notificans read successfully.',
        ]);
      }catch(\Exception $e){
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
      } 
    }
    

    public function TodaySummary(Request $request): JsonResponse
    {

        $data = [];
        $today = Carbon::today();
        $sales_man_id=$request->get("user_id");
        // $data['sales'] = (float) Sale::sum('grand_total');
        // $data['purchases'] = (float) Purchase::sum('grand_total');
        // $data['sale_returns'] = (float) SaleReturn::sum('grand_total');
        // $data['purchase_returns'] = (float) PurchaseReturn::sum('grand_total');
        $data['today_sales'] = (float) Sale::where("user_id",$sales_man_id)->where('date', $today)->sum('grand_total');
        $data['today_cash_recieved'] = (float) Sale::where("payment_type",1)->where("user_id",$sales_man_id)->where('date', $today)->sum('grand_total');
        $todaysaleIds =  Sale::where("user_id",$sales_man_id)->where('date', $today)->pluck('id');
        $data['today_total_sales_products']=SaleItem::whereIn('sale_id',$todaysaleIds)->sum('quantity');
        
        $todayreturnIds =  SaleReturn::where("salesman_id",$sales_man_id)->where('date', $today)->pluck('id');
        $data['today_total_sales_return_products']=SaleReturnItem::whereIn('sale_return_id',$todayreturnIds)->sum('quantity');

        $data['today_total_gift_sold']=GiftSubmit::where('sales_man_id',$sales_man_id)->whereDate('created_at', $today)->sum('total_quantity');

        // $data['today_sales_received'] = (float) SalesPayment::where('payment_date', $today)->sum('amount');
        // $data['today_purchases'] = (float) PurchaseReturn::where('date', $today)->sum('grand_total');
        // $data['today_expenses'] = (float) Expense::where('date', $today)->sum('amount');
        $openingcashdata=OpeningAndClosing::where('sales_man_id',$sales_man_id)->where('type','opening')->whereDate('created_at',$today)->sum('cash');
        $closingcashdata=OpeningAndClosing::where('sales_man_id',$sales_man_id)->where('type','closing')->whereDate('created_at',$today)->sum('cash');
        $data['today_opening_cash']=$openingcashdata??0;
        $data['today_closing_cash']=$closingcashdata??0;
         // Get the count of assigned customers
         $today=Carbon::today()->format('Y-m-d');
        //  $todattrip=AssignCustomersList::where('salesman_id',$sales_man_id)->where('status','<',2)->whereDate('assigned_date','<=',$today)->orWhereDate('assigned_date',$today)->count();        
         $todattrip = AssignCustomersList::where('salesman_id',$sales_man_id)
         ->where(function ($query) use ($today) {
          $query->where(function ($subQuery) use ($today) {
            $subQuery->whereDate('assigned_date', $today);
                 })->orWhere(function ($subQuery) use ($today) {
                     $subQuery->whereDate('assigned_date', '<', $today)
                                 ->where('status', '<', 2);
                 });
             })
             ->count();
        
         $data['today_trip'] =$todattrip;

         $data['today_collection']=CreditCollection::where('salesman_id',$sales_man_id)->whereDate('collected_date',$today)->sum('amount');
         $data['today_collection_list']=CreditCollection::with(['customer','customer.channelDetails','orderDetails'])->where('salesman_id',$sales_man_id)->whereDate('collected_date',$today)->latest()->get();
         
         $data['today_checkin_list']=CheckIn::with('customer','customer.channelDetails')->where('salesman_id',$sales_man_id)->whereDate('uploaded_date',$today)->latest()->get();
         $data['today_total_checkin']=CheckIn::where('salesman_id',$sales_man_id)->whereDate('uploaded_date',$today)->count();



         $data['today_stockin_remain_quantity']=$stockin_remain_quantity=LoadProductToSaleman::where('salesman_id',$sales_man_id)->whereDate('assign_for_date',Carbon::today())->sum('quantity');
         $data['today_stockin_total_quantity']=$stockin_total_quantity=LoadProductToSaleman::where('salesman_id',$sales_man_id)->whereDate('assign_for_date',Carbon::today())->sum('total_quantity');
         $data['today_sold_qauntity']=$stockin_total_quantity-$stockin_remain_quantity;        
         $data['today_stockout_quantity']=$stockout__quantity=UnloadProductFromSaleman::where('salesman_id',$sales_man_id)->whereDate('assign_for_date',Carbon::today())->sum('quantity');
         //today load products
         
         $loadProducts=LoadProductToSaleman::where('salesman_id',$sales_man_id)->whereDate('assign_for_date',Carbon::today())->get();
         $salesmain_details=Salesman::where('salesman_id',$sales_man_id)->first();
         $loadDetails=[];
         foreach ($loadProducts as $key=>$value){    
               $products = $this->productRepository->find($value->product_id);
               $product_details= $products->prepareAttributes();
               if($product_details['chanel']){                             
                 foreach($product_details['chanel'] as $keys=>$val){ 
                     if($salesmain_details->distributor_id!=$val->user_id){  
                        unset($product_details['chanel'][$keys]);
                     }                   
                 }
               }             
             $loadDetails[$key]= $product_details;
             $loadDetails[$key]['salesman_id']=$value->salesman_id;
            //  $loadDetails[$key]['assign_for_date']=$value->assign_for_date;
             $loadDetails[$key]['assign_quantity']=$value->quantity;
             $loadDetails[$key]['remain_quantity']=$value->quantity;
             $loadDetails[$key]['total_quantity']=$value->total_quantity;
             $loadDetails[$key]['sold_quantity']=$value->total_quantity-$value->quantity;
             $loadDetails[$key]['unique_code']=$value->unique_code;
             $loadDetails[$key]['product_id']=$value->product_id;
         } 
         $data['today_stockin_list'] =$loadDetails;            
         //today unload products
         $data['today_stockout_list']=UnloadProductFromSaleman::with(['salesman'=>function($query){
            $query->select(['id','first_name','last_name','unique_code']);
        }
        ,'product'=>function($query){
           $query->select(['id','name']);
        }])
        ->where('salesman_id',$sales_man_id)
        ->whereDate('assign_for_date',Carbon::today())
        ->select(['id','salesman_id','unique_code','product_id','assign_for_date','quantity as unload_quantity','total_quantity as total_load_quantity'])
        ->latest()->get(); 
       
        //today return list          
        $salesReturn=$this->saleReturnRepository->where('salesman_id',$sales_man_id)->where('date',Carbon::today())->latest()->get();
        $returndata=[];
        foreach($salesReturn as $key=>$val){
            $returndata[$key] = $val->prepareAttributes();
        }
        $data['today_sales_return'] =  $returndata;
        return $this->sendResponse($data, 'Today summary Retrieved Successfully');
    }

    public function SalesmanCreditList(Request $request,$id=null){         
        $data=CreditCollection::with(['customer','customer.channelDetails','orderDetails'])->where('salesman_id',$id)->latest()->get();
        return $this->sendResponse($data, 'All Collection Retrieved Successfully');
    } 
    

    public function SalesmanUpdateCreditList(Request $request,$id=null){  
        
        $validator = Validator::make($request->all(),[
            'salesman_id' => 'required',
            'credit_limit_id'=>'required', //collection
            'collected_date'=>'required',
            'collection_payment_type'=>'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()->first(),
                'success' => false,
            ], 422);
        }
        $collected_date=Date('y-m-d h:i:s',strtotime($request->collected_date));
        try{
            $message="Already Updated successfully"; 
            DB::beginTransaction();
            $check= CreditCollection::where('id',$request->credit_limit_id)
            ->where('salesman_id',$request->salesman_id)->where('status','Pending')->first();
            if($check){
                CreditCollection::where('id',$request->credit_limit_id)
                ->where('salesman_id',$request->salesman_id)
                ->update(['status'=>'Completed','collected_date'=>$collected_date,'collection_payment_type'=>$request->collection_payment_type]);                 
                
                $customer=Customer::where('id',$check->customer_id)->first();
                $current_limit =$customer->credit_limit;
                $customer->update(['credit_limit'=>$current_limit+$check->amount]);
                $message="Updated successfully"; 
            }         
            DB::commit();
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 200);
        }catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

    } 


    public function SalesmanCreditDetails(Request $request,$id=null){  
        
        $validator = Validator::make($request->all(),[
            'salesman_id' => 'required',
            'credit_limit_id'=>'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()->first(),
                'success' => false,
            ], 422);
        }
        try{
        $data=CreditCollection::with(['customer','customer.channelDetails','orderDetails'])->where('id',$request->credit_limit_id)->where('salesman_id',$request->salesman_id)->first();
                    
        return response()->json([
            'success' => true,
            'data'=>  $data,
            'message' => "credit limit details",
        ], 200);
        }catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

    } 


}
