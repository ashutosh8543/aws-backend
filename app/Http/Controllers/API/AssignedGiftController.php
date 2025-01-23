<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssignGift;
use App\Models\Warehouse;
use App\Models\Salesman;
use App\Models\Suppervisor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AssignedGiftController extends Controller
{
    // public function index(Request $request)
    // {
    //     $perPage = getPageSize($request);
    //     $assignedGifts = AssignGift::with(['salesman' => function($query) {
    //         $query->where('role_id', 6);
    //     }])->latest()->paginate($perPage);

    //     return response()->json([
    //         'message' => 'Assigned gifts fetched successfully.',
    //         'data' => $assignedGifts,
    //     ], 200);
    // }

    public function index(Request $request)
    {
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
        $startDate = request()->get('start_date');
        $endDate = request()->get('end_date');
        $loginUserId = Auth::id();
        $userDetails = Auth::user();
        $page=$request->get('page');
        $pageNumber=$page['number']??1;


        if($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            // dd($warehouse);
            if($warehouse){
             $ware_id = $warehouse->ware_id;
             $salesmanIds = Salesman::where('ware_id', $ware_id)
             ->where('ware_id', $ware_id)
             ->pluck('salesman_id');

            $assignedGiftsQuery = AssignGift::with(['salesman' => function($query) {
                $query->where('role_id', 6);
                }])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();


                $assignedGifts = $assignedGiftsQuery->paginate($perPage,['*'], 'page',$pageNumber);
                return response()->json([
                    'message' => 'Assigned gifts fetched successfully.',
                    'data' => $assignedGifts,
                ], 200);
            }
        }


        if($userDetails->role_id == 3){

            $distributor = User::where('id', $loginUserId)->first();
            // dd($distributor);
            if($distributor){
                $distributorId = $distributor -> id;
                $salesmanIds = Salesman::where('distributor_id', $distributorId )
                ->pluck('salesman_id');
                // dd($salesmanIds);

                $assignedGiftsQuery = AssignGift::with(['salesman' => function($query) {
                $query->where('role_id', 6);
                }])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();

                $assignedGifts = $assignedGiftsQuery->paginate($perPage,['*'], 'page',$pageNumber);
                return response()->json([
                    'message' => 'Assigned gifts fetched successfully.',
                    'data' => $assignedGifts,
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
            // dd($salesmanIds);

            $assignedGiftsQuery = AssignGift::with(['salesman' => function($query) {
                $query->where('role_id', 6);
                }])
                ->whereIn('salesman_id', $salesmanIds)
                ->latest();


                $assignedGifts = $assignedGiftsQuery->paginate($perPage,['*'], 'page',$pageNumber);
                return response()->json([
                    'message' => 'Assigned gifts fetched successfully.',
                    'data' => $assignedGifts,
                ], 200);
            }
        }


        try {
            $assignedGiftsQuery = AssignGift::with(['salesman' => function($query) {
                $query->where('role_id', 6);
            }])
            ->latest();


            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $assignedGiftsQuery = AssignGift::with(['salesman']);

            if ($sort_name) {
                $assignedGiftsQuery->orderBy($sort_name, $sort);
            } else {
                $assignedGiftsQuery->orderBy('id', 'desc');
            }


            // Apply date range filter
            if (!is_null($startDate) && !is_null($endDate) && $startDate && $endDate) {
                $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

                $assignedGiftsQuery->whereBetween('assign_for_date', [$startDate, $endDate]);
            }


            if ($search) {
                $searchTerms = explode(' ', $search);

                $assignedGiftsQuery->where(function ($query) use ($searchTerms) {
                    $query->whereHas('salesman', function ($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->where(function($subQuery) use ($term) {
                                $subQuery->where('first_name', 'LIKE', '%' . $term . '%')
                                         ->orWhere('last_name', 'LIKE', '%' . $term . '%');
                            });
                        }
                    })
                    ->orWhereHas('gift', function ($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->where('title', 'LIKE', '%' . $term . '%');
                        }
                    });
                });
            }




            // Fetch the paginated results
            $assignedGifts = $assignedGiftsQuery->paginate($perPage,['*'], 'page',$pageNumber);

            return response()->json([
                'message' => 'Assigned gifts fetched successfully.',
                'data' => $assignedGifts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching assigned gifts.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





}
