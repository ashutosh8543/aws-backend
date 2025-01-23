<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\AppBaseController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoadProductToSaleman;
use App\Models\UnloadProductFromSaleman;
use App\Models\LoadProductsHitory;
use App\Models\Warehouse;
use App\Models\Salesman;
use App\Models\Suppervisor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;
use Validator;
use Carbon\Carbon;

class LoadUnloadController extends AppBaseController
{


    // public function Load(Request $request)
    // {
    //     $rules = [
    //         'customer_id' => 'required',
    //         'date' => 'required|date',
    //     ];
    //     $customMessages = [
    //         'customer_id.required' => 'Please select sales man',
    //         'date.required' => 'Please select date'
    //     ];
    //     $validator = Validator::make($request->all(),$rules,$customMessages);
    //     if ($validator->fails()){
    //         $messages = $validator->errors()->all();
    //         $msg = $messages[0];
    //         return $this->sendError($msg);
    //     }

    //     try{

    //     $input=$request->all();
    //     $salesman_id=$input['customer_id'];
    //     $assign_for_date=$input['date'];
    //     $products=$input['sale_items'];
    //     DB::beginTransaction();
    //     $updated_product=[];
    //     if($products){
    //         foreach($products as $value){
    //         $checkProducts=LoadProductToSaleman::where('salesman_id',$salesman_id)->whereDate('assign_for_date',$assign_for_date)->where('product_id',$value['product_id'])->first();

    //         if(empty($checkProducts)){
    //         $loadproducts= LoadProductToSaleman::create([
    //             'salesman_id'=>$salesman_id,
    //             'quantity'=>$value['quantity'],
    //             'product_id'=>$value['product_id'],
    //             'products'=>$value,
    //             'assign_for_date'=>$assign_for_date,
    //             'total_quantity'=>$value['quantity']
    //         ]);
    //         LoadProductsHitory::create([
    //             'load_id'=>$loadproducts->id,
    //             'salesman_id'=>$salesman_id,
    //             'quantity'=>$value['quantity'],
    //             'product_id'=>$value['product_id'],
    //             'products'=>$value,
    //             'assign_for_date'=>$assign_for_date,
    //         ]);
    //         $loadproducts->update(['unique_code'=>'STKIN_'.$loadproducts->id]);
    //         }else{
    //             $update_quantity=$checkProducts->quantity+$value['quantity'];
    //             $update_total_quantity=$checkProducts->total_quantity+$value['quantity'];
    //             $checkProducts->update(['quantity'=>$update_quantity,'total_quantity'=>$update_total_quantity,'unique_code'=>'STKIN_'.$checkProducts->id]);
    //             LoadProductsHitory::create([
    //                 'load_id'=>$checkProducts->id,
    //                 'salesman_id'=>$salesman_id,
    //                 'quantity'=>$value['quantity'],
    //                 'product_id'=>$value['product_id'],
    //                 'products'=>$value,
    //                 'assign_for_date'=>$assign_for_date,
    //             ]);
    //         }
    //       }
    //     }
    //     DB::commit();
    //     return $this->sendSuccess('Products Uploaded successfully');
    //     } catch (Exception $e){
    //         DB::rollBack();
    //         throw new UnprocessableEntityHttpException($e->getMessage());
    //     }




    // }


//     public function Load(Request $request)
// {
//     $rules = [
//         'date' => 'required|date',
//         'warehouse_id' => 'required|exists:warehouses,id',
//     ];
//     $customMessages = [
//         'date.required' => 'Please select date',
//         'warehouse_id.required' => 'Please select a warehouse',
//         'warehouse_id.exists' => 'The selected warehouse does not exist',
//     ];

//     $validator = Validator::make($request->all(), $rules, $customMessages);
//     if ($validator->fails()) {
//         $messages = $validator->errors()->all();
//         $msg = $messages[0];
//         return $this->sendError($msg);
//     }

//     try {
//         $input = $request->all();
//         $salesman_id = $input['salesman_id'];
//         $assign_for_date = $input['date'];
//         $warehouse_id = $input['warehouse_id'];
//         // $product_id = $input['id'];
//         $products = $input['sale_items'];
//         DB::beginTransaction();
//         $updated_product = [];


//         if ($products) {
//             foreach ($products as $value) {
//                 $warehouseInventory = productInventoriesDetails()->where('warehouse_id', $warehouse_id)
//                     ->where('product_id', $value['id'])
//                     ->first();
//                 dd($warehouseInventory);
//                 $checkProducts = LoadProductToSaleman::where('salesman_id', $salesman_id)
//                     ->where('warehouse_id', $warehouse_id)
//                     ->whereDate('assign_for_date', $assign_for_date)
//                     ->where('product_id', $value['id'])
//                     ->first();

//                 if (empty($checkProducts)) {

//                     $loadproducts = LoadProductToSaleman::create([
//                         'salesman_id' => $salesman_id,
//                         'warehouse_id' => $warehouse_id,
//                         'quantity' => $value['quantity'],
//                         'product_id' => $value['id'],
//                         'products' => $value,
//                         'assign_for_date' => $assign_for_date,
//                         'total_quantity' => $value['quantity'],
//                     ]);

//                     LoadProductsHitory::create([
//                         'load_id' => $loadproducts->id,
//                         'salesman_id' => $salesman_id,
//                         'warehouse_id' => $warehouse_id,
//                         'quantity' => $value['quantity'],
//                         'product_id' => $value['id'],
//                         'products' => $value,
//                         'assign_for_date' => $assign_for_date,
//                     ]);

//                     $loadproducts->update(['unique_code' => 'STKIN_' . $loadproducts->id]);
//                 } else {
//                     $update_quantity = $checkProducts->quantity + $value['quantity'];
//                     $update_total_quantity = $checkProducts->total_quantity + $value['quantity'];

//                     $checkProducts->update([
//                         'quantity' => $update_quantity,
//                         'total_quantity' => $update_total_quantity,
//                         'unique_code' => 'STKIN_' . $checkProducts->id,
//                     ]);

//                     LoadProductsHitory::create([
//                         'load_id' => $checkProducts->id,
//                         'salesman_id' => $salesman_id,
//                         'warehouse_id' => $warehouse_id,
//                         'quantity' => $value['quantity'],
//                         'product_id' => $value['id'],
//                         'products' => $value,
//                         'assign_for_date' => $assign_for_date,
//                     ]);
//                 }
//             }
//         }

//         DB::commit();
//         return $this->sendSuccess('Products Uploaded successfully');
//     } catch (Exception $e) {
//         DB::rollBack();
//         throw new UnprocessableEntityHttpException($e->getMessage(),$e->getline());
//     }
// }


public function Load(Request $request)
{
    $rules = [
        'date' => 'required|date',
        'warehouse_id' => 'required|exists:warehouses,id',
        'salesman_id' => 'required|exists:salesmen,salesman_id',
    ];
    $customMessages = [
        'date.required' => 'Please select a date',
        'warehouse_id.required' => 'Please select a warehouse',
        'salesman_id.required' => 'Please select a salesman',
        'warehouse_id.exists' => 'The selected warehouse does not exist',
    ];

    $validator = Validator::make($request->all(), $rules, $customMessages);
    if ($validator->fails()) {
        $messages = $validator->errors()->all();
        $msg = $messages[0];
        return $this->sendError($msg);
    }

    try {
        $input = $request->all();
        $salesman_id = $input['salesman_id'];
        $assign_for_date = $input['date'];
        $warehouse_id = $input['warehouse_id'];
        $products = $input['sale_items'];
        DB::beginTransaction();

        if ($products) {
            foreach ($products as $value) {
                $productInventory = DB::table('product_inventories')
                    ->where('warehouse_id', $warehouse_id)
                    ->where('product_id', $value['id'])
                    ->first();

                if (!$productInventory) {
                    return $this->sendError("Product ID {$value['id']} not found in the selected warehouse.");
                }

                if ($productInventory->warehouse_quantities < $value['quantity']) {
                    return $this->sendError("Not enough quantity for Product ID {$value['id']} in the warehouse.");
                }


                $checkProducts = LoadProductToSaleman::where('salesman_id', $salesman_id)
                    ->where('warehouse_id', $warehouse_id)
                    ->whereDate('assign_for_date', $assign_for_date)
                    ->where('product_id', $value['id'])
                    ->first();

                if (empty($checkProducts)) {
                    // Create new assignment
                    $loadproducts = LoadProductToSaleman::create([
                        'salesman_id' => $salesman_id,
                        'warehouse_id' => $warehouse_id,
                        'quantity' => $value['quantity'],
                        'product_id' => $value['id'],
                        'products' => $value,
                        'assign_for_date' => $assign_for_date,
                        'total_quantity' => $value['quantity'],
                    ]);

                    LoadProductsHitory::create([
                        'load_id' => $loadproducts->id,
                        'salesman_id' => $salesman_id,
                        'warehouse_id' => $warehouse_id,
                        'quantity' => $value['quantity'],
                        'product_id' => $value['id'],
                        'products' => $value,
                        'assign_for_date' => $assign_for_date,
                    ]);

                    $loadproducts->update(['unique_code' => 'STKIN_' . $loadproducts->id]);
                } else {
                    // Update existing assignment
                    $update_quantity = $checkProducts->quantity + $value['quantity'];
                    $update_total_quantity = $checkProducts->total_quantity + $value['quantity'];

                    $checkProducts->update([
                        'quantity' => $update_quantity,
                        'total_quantity' => $update_total_quantity,
                        'unique_code' => 'STKIN_' . $checkProducts->id,
                    ]);

                    LoadProductsHitory::create([
                        'load_id' => $checkProducts->id,
                        'salesman_id' => $salesman_id,
                        'warehouse_id' => $warehouse_id,
                        'quantity' => $value['quantity'],
                        'product_id' => $value['id'],
                        'products' => $value,
                        'assign_for_date' => $assign_for_date,
                    ]);
                }

                // Reduce the assigned quantity from the product inventory
                DB::table('product_inventories')
                    ->where('id', $productInventory->id)
                    ->update(['warehouse_quantities' => $productInventory->warehouse_quantities - $value['quantity']]);
            }
        }

        DB::commit();
        return $this->sendSuccess('Products uploaded successfully.');
    } catch (Exception $e) {
        DB::rollBack();
        throw new UnprocessableEntityHttpException($e->getMessage(), $e->getLine());
    }
}




    public function index(Request $request)
    {
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $loginUserId = Auth::id();
        $userDetails = Auth::user();
        $page=$request->get('page');
        $pageNumber=$page['number']??1;

        \Log::info('Start Date: ' . $startDate);
        \Log::info('End Date: ' . $endDate);



        if($userDetails->role_id == 3){
            $distributor = User::where('id', $loginUserId)->first();

            if($distributor){
                $distributorId = $distributor->id;
                $country = $distributor->country;
                $salesmanIds = Salesman::where('distributor_id', $distributorId )
                ->where('country', $country)
                ->pluck('salesman_id');
                // dd($salesmanIds);


                $assignedProductsQuery = LoadProductToSaleman::with(['salesman' => function($query) {
                $query->where('role_id', 6);
                }])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();


                $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);
                return response()->json([
                    'message' => 'Assigned products to warehouse fetche    successfully.',
                    'data' => $assignedProducts ,
                ], 200);

            }
        }


        if($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            // dd($warehouse);
            if($warehouse){
             $ware_id = $warehouse->ware_id;
             $country = $warehouse->country;
            //  dd($country);
            //  dd($ware_id);
             $salesmanIds = Salesman::where('ware_id', $ware_id)
             ->where('ware_id', $ware_id)
             ->where('country', $country)
             ->pluck('salesman_id');
            //  dd($salesmanIds);

            $assignedProductsQuery = LoadProductToSaleman::with(['salesman' => function($query) {
                $query->where('role_id', 6);
                }])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();

                // dd($assignedProductsQuery);


                $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);
                return response()->json([
                    'message' => 'Assigned products to warehouse fetched successfully.',
                    'data' => $assignedProducts ,
                ], 200);
            }
        }

        if($userDetails->role_id == 5){
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();

            if($supervisor){
               $ware_id = $supervisor->ware_id;
               $country = $supervisor->country;

               $salesmanIds = Salesman::where('ware_id', $ware_id)
               ->where('ware_id', $ware_id)
               ->where('country', $country)
               ->pluck('salesman_id');
            //    dd( $salesmanIds);

               $assignedProductsQuery = LoadProductToSaleman::with(['salesman' => function($query) {
                $query->where('role_id', 6);
                }])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();


                $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);
                return response()->json([
                    'message' => 'Assigned products to supervisor fetched successfully.',
                    'data' => $assignedProducts ,
                ], 200);


            }

        }


        try {
            $assignedProductsQuery = LoadProductToSaleman::with(['salesman' => function($query) {
                $query->where('role_id', 6);
            }, 'product'])
            ->latest();

            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $assignedProductsQuery = LoadProductToSaleman::with(['salesman', 'product']);

            if ($sort_name) {
                $assignedProductsQuery->orderBy($sort_name, $sort);
            } else {
                $assignedProductsQuery->orderBy('id', 'desc');
            }

            if ($startDate && $endDate) {
                $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

                $assignedProductsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($search) {
                $searchTerms = explode(' ', $search);
                $assignedProductsQuery->where(function($query) use ($searchTerms) {
                    $query->whereHas('salesman', function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->where(function($subQuery) use ($term) {
                                $subQuery->where('first_name', 'LIKE', "%$term%")
                                         ->orWhere('last_name', 'LIKE', "%$term%");
                            });
                        }
                    });


                    $query->orWhereHas('product', function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->where(function($subQuery) use ($term) {
                                $subQuery->where('name', 'LIKE', "%$term%");
                            });
                        }
                    });
                });
            }

            $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);

            return response()->json([
                'message' => 'Assigned products fetched successfully.',
                'data' => $assignedProducts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching assigned products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    public function stockOutList(Request $request)
    {
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $page=$request->get('page');
        $pageNumber=$page['number']??1;

        $loginUserId = Auth::id();
        $userDetails = Auth::user();

        \Log::info('Start Date: ' . $startDate);
        \Log::info('End Date: ' . $endDate);

        if($userDetails->role_id == 3){
            $distributor = User::where('id', $loginUserId)->first();
            if($distributor){
                $distributorId = $distributor->id;
                $country = $distributor->country;

                $salesmanIds = Salesman::where('distributor_id', $distributorId )
                ->where('country', $country)
                ->pluck('salesman_id');
                // dd($salesmanIds);
                $assignedProductsQuery = UnloadProductFromSaleman::with(['salesman' => function($query) {
                    $query->where('role_id', 6);
                }, 'product'])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();

                $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);

            return response()->json([
                'message' => 'Stock Out products fetched successfully.',
                'data' => $assignedProducts,
            ], 200);


            }

        }

        if($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if($warehouse){
                $ware_id = $warehouse->ware_id;
                // dd($ware_id);
                $salesmanIds = Salesman::where('ware_id', $ware_id)
                ->where('ware_id', $ware_id)
                ->pluck('salesman_id');
                //  dd($salesmanIds);

                $assignedProductsQuery = UnloadProductFromSaleman::with(['salesman' =>function($query) {
                $query->where('role_id', 6);
                }, 'product'])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();

                $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);

                return response()->json([
                    'message' => 'Stock Out products fetched successfully.',
                    'data' => $assignedProducts,
                ], 200);


            }

        }


        if($userDetails->role_id == 5){
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();

            if($supervisor){
               $ware_id = $supervisor->ware_id;
               $country = $supervisor->country;
               $salesmanIds = Salesman::where('ware_id', $ware_id)
               ->where('ware_id', $ware_id)
               ->where('country', $country)
               ->pluck('salesman_id');
            //    dd( $salesmanIds);

            $assignedProductsQuery = UnloadProductFromSaleman::with(['salesman' =>function($query) {
                $query->where('role_id', 6);
                }, 'product'])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();

                $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);

                return response()->json([
                    'message' => 'Stock Out products fetched successfully.',
                    'data' => $assignedProducts,
                ], 200);


            }

        }

        try {
            $assignedProductsQuery = UnloadProductFromSaleman::with(['salesman' => function($query) {
                $query->where('role_id', 6);
            }, 'product'])->latest();


            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
              $sort = 'desc';
            }

            $assignedProductsQuery = UnloadProductFromSaleman::with(['salesman', 'product' ]);

            if ($sort_name) {
                $assignedProductsQuery->orderBy($sort_name, $sort);
            } else {
                $assignedProductsQuery->orderBy('id', 'desc');
            }

            if ($startDate && $endDate) {
                $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

                $assignedProductsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($search) {
                $searchTerms = explode(' ', $search);
                $assignedProductsQuery->where(function($query) use ($searchTerms) {
                    $query->whereHas('salesman', function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->where(function($subQuery) use ($term) {
                                $subQuery->where('first_name', 'LIKE', "%$term%")
                                         ->orWhere('last_name', 'LIKE', "%$term%");
                            });
                        }
                    });


                    $query->orWhereHas('product', function($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->where(function($subQuery) use ($term) {
                                $subQuery->where('name', 'LIKE', "%$term%");
                            });
                        }
                    });
                });
            }


            $assignedProducts = $assignedProductsQuery->paginate($perPage,['*'], 'page',$pageNumber);

            return response()->json([
                'message' => 'Stock Out products fetched successfully.',
                'data' => $assignedProducts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching stock out products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }







    public function todayStockInProducts(Request $request)
    {
         $perPage = getPageSize($request);
         $search = $request->get('search');
         $startDate = $request->get('start_date');
         $endDate = $request->get('end_date');
         $today=Carbon::today();
         try{
             $assignedProductsQuery = LoadProductToSaleman::with(['salesman' => function     ($query) {
                 $query->where('role_id', 6);
             }, 'product'=>function($query){
                $query->select(['id','name','product_unit']);
             }])
             ->where('assign_for_date',$today)
             ->where('quantity','>',0)
             ->latest()->get();
            return response()->json([
                'message' => 'today Assigned products fetched successfully.',
                'data' => $assignedProductsQuery,
            ], 200);
        } catch (\Exception $e){
            return response()->json([
                'message' => 'Error fetching assigned products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


   public function stockOutProduct(Request $request){

        $validator = Validator::make($request->all(),[
            'salesman_id' => 'required',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
        ]);
        $today= \Carbon\Carbon::today();
        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        try{
            DB::beginTransaction();
        foreach ($request->products as $key=>$productId){
                 $loadedProduct=LoadProductToSaleman::
                 where('salesman_id',$request->salesman_id)
                 ->where('product_id',$productId)
                ->where('assign_for_date',$today)
                ->first();


            $unload= UnloadProductFromSaleman::create([
                'salesman_id'=>$request->salesman_id,
                'quantity'=>$loadedProduct->quantity??0,
                'total_quantity'=>$loadedProduct->total_quantity??0,
                'product_id' => $productId,
                'assigned_date' =>  $today,
            ]);
            $unload->update(['unique_code'=>'STKOUT_'.$unload->id]);
                // $loadedProduct->update(['quantity'=>0]);
               }
            DB::commit();
        return response()->json([
            'message' => 'Products stock Out successfully.',
        ], 200);
        }catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

    }





}
