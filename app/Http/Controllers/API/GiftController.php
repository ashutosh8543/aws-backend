<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gift;
use App\Models\GiftSubmit;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\AssignGift;
use Illuminate\Support\Facades\Auth;
use App\Models\Salesman;
use App\Models\Warehouse;
use App\Models\Suppervisor;
use DB;

class GiftController extends AppBaseController
{



    // public function index(Request $request): JsonResponse
    // {

    //     $userDetails = Auth::user();
    //     $perPage = getPageSize($request);
    //     $loginUserId = Auth::id();
    //     $search = $request->filter['search'] ?? '';

    //     $giftQuery = Gift::with(['giftInventoriesDetails'])->orderBy('id', 'desc');

    //     if ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
    //         $country = $userDetails->country;
    //         $gift = $giftQuery->where('country', $country)->paginate($perPage);
    //     }
    //     elseif($userDetails->role_id == 3){
    //         $distributor = User::where('id', $loginUserId)->first();
    //         if($distributor){
    //             $country = $distributor->country;
    //             $gift = $giftQuery->where('country', $country)->paginate($perPage);
    //         }
    //     }
    //     elseif($userDetails->role_id == 4){
    //         $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
    //         if($warehouse){
    //             $country = $warehouse->country;
    //             $gift = $giftQuery->where('country', $country)->paginate($perPage);
    //         }
    //     }
    //     elseif($userDetails->role_id == 5){
    //         $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
    //         if($warehouse){
    //             $country = $warehouse->country;
    //             $gift = $giftQuery->where('country', $country)->paginate($perPage);
    //         }
    //     }
    //     else {
    //         $gift = $giftQuery->paginate($perPage);
    //     }

    //     return $this->sendResponse($gift, 'Gift list retrieved successfully');
    // }


    public function index(Request $request): JsonResponse
    {
        $userDetails = Auth::user();
        $perPage = getPageSize($request);
        $loginUserId = Auth::id();
        $search = $request->filter['search'] ?? '';
        $page=$request->get('page');
        $pageNumber=$page['number']??1;

        $giftQuery = Gift::with(['giftInventoriesDetails'])->orderBy('id', 'desc');


        $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }

        $giftQuery = Gift::with(['giftInventoriesDetails']);

        if ($sort_name) {
            $giftQuery->orderBy($sort_name, $sort);
        } else {
            $giftQuery->orderBy('id', 'desc');
        }

        if ($search) {
            $giftQuery->where(function($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            });
        }

        if ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
            $country = $userDetails->country;
            $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
        } elseif ($userDetails->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if ($distributor) {
                $country = $distributor->country;
                $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
            }
        } elseif ($userDetails->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $country = $warehouse->country;
                $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
            }
        } elseif ($userDetails->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if ($supervisor) {
                $country = $supervisor->country;
                $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
            }
        } else {
             $gift = $giftQuery->paginate($perPage,['*'], 'page',$pageNumber);
        }

        return $this->sendResponse($gift, 'Gift list retrieved successfully');
    }


    public function indexAll(Request $request): JsonResponse
    {
        $userDetails = Auth::user();
        $perPage = getPageSize($request);
        $loginUserId = Auth::id();
        $search = $request->filter['search'] ?? '';
        $page=$request->get('page');
        $pageNumber=$page['number']??1;

        $giftQuery = Gift::with(['giftInventoriesDetails'])
        ->where('status', 1)
        ->orderBy('id', 'desc');


        $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }

        $giftQuery = Gift::with(['giftInventoriesDetails']);

        if ($sort_name) {
            $giftQuery->orderBy($sort_name, $sort);
        } else {
            $giftQuery->orderBy('id', 'desc');
        }

        if ($search) {
            $giftQuery->where(function($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
            });
        }

        if ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
            $country = $userDetails->country;
            $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
        } elseif ($userDetails->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if ($distributor) {
                $country = $distributor->country;
                $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
            }
        } elseif ($userDetails->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $country = $warehouse->country;
                $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
            }
        } elseif ($userDetails->role_id == 5) {
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if ($supervisor) {
                $country = $supervisor->country;
                $gift = $giftQuery->where('country', $country)->paginate($perPage,['*'], 'page',$pageNumber);
            }
        } else {
             $gift = $giftQuery->paginate($perPage,['*'], 'page',$pageNumber);
        }

        return $this->sendResponse($gift, 'Gift list retrieved successfully');
    }



   public  function  getDetails(Request $request,$id=null):JsonResponse
   {
        $gift=Gift::where('id',$id)->first();
        return $this->sendResponse($gift,'Gift details retrieved Successfully');
   }


   public function submitGift(Request $request){

     $input = $request->all();

     $validator = Validator::make($input, [
         'sales_man_id'=>'required|exists:users,id',
         'outlet_id'=>'required|exists:customers,id',
         'gift_id'=>'required|exists:gifts,id',
         'quantity'=>'required',
         'discription'=>"required",
         'location'=>'required',
         'uploaded_date'=>"required",
         'image'=>'required',
     ]);

     if ($validator->fails()) {
         return $this->SendError($validator->messages());
     }
       $giftDetails=Gift::whereId($request->gift_id)->first();
         try {

            if ($request->hasFile('image')){
                    $image = $request->image;
                     $image_name_with_ext = rand().$image->getClientOriginalName();
                     $destinationPath = 'gift';
                     $image->move(public_path($destinationPath), $image_name_with_ext);
                     $vehicle_image=url("public/gift/")."/".$image_name_with_ext;
                     $input['image']=$vehicle_image;
             }
             $input['gitf_details']=$giftDetails;

             DB::table('gift_submits')->insert($input);
             return $this->sendSuccess('Gift submitted successfully');

         } catch (\Exception $e) {
             return $this->sendError($e->getMessage());
         }

   }


//     public  function  submitGiftHistory(Request $request,$id=null):JsonResponse
//     {
//       $perPage = getPageSize($request);
//       $pageNumner=$request->page['number']??1;
//       $giftHistory=GiftSubmit::with(['outlets'])->orderBy('id','desc')->paginate($perPage);
//       return $this->sendResponse($giftHistory,'Gift history retrieved Successfully');
//    }


public function submitGiftHistory(Request $request, $id = null): JsonResponse
{
    $perPage = getPageSize($request);
    $pageNumber = $request->page['number'] ?? 1;
    $loginUserId = Auth::id();
    $userDetails = Auth::user();
    $loginUser = User::find($loginUserId);
    $page=$request->get('page');
    $pageNumber=$page['number']??1;
    $startDate = request()->get('start_date');
    $endDate = request()->get('end_date');
    $warehouse_id = request()->get('warehouse_id');
    $search = $request->filter['search'] ?? '';

    $customer_id = request()->get('customer_id');

    $user = Auth::user();
    $giftHistory = GiftSubmit::with(['outlets','salesman_details'])->orderBy('id', 'desc');

    $sort = null;
        $sort_name = ltrim($request->sort, '-');
        if ($request->sort == $sort_name) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }

        $giftHistory = GiftSubmit::with(['outlets','salesman_details']);

        if ($sort_name) {
            $giftHistory->orderBy($sort_name, $sort);
        } else {
            $giftHistory->orderBy('id', 'desc');
        }


    if ($search) {
        $searchTerms = explode(' ', $search);
        $giftHistory = $giftHistory->where(function($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $query->where(function($subQuery) use ($term) {
                    $subQuery->whereHas('salesman_details', function ($subQuery) use ($term) {
                        $subQuery->where('first_name', 'like', '%' . $term . '%')
                                 ->orWhere('last_name', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('outlets', function ($subQuery) use ($term) {
                        $subQuery->where('name', 'like', '%' . $term . '%');
                    });
                });
            }
        });
    }

    if($warehouse_id){
        $warehouse = Warehouse::where('id', $warehouse_id)->first();
        $salesmanIds = Salesman::where('ware_id',$warehouse->ware_id)
        ->where('country', $userDetails->country??'')
        ->pluck('salesman_id');
        $giftHistory = GiftSubmit::with(['outlets','salesman_details'])->whereIn('sales_man_id', $salesmanIds)->orderBy('id', 'desc');
    }

    if($customer_id!='null' && isset($customer_id)){
        $giftHistory->where('outlet_id',$customer_id);
    }

    if ($startDate != 'null' && $endDate != 'null' && $startDate && $endDate) {
        $giftHistory = GiftSubmit::with(['outlets','salesman_details'])
        ->whereDate('created_at', '>=',$startDate)
        ->whereDate('created_at', '<=', $endDate)
        ->orderBy('id', 'desc');

    }
    if ($user->role_id == 1 || $user->role_id == 2) {
        $giftHistory = $giftHistory->where('country',$user->country)->paginate($perPage,['*'], 'page',$pageNumber);
    } elseif ($user->role_id == 3) {
        $distributor = User::where('id', $loginUserId)->first();
        if($distributor){
            $distributor_id = $distributor->id;
            $country = $distributor->country;
            $salesmanIds = Salesman::where('distributor_id', $distributor_id)
                ->where('distributor_id', $distributor_id)
                ->where('country', $country)
                ->pluck('salesman_id');
            $giftHistory = $giftHistory->whereIn('sales_man_id', $salesmanIds)->paginate($perPage,['*'], 'page',$pageNumber);
        }
    } elseif ($user->role_id == 4) {
        $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
        if($warehouse){
            $ware_id = $warehouse->ware_id;
            $country = $warehouse->country;
            $salesmanIds = Salesman::where('ware_id', $ware_id)
                    ->where('ware_id', $ware_id)
                    ->where('country', $country)
                    ->pluck('salesman_id');
                    $giftHistory = $giftHistory->whereIn('sales_man_id', $salesmanIds)->paginate($perPage,['*'], 'page',$pageNumber);
        }

    }  elseif ($user->role_id == 5){
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if($supervisor){
               $ware_id = $supervisor->ware_id;
               $country = $supervisor->country;
               $salesmanIds = Salesman::where('ware_id', $ware_id)
                    ->where('ware_id', $ware_id)
                    ->where('country', $country)
                    ->pluck('salesman_id');
                    $giftHistory = $giftHistory->whereIn('sales_man_id', $salesmanIds)->paginate($perPage,['*'], 'page',$pageNumber);

            }
        }

    return $this->sendResponse($giftHistory, 'Gift history retrieved successfully');
}


   public  function  submitGiftDetails(Request $request,$id=null):JsonResponse
   {
      $giftHistory=GiftSubmit::with(['outlets','salesman','giftItem'])->where('id',$id)->first();
      return $this->sendResponse($giftHistory,'Gift history retrieved Successfully');
   }



    public function storeGift(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:250',
            'discription' => 'required|string|max:1000',
            'desc_in_china' => 'required|string|max:1000',
            'desc_in_indonesia' => 'required|string|max:1000',
            'quantity' => 'required|integer',
            'cn_name' => 'required|string',
            'bn_name' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'=> 'required'
        ]);

        $userDetails = Auth::user();

        if($userDetails->role_id == 1 || $userDetails->role_id == 2){
           $country = $request->country;
        }else{
            $country = $userDetails->country;
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ]);
        }


        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageNameWithExt = time() . '_' . $image->getClientOriginalName();
            $destinationPath = 'gift';
            $image->move(public_path($destinationPath), $imageNameWithExt);
            $imagePath = url($destinationPath . '/' . $imageNameWithExt);
        }

        $gift = Gift::create([
            'title' => $request->title,
            'discription' => $request->discription,
            'desc_in_china' => $request->desc_in_china,
            'desc_in_indonesia' => $request->desc_in_indonesia,
            'quantity' => $request->quantity,
            'cn_name' => $request->cn_name,
            'bn_name' => $request->bn_name,
            'image' => $imagePath,
            'country' => $country,
            'status'=> $request->status
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Gift created successfully',
            'gift' => $gift
        ]);
    }





       public function deleteGift($id)
       {
            $gift = Gift::find($id);
            if (!$gift) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Gift not found'
                ]);
            }

            if ($gift->image) {
                $imagePath = public_path($gift->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $gift->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Gift deleted successfully',
                'data'=> $gift
            ]);
        }


        public function updateGift(Request $request, $id)
        {

            $validator = Validator::make($request->all(), [
                'title' => 'string|max:250',
                'discription' => 'string|max:1000',
                'desc_in_china'=> 'string|max:1000',
                'desc_in_indonesia'=> 'string|max:1000',
                'quantity' => 'integer',
                'cn_name' => 'string',
                'bn_name' => 'string',
                'image' => 'max:2048',
            ]);

            if ($validator->fails()) {
              return response()->json([
                       'status' => 400,
                       'message' => 'Validation error',
                       'errors' => $validator->errors()
                   ]);
            }

            $gift = Gift::find($id);
            if (!$gift) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Gift not found'
                ]);
            }

            $gift->title = $request->input('title');
            $gift->discription = $request->input('discription');
            $gift->desc_in_china = $request->input('desc_in_china');
            $gift->desc_in_indonesia = $request->input('desc_in_indonesia');
            $gift->quantity = $request->input('quantity');
            $gift->cn_name = $request->input('cn_name');
            $gift->bn_name = $request->input('bn_name');
            $gift->status = $request->input('status');


            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageNameWithExt = time() . '_' . $image->getClientOriginalName();
                $destinationPath = 'gift';

                if (!file_exists(public_path($destinationPath))) {
                    mkdir(public_path($destinationPath), 0755, true);
                }

                $image->move(public_path($destinationPath), $imageNameWithExt);
                $gift->image = url($destinationPath . '/' . $imageNameWithExt);
            }

            $gift->save();

            return response()->json([
                'status' => 200,
                'message' => 'Gift updated successfully',
                'gift' => $gift
            ]);
        }




        public function show($id)
        {
            $gift = Gift::find($id);

            if (!$gift) {
                return response()->json(['message' => 'Gift not found'], 404);
            }

            return response()->json([
                'status'=>200,
                'gift'=>$gift
            ]);
        }


        public function assign(Request $request)
        {

            $validator = Validator::make($request->all(), [
                'salesman_id' => 'required',
                'gift_id' => 'required|exists:gifts,id',
                'quantity' => 'required|integer|min:1',
                'assign_for_date' => 'required|date',
                'warehouse_id' => 'required|exists:warehouses,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

           $gift = Gift::find($request->gift_id);

             if (!$gift) {
                 return response()->json([
                    'message' => 'Gift not found.',
                ], 404);
             }

            $warehouseInventory = $gift->giftInventoriesDetails()->where('warehouse_id',  $request->warehouse_id)->first();

            if (!$warehouseInventory) {
                return response()->json([
                    'message' => 'Warehouse not found for this gift.',
                ], 400);
            }
            if ($warehouseInventory->warehouse_quantities < $request->quantity) {
                return response()->json([
                  'message' => 'Not enough quantity available in the selected warehouse.',
                ], 400);
            }

            $existingAssignment = AssignGift::where([
                'salesman_id' => $request->salesman_id,
                'gift_id' => $request->gift_id,
                'warehouse_id' => $request->warehouse_id,
                "assign_for_date"=>$request->assign_for_date
            ])->first();

            if ($existingAssignment) {
                $existingAssignment->quantity += $request->quantity;
                $existingAssignment->save();

                $warehouseInventory->warehouse_quantities -= $request->quantity;
                $warehouseInventory->save();

                return response()->json([
                    'message' => 'Gift quantity updated for the salesman.',
                    'data' => $existingAssignment,
                ], 200);
            }

            $assignment = AssignGift::create([
                'salesman_id' => $request->salesman_id,
                'gift_id' => $request->gift_id,
                'quantity' => $request->quantity,
                'assign_for_date' => $request->assign_for_date,
                'warehouse_id' => $request->warehouse_id,
                'gifts'=> $gift,
            ]);

            $warehouseInventory->warehouse_quantities -= $request->quantity;
            $warehouseInventory->save();

            return response()->json([
                'message' => 'Gift assigned successfully.',
                'data' => $assignment,
            ], 201);
        }















}
