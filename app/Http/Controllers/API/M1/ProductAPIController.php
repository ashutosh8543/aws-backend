<?php

namespace App\Http\Controllers\API\M1;

use App\Http\Controllers\AppBaseController;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\LoadProductToSaleman;
use Carbon\Carbon;
use App\Models\MainProduct;
use App\Models\Product;
use App\Models\Salesman;
use App\Models\Chanel;
use App\Models\UnloadProductFromSaleman;



class ProductAPIController extends AppBaseController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $products = $this->productRepository;

        if ($request->get('product_unit')) {
            $products->where('product_unit', $request->get('product_unit'));
        }

        if ($request->get('warehouse_id') && $request->get('warehouse_id') != 'null') {
            $warehouseId = $request->get('warehouse_id');
            $products->whereHas('stock', function ($q) use ($warehouseId) {
                $q->where('manage_stocks.warehouse_id', $warehouseId);
            })->with([
                'stock' => function (HasOne $query) use ($warehouseId) {
                    $query->where('manage_stocks.warehouse_id', $warehouseId);
                },
            ]);
        }

        $products = $products->get();
        $data = [];
        foreach ($products as $product) {
            $data[] = $product->prepareAttributes();
        }

        return $this->sendResponse($data, 'Products retrieved successfully');
    }

    public function show($id): JsonResponse
    {   
        $product = $this->productRepository->find($id);
        $data = $product->prepareAttributes();

        return $this->sendResponse($data, 'Product Retrieved Successfully');
    }

    public function getProductByCategory($id): JsonResponse
    {
        $products = $this->productRepository->whereProductCategoryId($id)->get();
        $data = [];
        foreach ($products as $product) {
            $data[] = $product->prepareProducts();
        }

        return $this->sendResponse($data, 'Product Retrieved Successfully.');
    }

    public function getProductByBrand($id): JsonResponse
    {
        $products = $this->productRepository->whereBrandId($id)->get();
        $data = [];
        foreach ($products as $product) {
            $data[] = $product->prepareProducts();
        }

        return $this->sendResponse($data, 'Product Retrieved Successfully.');
    }

    /**
     * @param $id
     */
    public function getProductByBrandAndCategory(Request $request): JsonResponse
    {
        $products = $this->productRepository->whereBrandId($request->brand_Id)->whereProductCategoryId($request->category_id)->get();
        $data = [];
        foreach ($products as $product){
            $data[] = $product->prepareProducts();
        }

        return $this->sendResponse($data, 'Product Retrieved Successfully.');
    }
     
    public function TodayLoadedProductList(Request $request,$id=null){        
      

        $salesman=LoadProductToSaleman::where('salesman_id',$id)->whereDate('assign_for_date',Carbon::today())->get();
        $salesmain_details=Salesman::where('salesman_id',$id)->first();
        $data=[];
        $chaneldetails=[];
        foreach ($salesman as $key=>$value){    
              $products = $this->productRepository->find($value->product_id);
              $products->load('priceInventories.channel');
              $product_details= $products->prepareAttributes();
            //   dd($product_details);
              if(count($product_details['chanel']) && $product_details['chanel']){
                foreach($product_details['chanel'] as $keys=>$val){ 
                    if($salesmain_details->distributor_id!=$val->user_id){  
                       unset($product_details['chanel'][$keys]);
                    }                   
                }                
                if(!count($product_details['chanel'])){
                    $chanels=Chanel::where('status','Active')->get();
                   foreach($chanels as $values){
                      array_push($chaneldetails,[
                         "id"=> $values->id,
                         "price"=> $products->product_price,
                         "user_id"=> $salesmain_details->distributor_id,
                         "chanel_id"=> $values->id,
                          "product_id"=> $value->product_id,
                          "channel"=>Chanel::find($values->id)
                      ]);
                   }
                }

              }else{
                  $chanels=Chanel::where('status','Active')->get();
                   foreach($chanels as $values){
                      array_push($chaneldetails,[
                         "id"=> $values->id,
                         "price"=> $products->product_price,
                         "user_id"=> $salesmain_details->distributor_id,
                         "chanel_id"=> $values->id,
                        "product_id"=> $value->product_id,
                        "channel"=>Chanel::find($values->id)
                      ]);
                   }
            } 
            if(!count($product_details['chanel'])){
                $product_details['chanel']=$chaneldetails;
                $chaneldetails=[];      
            }         
            $data[$key]= $product_details;
            $data[$key]['salesman_id']=$value->salesman_id;
            $data[$key]['assign_for_date']=$value->assign_for_date;
            $data[$key]['assign_quantity']=$value->quantity;
            $data[$key]['remain_quantity']=$value->quantity;
            $data[$key]['total_quantity']=$value->total_quantity;
            $data[$key]['sold_quantity']=$value->total_quantity-$value->quantity;
            $data[$key]['unique_code']=$value->unique_code;
            $data[$key]['product_id']=$value->product_id;
        }
        return $this->sendResponse($data, 'Retrieved Successfully.');

    }

    public function AllLoadedProductList(Request $request,$id=null){     

        $salesman=LoadProductToSaleman::where('salesman_id',$id)->get();
        $salesmain_details=Salesman::where('salesman_id',$id)->first();
        $data=[];
        foreach ($salesman as $key=>$value){
              $products = $this->productRepository->find($value->product_id);
              $product_details= $products->prepareAttributes();
              if($product_details['chanel']){                             
                foreach($product_details['chanel'] as $keys=>$val){ 
                    if($salesmain_details->distributor_id!=$val->user_id){  
                       unset($product_details['chanel'][$keys]);
                    }                   
                }
            }             
            $data[$key]= $product_details;
            $data[$key]['salesman_id']=$value->salesman_id;
            $data[$key]['assign_for_date']=$value->assign_for_date;
            $data[$key]['assign_quantity']=$value->total_quantity;
            $data[$key]['remain_quantity']=$value->quantity;
            $data[$key]['unique_code']=$value->unique_code;
            $data[$key]['product_id']=$value->product_id;
        }
        return $this->sendResponse($data, 'Retrieved Successfully.');

    } 
    
    public function UpcomingLoadedProductList(Request $request,$id=null){        
      

        $salesman=LoadProductToSaleman::where('salesman_id',$id)->whereDate('assign_for_date','>',Carbon::today())->get();
        $salesmain_details=Salesman::where('salesman_id',$id)->first();
        $data=[];
        foreach ($salesman as $key=>$value){
              $products = $this->productRepository->find($value->product_id);
              $product_details= $products->prepareAttributes();
              if($product_details['chanel']){                             
                foreach($product_details['chanel'] as $keys=>$val){ 
                    if($salesmain_details->distributor_id!=$val->user_id){  
                       unset($product_details['chanel'][$keys]);
                    }                   
                }
              }
             
            $data[$key]= $product_details;
            $data[$key]['salesman_id']=$value->salesman_id;
            $data[$key]['assign_for_date']=$value->assign_for_date;
            $data[$key]['assign_quantity']=$value->total_quantity;
            $data[$key]['remain_quantity']=$value->quantity;
            $data[$key]['unique_code']=$value->unique_code;
            $data[$key]['product_id']=$value->product_id;
        }
        return $this->sendResponse($data, 'Retrieved Successfully.');

    }

    public function DistributorProducts(Request $request,$id=null){     
        // dd($id);
        $mainprod=MainProduct::where('status',1)->whereNull('user_id')->orWhere('user_id',$id)->get();

        $data=[];
        $i=0;
        $chaneldetails=[];
        foreach($mainprod as $key=>$value){
            $products =Product::where('main_product_id',$value->id)->first();
            if(!empty($products)){  
                // $pro =$products->prepareAttributes();  
                $products->load('priceInventories.channel');  
              
                $product_details= $products->prepareAttributes();
                if(count($product_details['chanel']) && $product_details['chanel']){
                  foreach($product_details['chanel'] as $keys=>$val){ 
                      if($id!=$val->user_id){  
                         unset($product_details['chanel'][$keys]);
                      }                   
                  }
                  if(!count($product_details['chanel'])){
                    $chanels=Chanel::where('status','Active')->get();
                   foreach($chanels as $values){
                      array_push($chaneldetails,[
                         "id"=> $values->id,
                         "price"=> $products->product_price,
                         "user_id"=> $id,
                         "chanel_id"=> $values->id,
                          "product_id"=> $value->product_id,
                          "channel"=>Chanel::find($values->id)
                      ]);
                   }
                }
                }else{
                    $chanels=Chanel::where('status','Active')->get();
                     foreach($chanels as $values){
                        array_push($chaneldetails,[
                           "id"=> $values->id,
                           "price"=> $products->product_price,
                           "user_id"=> $id,
                           "chanel_id"=> $values->id,
                           "product_id"=> $value->id,
                           "channel"=>Chanel::find($values->id)
                        ]);
                     }
              } 
              if(!count($product_details['chanel'])){
                  $product_details['chanel']=$chaneldetails;
                  $chaneldetails=[];   
              } 
              
                $data[$i]=  $product_details;
                ++$i; 
            }    
       
        }
        return $this->sendResponse($data, 'Retrieved Successfully.');

    }

    //Stock in details

    
    public function AllSockInDetailsList(Request $request,$id=null){     

        $salesman=LoadProductToSaleman::with('LoadedProducts')->where('salesman_id',$id)->latest()->get();
        $salesmain_details=Salesman::where('salesman_id',$id)->first();
        $data=[];
        foreach($salesman as $key=>$value){
              $products = $this->productRepository->find($value->product_id);
              $product_details= $products->prepareAttributes();
              if($product_details['chanel']){                             
                foreach($product_details['chanel'] as $keys=>$val){ 
                    if($salesmain_details->distributor_id!=$val->user_id){  
                       unset($product_details['chanel'][$keys]);
                    }                   
                }
            }                       
            $data[$key]= $product_details;
            $data[$key]['salesman_id']=$value->salesman_id;
            $data[$key]['assign_for_date']=$value->assign_for_date;
            $data[$key]['total_assign_quantity']=$value->total_quantity??0;
            $data[$key]['sold_quantity']=$value->total_quantity??0-$value->quantity;
            $data[$key]['product_id']=$value->product_id;
            $data[$key]['loaded_products']=$value['LoadedProducts']??'';  
        }
        return $this->sendResponse($data, 'All StockIn List.');

    }
    public function stockOutProductList(Request $request,$id=null){      

        $data=UnloadProductFromSaleman::with(['salesman'=>function($query){
            $query->select(['id','first_name','last_name','unique_code']);
        }
        ,'product'=>function($query){
           $query->select(['id','name']);
        }])
        ->where('salesman_id',$id)
        ->select(['id','salesman_id','unique_code','product_id','assign_for_date','quantity as unload_quantity','total_quantity as total_load_quantity'])
        ->latest()->get();  
        
        $total_stockOut=UnloadProductFromSaleman::where('salesman_id',$id)->sum('quantity');
        return response()->json([
            'data'=>$data,
            'total_unload'=>$total_stockOut,
            'message'=>'All stock out products fetch Successfully.'
         ]);

    }

    public function todayStockOutProductList(Request $request,$id=null){      

        $data=UnloadProductFromSaleman::with(['salesman'=>function($query){
            $query->select(['id','first_name','last_name','unique_code']);
        }
        ,'product'=>function($query){
           $query->select(['id','name']);
        }])
        ->where('salesman_id',$id)
        ->whereDate('assign_for_date',Carbon::today())
        ->select(['id','salesman_id','unique_code','product_id','assign_for_date','quantity as unload_quantity','total_quantity as total_load_quantity'])
        ->latest()->get();      
        return $this->sendResponse($data, 'Today stock out products fetch Successfully.');

    }

  
    public function stockInOutProductList(Request $request,$id=null){      
        
        $stockin=LoadProductToSaleman::with(['product'=>function($query){
           $query->select(['id','name','product_unit']);
        },'product.productBaseUnit'])
        ->where('salesman_id',$id)->select(['id','salesman_id','product_id','quantity','total_quantity','assign_for_date','created_at','unique_code']); 

        $stockout=UnloadProductFromSaleman::with(['product'=>function($query){
           $query->select(['id','name','product_unit']);
        },'product.productBaseUnit'])
        ->where('salesman_id',$id)->select(['id','salesman_id','product_id','quantity','total_quantity','assign_for_date','created_at','unique_code']);        
        $combined = $stockin->union($stockout)->latest()->get();         
        return $this->sendResponse($combined, 'All stock  In/out products fetch Successfully.');

    }
    






}
