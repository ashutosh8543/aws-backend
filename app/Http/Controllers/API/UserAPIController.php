<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateChangePasswordRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Models\POSRegister;
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OpeningAndClosing;
use DB;
use App\Models\Mileage;
use App\Models\Warehouse;
use App\Models\Suppervisor;
use App\Models\Salesman;
use App\Models\Distributor;
use App\Models\Area;

/**
 * Class UserAPIController
 */
class UserAPIController extends AppBaseController
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(Request $request): UserCollection
    {
        $perPage = getPageSize($request);
        $search = '';
        if ($request->filter) {
            $search = $request->filter['search'] ?? '';
        }

        $sort = null;
        $sort_name=ltrim($request->sort,'-');
        if ($request->sort == $sort_name){
            $sort = 'asc';
            $request->request->remove('sort');
        }else{
            $sort = 'desc';
            $request->request->remove('sort');
        }

        $users = $this->userRepository->getUsers($perPage,$search,$sort,$sort_name);
        UserResource::usingWithCollection();
        return new UserCollection($users);
    }

    public function DistributorList(Request $request): UserCollection
    {
         $search = '';

         if($request->filter){

          $search =  $request->filter['search'];
         }
        $perPage = getPageSize($request);
        $loginUserId = Auth::id();
        $userDetails = Auth::user();

        $sort = null;
        $sort_name=ltrim($request->sort,'-');
        if ($request->sort == $sort_name){
            $sort = 'asc';
            $request->request->remove('sort');
        }else{
            $sort = 'desc';
            $request->request->remove('sort');
        }

        if($userDetails->role_id==1 || $userDetails->role_id==2){
            $users = $this->userRepository->getDistributors($perPage, $search,$sort,$sort_name);
        }
        if($userDetails->role_id == 3){
            $users = $this->userRepository->getDistributors($perPage, $search,$sort,$sort_name);
        }

        if($userDetails->role_id == 4){
            $users = $this->userRepository->getDistributors($perPage, $search,$sort,$sort_name);
        }

        if($userDetails->role_id == 5){
            $users = $this->userRepository->getDistributors($perPage, $search,$sort,$sort_name);
        }

        UserResource::usingWithCollection();

        return new UserCollection($users);
    }





    public function SupervisorList(Request $request): UserCollection
    {
        $search = '';
        if($request->filter) {
            $search = $request->filter['search'] ?? '';
        }
        $user_details = Auth::user();
        $perPage = getPageSize($request);

        $sort = null;
        $sort_name=ltrim($request->sort,'-');
        if ($request->sort == $sort_name){
            $sort = 'asc';
            $request->request->remove('sort');
        }else{
            $sort = 'desc';
            $request->request->remove('sort');
        }

        if($user_details->role_id == 5){
            $users = $this->userRepository->getSupervisor($perPage, $search,$sort,$sort_name);
        }
        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $users = $this->userRepository->getSupervisor($perPage, $search,$sort,$sort_name);
        } elseif ($user_details->role_id == 3) {
            $users = $this->userRepository->getSupervisor($perPage, $search, $sort,$sort_name);
        }elseif($user_details->role_id == 4){
            $users = $this->userRepository->getSupervisor($perPage, $search,$sort,$sort_name);
        }
        else {
            $users = $this->userRepository->getSupervisor($perPage, $search,$sort,$sort_name);
        }
        UserResource::usingWithCollection();
        return new UserCollection($users);
    }
    public function SalesmansList(Request $request): UserCollection
    {
        $search = '';

        if ($request->filter) {
            $search = $request->filter['search'] ?? '';
        }

        $user_details = Auth::user();
        $perPage = getPageSize($request);


        $sort = null;
        $sort_name=ltrim($request->sort,'-');
        if ($request->sort == $sort_name){
            $sort = 'asc';
            $request->request->remove('sort');
        }else{
            $sort = 'desc';
            $request->request->remove('sort');
        }

        if($user_details->role_id == 5){
            $users = $this->userRepository->getSalesmans($perPage, $search, $sort,$sort_name);
        }
        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $users = $this->userRepository->getSalesmans($perPage, $search, $sort,$sort_name);
        } elseif ($user_details->role_id == 3) {
            $users = $this->userRepository->getSalesmans($perPage, $search, $sort,$sort_name);
        }elseif($user_details->role_id == 4){
            $users = $this->userRepository->getSalesmans($perPage, $search, $sort,$sort_name);
        }
        else {
            $users = $this->userRepository->getSalesmans($perPage, $search, $sort,$sort_name);
        }
        UserResource::usingWithCollection();
        return new UserCollection($users);
    }

    public function showSalesMan(): JsonResponse
    {
        $salesmen = Salesman::with(['salesManDetails', 'giftInventories'])
        ->where('status', 1)
        ->latest()->get();
        return response()->json($salesmen);
    }

    public function store(CreateUserRequest $request): UserResource
    {
        $userDetails=Auth::user();
        $input = $request->all();


        if($input['role_id'] == 4){
            $new_user = User::find($input['distributor_id']);
            $input['country'] = $new_user ->country;
            $input['region'] = $new_user->region;
        }


        if (isset($input['role_id']) && in_array($input['role_id'], [1, 2])) {
            $input['country'] = $userDetails->country ?? '';
        }


        if( $input['role_id']==5){
          $new_user =   User::find($input['distributor_id']);
          $input['country'] = $new_user ->country;
          $input['region'] = $new_user->region;
           $input['area'] = Area::where('region_id', $new_user->region)->first()->id?? null;
        }

        if($input['role_id']==6){
            $new_user =   User::find($input['distributor_id']);
            $input['country'] = $new_user ->country;
            $input['region'] = $new_user->region;
            $input['area'] = Area::where('region_id', $new_user->region)->first()->id ?? null;
          }

        $user = $this->userRepository->storeUser($input);

        if(!empty($user)){
            $unique_code="YSQ#".$user->id;
            $user->where('id',$user->id)->update(['unique_code'=>$unique_code]);
        }

        if(!empty($user) && $input['role_id']==4){
        Warehouse::create([
                'ware_id'=> $user->id??'',
                'user_id'=>$input['distributor_id'],
                'name'=>$input['first_name'].' '.$input['last_name'],
                'phone'=>$input['phone'],
                'country'=>User::where('id',$input['distributor_id'])->first()->country??'',
                'area'=>$input['area'],
                'email'=>$input['email'],
                'status'=>$input['status']
            ]);
        }
        if(!empty($user) && $input['role_id']==5){
            // dd($request->all());
            Suppervisor::create(            [
                    'ware_id'=> $input['ware_id'],
                    'distributor_id'=>$input['distributor_id'],
                    'supervisor_id'=>$user->id??'',
                    'country'=>$input['country'],
                    'status'=>$input['status']
                    // 'name'=>$input['first_name'],
                    // 'phone'=>$input['phone'],
                    // 'country'=>User::where('id',$input['distributor_id'])->first()->country??'',
                    // 'city',
                    // 'email'=>$input['email'],
                    // 'zip_code',
                ]);
            }
        if(!empty($user) && $input['role_id']==6){
            Salesman::create(            [
                    'ware_id'=> $input['ware_id']??'',
                    'distributor_id'=>$input['distributor_id'],
                    'salesman_id'=>$user->id??'',
                    'country'=>$input['country'],
                    'supervisor_id'=>$input['supervisor_id'],
                    'status'=>$input['status']
                    // 'name'=>$input['first_name'],
                    // 'phone'=>$input['phone'],
                    // 'country'=>User::where('id',$input['distributor_id'])->first()->country??'',
                    // 'city',
                    // 'email'=>$input['email'],
                    // 'zip_code',
                ]);
            }
        return new UserResource($user);
    }

    public function show($id): UserResource
    {
         $user = $this->userRepository->with(['supervisor','supervisor.supervisorDetails','supervisor.supervisorWarehouse','salesman.warehouse','salesman.warehouse.suppervisors.warehouseSuppervisor'])->find($id);
         $supervisior = Suppervisor::where('supervisor_id', $id)->first();

         $saleman = Salesman::where('salesman_id', $id)->first();

         if(!empty($saleman)){
            // dd($saleman);
            $warehouselist = User::find($saleman['ware_id']);
            $user->warehouseList = $warehouselist;
          }
         if(!empty($supervisior)){
           $distributors = User::find($supervisior['distributor_id']);
           $user->distributorlist= $distributors;
         }
        return new UserResource($user);
    }

    /**
     * @return UserResource|JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if (Auth::id() == $user->id){
            return $this->sendError('User can\'t be updated.');
        }
        $input = $request->all();
        if( $input['role_id']==5){
            $new_user =   User::find($input['distributor_id']);
            $input['country'] = $new_user ->country;
            $input['region'] = $new_user->region;
            $input['area'] = Area::where('region_id', $new_user->region)->first()->id?? null;
          }


        if ($request->filled('password')) {
            $input['password'] = Hash::make($request->password);
        } else {
            unset($input['password']);
        }

          if($input['role_id'] == 4){
            $new_user = User::find($input['distributor_id']);
            $input['country'] = $new_user ->country;
            $input['region'] = $new_user->region;
        }

        // dd($input);
        if($input['role_id']==6){
            $new_user =   User::find($input['distributor_id']);
            $input['country'] = $new_user ->country??'';
            $input['region'] = $new_user->region??'';
            $input['area'] = Area::where('region_id', $new_user->region)->first()->id ?? null;

          }
        $user = $this->userRepository->updateUser($input, $user->id);

        if(!empty($user) && $input['role_id']==4){
            Warehouse::where('id',$input['warehouse_id'])->update([
                    'user_id'=>$input['distributor_id'],
                    'name'=>$input['first_name'].' '.$input['last_name'],
                    'phone'=>$input['phone'],
                    'country'=>User::where('id',$input['distributor_id'])->first()->country??'',
                    'area'=>$input['area'],
                    // 'city',
                    'email'=>$input['email'],
                    'status'=>$input['status']
                    // 'zip_code',
                ]);
            }

            if(!empty($user) && $input['role_id']==5){
                Suppervisor::where("id",$input['supervisor_id'])->update([
                        'ware_id'=> $input['ware_id'],
                        'country'=>$input['country'],
                        'distributor_id'=>$input['distributor_id'],
                        'status'=>$input['status']
                        // 'supervisor_id'=>$user->id??'',
                        // 'name'=>$input['first_name'],
                        // 'phone'=>$input['phone'],
                        // 'country'=>User::where('id',$input['distributor_id'])->first()->country??'',
                        // 'city',
                        // 'email'=>$input['email'],
                        // 'zip_code',
                    ]);
                }
            if(!empty($user) && $input['role_id']==6){
                Salesman::where('id',$input['salesman_id'])->update([
                        'ware_id'=> $input['ware_id'],
                        'country'=>$input['country'],
                        'distributor_id'=>$input['distributor_id'],
                        'supervisor_id'=>$input['supervisor_id'],
                        'status'=>$input['status']
                        // 'salesman_id'=>$user->id??'',
                        // 'name'=>$input['first_name'],
                        // 'phone'=>$input['phone'],
                        // 'country'=>User::where('id',$input['distributor_id'])->first()->country??'',
                        // 'city',
                        // 'email'=>$input['email'],
                        // 'zip_code',
                    ]);
                }
        return new UserResource($user);
    }



    // public function update(UpdateUserRequest $request, User $user)
    // {
    //     if (Auth::id() == $user->id) {
    //         return $this->sendError('User can\'t be updated.');
    //     }

    //     $input = $request->all();

    //     if (in_array($input['role_id'], [4, 5, 6])) {
    //         $new_user = User::find($input['distributor_id']);
    //         if ($new_user) {
    //             $input['country'] = $new_user->country;
    //             $input['region'] = $new_user->region;
    //             if ($input['role_id'] == 6) {
    //                 $input['area'] = Area::where('region_id', $new_user->region)->first    ()->id ?? null;
    //             }
    //         }
    //     }

    //     if ($request->filled('password')) {
    //         $input['password'] = Hash::make($request->password);
    //     } else {
    //         unset($input['password']);
    //     }

    //     $user = $this->userRepository->updateUser($input, $user->id);

    //     if (!empty($user)) {
    //         if ($input['role_id'] == 4) {
    //             Warehouse::where('id', $input['warehouse_id'])->update([
    //                 'user_id' => $input['distributor_id'],
    //                 'name' => $input['first_name'] . ' ' . $input['last_name'],
    //                 'phone' => $input['phone'],
    //                 'country' => User::where('id', $input['distributor_id'])->first()    ->country ?? '',
    //                 'area' => $input['area'],
    //                 'email' => $input['email'],
    //             ]);
    //         }

    //         if ($input['role_id'] == 5) {
    //             Suppervisor::where("id", $input['supervisor_id'])->update([
    //                 'ware_id' => $input['ware_id'],
    //                 'distributor_id' => $input['distributor_id'],
    //             ]);
    //         }

    //         if ($input['role_id'] == 6) {
    //             Salesman::where('id', $input['salesman_id'])->update([
    //                 'ware_id' => $input['ware_id'],
    //                 'distributor_id' => $input['distributor_id'],
    //             ]);
    //         }
    //     }

    //         return new UserResource($user);
    // }


    public function destroy(User $user): JsonResponse
    {
        if (Auth::id() == $user->id) {
            return $this->sendError('User can\'t be deleted.');
        }
        if($user->role_id==5){
            Suppervisor::where("supervisor_id",$user->id)->delete();
        }
        if($user->role_id==6){
            Salesman::where("salesman_id",$user->id)->delete();
        }
        $this->userRepository->delete($user->id);

        return $this->sendSuccess('User deleted successfully');
    }

    public function editProfile(): UserResource
    {
        $user = Auth::user();

        return new UserResource($user);
    }

    public function updateProfile(UpdateUserProfileRequest $request): UserResource
    {
        $input = $request->all();
        $updateUser = $this->userRepository->updateUserProfile($input);

        return new UserResource($updateUser);
    }

    public function changePassword(UpdateChangePasswordRequest $request): JsonResponse
    {
        $input = $request->all();
        try {
            $this->userRepository->updatePassword($input);

            return $this->sendSuccess('Password updated successfully');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function updateLanguage(Request $request): JsonResponse
    {
        $language = $request->get('language');
        $user = Auth::user();
        $user->update([
            'language' => $language,
        ]);

        return $this->sendResponse($user->language, 'Language Updated Successfully');
    }

    public function config(Request $request)
    {
        $user = Auth::user();

        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        // dd($userPermissions);

        $composerFile = file_get_contents('../composer.json');
        $composerData = json_decode($composerFile, true);
        $currentVersion = isset($composerData['version']) ? $composerData['version'] : '';
        $dateFormat = getSettingValue('date_format');

        $openRegister = POSRegister::where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->exists();

        return $this->sendResponse([
            'permissions' => $userPermissions,
            'version' => $currentVersion,
            'date_format' => $dateFormat,
            'is_version' => getSettingValue('show_version_on_footer'),
            'is_currency_right' => getSettingValue('is_currency_right'),
            'open_register' => $openRegister ? false : true,
        ], 'Config retrieved successfully.');
    }


        // public function openingClosingCashList(Request $request): JsonResponse
        // {
        //       $perPage = getPageSize($request);
        //       $startDate = $request->get('start_date');
        //       $endDate = $request->get('end_date');
        //       $loginUserId = Auth::id();
        //       $userDetails = Auth::user();

        //    //   dd($userDetails);

        //       // Debug: Log the received dates
        //       \Log::info('Start Date: ' . $startDate);
        //       \Log::info('End Date: ' . $endDate);


        //     if ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
        //         $country = $userDetails->country;
        //         $openingCashQuery = OpeningAndClosing::with(['sales_man'])
        //         ->where('country', $country)
        //         ->latest();
        //         $openingCashList = $openingCashQuery->paginate($perPage);
        //         return $this->sendResponse($openingCashList, 'All cash list retrieved successfully');
        //     }


        //     if ($userDetails->role_id == 3) {
        //        $distributor = User::where('id', $loginUserId)->first();
        //         if ($distributor) {
        //            $distributorId = $distributor->id;
        //            $country = $distributor->country;
        //            $salesmanIds = Salesman::where('distributor_id', $distributorId)->pluck('salesman_id');

        //            $openingCashQuery = OpeningAndClosing::with(['sales_man'])
        //               ->whereIn('sales_man_id', $salesmanIds)
        //               ->where('country', $country)
        //               ->latest();

        //             $openingCashList = $openingCashQuery->paginate($perPage);
        //             return $this->sendResponse($openingCashList, 'All cash list retrieved successfully');
        //         }
        //     }


        //     if ($userDetails->role_id == 4) {
        //         $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
        //         if ($warehouse) {
        //            $ware_id = $warehouse->ware_id;
        //            $country = $warehouse->country;
        //            $salesmanIds = Salesman::where('ware_id', $ware_id)
        //             ->where('country', $country)
        //             ->pluck('salesman_id');

        //             $openingCashQuery = OpeningAndClosing::with(['sales_man'])
        //                 ->whereIn('sales_man_id', $salesmanIds)
        //                 ->where('country', $country)
        //                 ->latest();

        //             $openingCashList = $openingCashQuery->paginate($perPage);
        //             return $this->sendResponse($openingCashList, 'All cash list retrieved successfully');
        //         }
        //     }


        //     if($userDetails->role_id == 5){
        //        $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
        //         if($supervisor){
        //            $ware_id = $supervisor->ware_id;
        //            $country = $supervisor->country;
        //            $salesmanIds = Salesman::where('ware_id', $ware_id)
        //                ->where('ware_id', $ware_id)
        //                ->where('country', $country)
        //                ->pluck('salesman_id');
        //                // dd($salesmanIds);

        //             $openingCashQuery = OpeningAndClosing::with(['sales_man'])
        //             ->whereIn('sales_man_id', $salesmanIds )
        //             ->latest();

        //             $openingCashList = $openingCashQuery->paginate($perPage);
        //             return $this->sendResponse($openingCashList, 'All cash list retrieved successfully');
        //         }
        //     }

        //     try {
        //         $openingCashQuery = OpeningAndClosing::with(['sales_man'])
        //         ->latest();

        //         if ($startDate && $endDate) {
        //             $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
        //             $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

        //             $openingCashQuery->whereBetween('created_at', [$startDate, $endDate]);
        //         }

        //         $openingCashList = $openingCashQuery->paginate($perPage);

        //         return $this->sendResponse($openingCashList, 'All cash list retrieved successfully');
        //     } catch (\Exception $e) {
        //         return response()->json([
        //             'message' => 'Error fetching cash list.',
        //             'error' => $e->getMessage(),
        //         ], 500);
        //     }
        // }


    public function openingClosingCashList(Request $request): JsonResponse
    {
        $perPage = getPageSize($request);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->filter['search'] ?? '';
        $loginUserId = Auth::id();
        $userDetails = Auth::user();

        $warehouse_id=$request->get('warehouse_id');
        try {
            $openingCashQuery = OpeningAndClosing::with(['sales_man']);

            $sort = null;
            $sort_name = ltrim($request->sort, '-');
            if ($request->sort == $sort_name) {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }

            $openingCashQuery = OpeningAndClosing::with(['sales_man']);

            if ($sort_name) {
                $openingCashQuery->orderBy($sort_name, $sort);
            } else {
                $openingCashQuery->orderBy('id', 'desc');
            }

            if ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
                $country = $userDetails->country;
                $openingCashQuery->where('country', $country);
            } elseif ($userDetails->role_id == 3) {
                $distributor = User::where('id', $loginUserId)->first();
                if ($distributor) {
                   $distributorId = $distributor->id;
                   $country = $distributor->country;
                   $salesmanIds = Salesman::where('distributor_id', $distributorId)->pluck('salesman_id');
                   $openingCashQuery->whereIn('sales_man_id', $salesmanIds)
                   ->where('country', $country);
                }
            } elseif ($userDetails->role_id == 4) {
                $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
                if ($warehouse) {
                    $ware_id = $warehouse->ware_id;
                    $country = $warehouse->country;
                    $salesmanIds = Salesman::where('ware_id', $ware_id)
                       ->where('country', $country)
                       ->pluck('salesman_id');
                    $openingCashQuery->whereIn('sales_man_id', $salesmanIds)
                    ->where('country', $country);
                }
            } elseif ($userDetails->role_id == 5) {
                $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
                if ($supervisor) {
                    $ware_id = $supervisor->ware_id;
                    $country = $supervisor->country;
                    $salesmanIds = Salesman::where('ware_id', $ware_id)
                        ->where('country', $country)
                        ->pluck('salesman_id');
                    $openingCashQuery->whereIn('sales_man_id', $salesmanIds);
                }
            }


            if ($startDate && $endDate) {
                $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
                $openingCashQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if (!empty($search)) {
                $searchTerms = explode(' ', $search);
                $openingCashQuery->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->whereHas('sales_man', function ($query) use ($term) {
                            $query->where('first_name', 'like', '%' . $term . '%')
                                ->orWhere('last_name', 'like', '%' . $term . '%');
                        });
                    }
                });
            }

            if($warehouse_id){
                $warehouse = Warehouse::where('id',$warehouse_id)->first();
                    if ($warehouse) {
                        $ware_id = $warehouse->ware_id;
                        $country = $warehouse->country;
                        $salesmanIds = Salesman::where('ware_id', $ware_id)
                           ->where('country', $country)
                           ->pluck('salesman_id');
                        $openingCashQuery->whereIn('sales_man_id', $salesmanIds)
                        ->where('country', $country);
                    }
             }
            $openingCashList = $openingCashQuery->latest()->paginate($perPage);


            return $this->sendResponse($openingCashList, 'All cash list retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
            'message' => 'Error fetching cash list.',
            'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function MileageRecords(Request $request): JsonResponse
    {
        $perPage = getPageSize($request);
        $search = $request->filter['search'] ?? '';
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $loginUserId = Auth::id();
        $userDetails = Auth::user();

        $warehouse_id=$request->get('warehouse_id');

        $mileage_type=$request->get('payment_type');


        if($userDetails->role_id == 3){
            $distributor = User::where('id', $loginUserId)->first();

            if($distributor){
                $distributorId = $distributor -> id;
                $country = $distributor->country;
                $salesmanIds = Salesman::where('distributor_id', $distributorId )
                ->where('country', $country)
                ->pluck('salesman_id');
                // dd($salesmanIds);

                $mileageQuery = Mileage::with(['sales_man' => function($query) {
                    $query->where('role_id', 6);
                }])
                ->whereIn('sales_man_id', $salesmanIds)
                ->latest();

                if($warehouse_id){
                    $warehouse = Warehouse::where('id',$warehouse_id)->first();
                        if ($warehouse) {
                            $ware_id = $warehouse->ware_id;
                            $country = $warehouse->country;
                            $salesmanIds = Salesman::where('ware_id', $ware_id)
                               ->where('country', $country)
                               ->pluck('salesman_id');
                            $mileageQuery->whereIn('sales_man_id', $salesmanIds)
                            ->where('country', $country);
                        }
                }
                if($mileage_type){
                    $mileage_type = $mileage_type==1?'start':'end';
                    $data = $mileageQuery->whereType($mileage_type)->paginate($perPage);
                }else{
                    $data = $mileageQuery->paginate($perPage);
                }
                return $this->sendResponse($data, 'Mileage record for distributor fetched successfully');
            }
        }


        if($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();

            if($warehouse){
             $ware_id = $warehouse->ware_id;
             $country = $warehouse->country;
             $salesmanIds = Salesman::where('ware_id', $ware_id)
             ->where('ware_id', $ware_id)
             ->where('country', $country)
             ->pluck('salesman_id');

             $mileageQuery = Mileage::with(['sales_man' => function($query) {
                $query->where('role_id', 6);
            }])
            ->whereIn('sales_man_id', $salesmanIds)
            ->latest();

            if($warehouse_id){
                $warehouse = Warehouse::where('id',$warehouse_id)->first();
                    if ($warehouse) {
                        $ware_id = $warehouse->ware_id;
                        $country = $warehouse->country;
                        $salesmanIds = Salesman::where('ware_id', $ware_id)
                           ->where('country', $country)
                           ->pluck('salesman_id');
                        $mileageQuery->whereIn('sales_man_id', $salesmanIds)
                        ->where('country', $country);
                    }
            }

            if($mileage_type){
                $mileage_type = $mileage_type==1?'start':'end';
                $data = $mileageQuery->whereType($mileage_type)->paginate($perPage);
            }else{
                $data = $mileageQuery->paginate($perPage);
            }

            return $this->sendResponse($data, 'Mileage record for warehouse fetched successfully');

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
            $mileageQuery = Mileage::with(['sales_man' => function($query) {
                $query->where('role_id', 6);
            }])
            ->whereIn('sales_man_id', $salesmanIds)
            ->latest();

            if($warehouse_id){
                $warehouse = Warehouse::where('id',$warehouse_id)->first();
                    if ($warehouse) {
                        $ware_id = $warehouse->ware_id;
                        $country = $warehouse->country;
                        $salesmanIds = Salesman::where('ware_id', $ware_id)
                           ->where('country', $country)
                           ->pluck('salesman_id');
                        $mileageQuery->whereIn('sales_man_id', $salesmanIds)
                        ->where('country', $country);
                    }
            }

            if($mileage_type){
                $mileage_type = $mileage_type==1?'start':'end';
                $data = $mileageQuery->whereType($mileage_type)->paginate($perPage);
            }else{
                $data = $mileageQuery->paginate($perPage);
            }

            return $this->sendResponse($data, 'Mileage record for supervisor fetched successfully');
            }
        }


        try {
            $mileageQuery = Mileage::with(['sales_man' => function($query) {
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

            $mileageQuery = Mileage::with(['sales_man']);

            if ($sort_name) {
                $mileageQuery->orderBy($sort_name, $sort);
            } else {
                $mileageQuery->orderBy('id', 'desc');
            }

            if ($startDate && $endDate) {
                $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();

                $mileageQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($search) {
                $mileageQuery->where(function($q) use ($search) {
                    $searchTerms = explode(' ', $search);
                    foreach ($searchTerms as $term) {
                        $q->whereHas('sales_man', function($query) use ($term) {
                            $query->where('first_name', 'LIKE', "%$term%")
                                  ->orWhere('last_name', 'LIKE', "%$term%");
                        });
                    }
                });
            }


            if($warehouse_id){
                $warehouse = Warehouse::where('id',$warehouse_id)->first();
                    if ($warehouse) {
                        $ware_id = $warehouse->ware_id;
                        $country = $warehouse->country;
                        $salesmanIds = Salesman::where('ware_id', $ware_id)
                           ->where('country', $country)
                           ->pluck('salesman_id');
                        $mileageQuery->whereIn('sales_man_id', $salesmanIds)
                        ->where('country', $country);
                    }
            }

            if($mileage_type){
                $mileage_type = $mileage_type==1?'start':'end';
                $data = $mileageQuery->whereType($mileage_type)->paginate($perPage);
            }else{
                $data = $mileageQuery->paginate($perPage);
            }

            return $this->sendResponse($data, 'Mileage record fetched successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }



        public function fetchMileage(Request $request,$id=null): JsonResponse
        {
            $data=Mileage::with(['sales_man'])->find($id);
            return $this->sendResponse($data ,' retrieved Successfully');
        }



        // public function fetchMileage(Request $request, $id = null): JsonResponse
        // {
        //     $perPage = getPageSize($request);
        //     $mileageQuery = Mileage::with(['sales_man']);

        //     if ($id) {
        //         $mileage = $mileageQuery->find($id);

        //         if (!$mileage) {
        //             return response()->json([
        //                 'message' => 'Mileage record not found.'
        //             ], 404);
        //         }

        //         return $this->sendResponse($mileage, 'Retrieved Successfully');
        //     }

        //     $search = $request->get('search');
        //     if ($search) {
        //         $mileageQuery->where('some_field', 'LIKE', "%$search%");
        //     }

        //     $startDate = $request->get('start_date');
        //     $endDate = $request->get('end_date');

        //     if ($startDate && $endDate) {
        //         $mileageQuery->whereBetween('date', [$startDate, $endDate]);
        //     }

        //     $mileageData = $mileageQuery->latest()->paginate($perPage);

        //     return $this->sendResponse($mileageData, 'Retrieved Successfully');
        // }



    public function addCashAmount(Request $request):JsonResponse
    {
                  $created_by=Auth::user()->id;
                  $OpeningCashList  =   new OpeningAndClosing();
                  $OpeningCashList->sales_man_id=$request->sales_man_id;
                  $OpeningCashList->cash=$request->cash;
                  $OpeningCashList->type=$request->type;
                  $OpeningCashList->country = $request->country;
                  $OpeningCashList->created_by =  $created_by;
                  $OpeningCashList->save();

                  if($OpeningCashList->type=="opening") {
                    $OpeningCashList->update(['unique_id'=>"CSHO_".$OpeningCashList->id]);
                  }else{
                    $OpeningCashList->update(['unique_id'=>"CSHC_".$OpeningCashList->id]);
                  }
                  return $this->sendSuccess('Cash asigned successfully');

   }





     public function showUsersByRole(Request $request, $roleId=null) {
        $users = User::where('role_id', $roleId)->get();

        return response()->json([
            'status' => 200,
            'users' => $users
        ], 200);
    }

    public function DistributorsList(Request $request): UserCollection
    {
         $search = '';

         if($request->filter){

          $search =  $request->filter['search'];
         }
        // $perPage = getPageSize($request);
        $loginUserId = Auth::id();
        $userDetails = Auth::user();



        if($userDetails->role_id==1 || $userDetails->role_id==2){
            $users = $this->userRepository->getDistributor($search);
        }
        if($userDetails->role_id == 3){
            $users = $this->userRepository->getDistributor($search);
        }

        if($userDetails->role_id == 4){
            $users = $this->userRepository->getDistributor($search);
        }

        if($userDetails->role_id == 5){
            $users = $this->userRepository->getDistributor($search);
        }

        UserResource::usingWithCollection();

        return new UserCollection($users);
    }



    public function SupervisorListAll(Request $request): UserCollection
    {
        $search = '';
        if($request->filter) {
            $search = $request->filter['search'] ?? '';
        }
        $user_details = Auth::user();

        if($user_details->role_id == 5){
            $users = $this->userRepository->getSupervisors($search);
        }
        if ($user_details->role_id == 1 || $user_details->role_id == 2) {
            $users = $this->userRepository->getSupervisors($search);
        } elseif ($user_details->role_id == 3) {
            $users = $this->userRepository->getSupervisors($search);
        }elseif($user_details->role_id == 4){
            $users = $this->userRepository->getSupervisors($search);
        }
        else {
            $users = $this->userRepository->getSupervisors($search);
        }
        UserResource::usingWithCollection();
        return new UserCollection($users);
    }

    // public function changeActiveStatus($id): UserResource
    // {

    //     $users = User::findOrFail($id);
    //     $userDetails = Auth::user();

    //     $status = !$users->status;
    //     $users->update(['status' => $status]);

    //     return new UserResource($users);
    // }


    public function changeActiveStatus($id): UserResource
   {
    $users = User::findOrFail($id);
    $userDetails = Auth::user();
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
