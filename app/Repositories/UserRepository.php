<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use App\Models\Suppervisor;
use App\Models\Warehouse;
use App\Models\Salesman;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\GiftInventory;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class UserRepository
 */
class UserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'unique_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'created_at',
        //        'roles.name',
    ];

    /**
     * Return searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     */
    public function storeUser($input)
    {

        try {
            DB::beginTransaction();

            $userDetails=Auth::user();

            if(!$input['country']){
                $input['country']=$userDetails->country??'';
            }
            $input['added_by']= $userDetails->id??'';

            $input['password'] = Hash::make($input['password']);

            if (isset($input['role_id']) && $input['role_id'] == 6 && isset($input['supervisor_id'])) {
                $input['supervisor_id'] = $input['supervisor_id'];
            }


            $user = $this->create($input);
            if (isset($input['role_id'])) {
                $adminRole = Role::whereId($input['role_id'])->first();
                // dd($adminRole);
                $user->assignRole($adminRole->name);
            }

            if (isset($input['image']) && ! empty($input['image'])) {
                $user->addMedia($input['image'])->toMediaCollection(User::PATH,
                    config('app.media_disc'));
            }
            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     */
    public function updateUser($input, $id)
    {
        // dd($input);
        try {
            DB::beginTransaction();
            $user = $this->update($input, $id);

            if (isset($input['role_id'])) {
                $adminRole = Role::whereId($input['role_id'])->first();
                $user->syncRoles($adminRole->name);
            }
            if (isset($input['image']) && $input['image']) {
                $user->clearMediaCollection(User::PATH);
                $user['image_url'] = $user->addMedia($input['image'])->toMediaCollection(User::PATH,
                    config('app.media_disc'));
            }
            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return User|\Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function updateUserProfile($input)
    {
        try {
            DB::beginTransaction();
            unset($input['role_id']);

            $user = Auth::user();
            $user->update($input);

            if ((! empty($input['image']))) {
                $user->clearMediaCollection(User::PATH);
                $user->media()->delete();
                $user->addMedia($input['image'])->toMediaCollection(User::PATH, config('app.media_disc'));
            }
            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     */
    // public function getUsers($perPage)
    // {
    //     $loginUserId = Auth::id();
    //     if (Auth::user()->hasRole(Role::ADMIN)){
    //         if (request()->get('returnAll') == 'true'){
    //             $users = $this->paginate($perPage);
    //         } else {
    //             $users = $this->where('role_id',2)->latest()->paginate($perPage);
    //         }
    //     } else {
    //         $users = $this->whereHas('roles', function ($q) {
    //             $q->where('name', '!=', Role::ADMIN);
    //         });

    //         if (request()->get('returnAll') == 'true') {
    //             $users = $users->paginate($perPage);
    //         } else {
    //             $users = $users->where('id', '!=', $loginUserId)->where('role_id',2)->latest()->paginate($perPage);
    //         }
    //     }

    //     return $users;
    // }


    public function getUsers($perPage,$search=null, $sort = null, $sort_name = null)
    {
        $loginUserId = Auth::id();
        $query = User::where('role_id',2);
        if ($search) {
            $query->where('role_id', 2)
            ->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $query->when($sort && $sort_name, function ($q) use ($sort, $sort_name) {
            $q->orderBy($sort_name, $sort);
        });

        return $query->latest()->paginate($perPage);
    }





    // public function getDistributors($perPage, $search = null)
    // {
    //     $loginUserId = Auth::id();
    //     $userDetails = Auth::user();

    //     $query = User::with(['warehouse', 'salesmen']);

    //     if($userDetails->role_id == 5){
    //         $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
    //         if($supervisor){
    //           $distributor_id = $supervisor-> distributor_id;
    //           $query->where('id', $distributor_id);
    //         }
    //     }

    //     if ($userDetails->role_id == 3) {
    //         $query->where('id', $loginUserId);
    //     }

    //     elseif ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
    //         $country = $userDetails->country;
    //         $query->where('country', $country)
    //         ->where('role_id', 3);
    //     }


    //     if ($search) {
    //         $query->where(function($q) use ($search) {
    //             $q->where('first_name', 'like', '%'.$search.'%')
    //               ->orWhere('last_name', 'like', '%'.$search.'%')
    //               ->orWhere('email', 'like', '%'.$search.'%')
    //               ->orWhere('phone', 'like', '%'.$search.'%');
    //         });
    //     }

    //     return $query->latest()->paginate($perPage);
    // }

    public function getDistributors($perPage, $search = null,$sort=null,$sort_name=null)
    {
        $loginUserId = Auth::id();
        $userDetails = Auth::user();

        $query = User::where('role_id',3)->with(['warehouse', 'salesmen']);

        if($userDetails->role_id == 5){
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if($supervisor){
              $distributor_id = $supervisor-> distributor_id;
              $query->where('id', $distributor_id);
            }
        }

        if ($userDetails->role_id == 3) {
            $query->where('id', $loginUserId);
        }
        elseif($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if($warehouse){
                $distributor_id = $warehouse->user_id;
                // dd($distributor_id);
                $country = $userDetails->country;
                $query->where('id', $distributor_id )
                ->where('country', $country);
            }
        }
        elseif ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
            $country = $userDetails->country;
            $query->where('country', $country)
            ->where('role_id', 3);
        }

        if ($search) {
            $query->where('role_id', 3)
            ->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        return $query->when($sort,function($q) use ($sort,$sort_name){
                 $q->orderBy($sort_name,$sort);
        })->latest()->paginate($perPage);
    }



    public function getDistributor( $search = null)
    {
        $loginUserId = Auth::id();
        $userDetails = Auth::user();

        $query = User::with([
            'warehouse' => function ($q) {
                $q->where('status', 1);
            },
            'salesmen'
        ])->where('status', 1);

        if($userDetails->role_id == 5){
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            if($supervisor){
              $distributor_id = $supervisor-> distributor_id;
              $query->where('id', $distributor_id);
            }
        }
        if ($userDetails->role_id == 3) {
            $query->where('id', $loginUserId);
        }
        elseif($userDetails->role_id == 4){
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if($warehouse){
                $distributor_id = $warehouse->user_id;
                $country = $userDetails->country;
                $query->where('id', $distributor_id )
                ->where('country', $country);
            }
        }
        elseif ($userDetails->role_id == 1 || $userDetails->role_id == 2) {
            $country = $userDetails->country;
            $query->where('country', $country)
            ->where('role_id', 3);
        }


        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%'.$search.'%')
                  ->orWhere('last_name', 'like', '%'.$search.'%')
                  ->orWhere('email', 'like', '%'.$search.'%')
                  ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        return $query->latest()->get();
    }

    public function getSupervisor($perPage, $search = null, $sort = null, $sort_name = null)
    {
        $loginUserId = Auth::id();
        $userDetails = Auth::user();
        $loginUser = User::find($loginUserId);

        $query = User::with(['countryDetails', 'regionDetails', 'areaDetails'])
        ->where('role_id',5);

        if ($search) {
            $query->where('role_id', 5)
            ->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }


        if($userDetails->role_id == 3){
            $distributor = User::where('id', $loginUserId)->first();
            if($distributor){
                $distributor_id = $distributor->id;
                $country = $distributor->country;
                $supervisorIds = Suppervisor::where('distributor_id', $distributor_id)
                ->where('country', $country)
                ->pluck('supervisor_id');

                // if ($salesmanIds->isNotEmpty()) {
                    // $users = $this->with(['region'])
                    //               ->whereIn('id', $salesmanIds)
                    //               ->latest()
                    //               ->paginate($perPage);
                    $query->whereIn('id', $supervisorIds);
                // }


            }
        }


        if ($userDetails->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $ware_id = $warehouse->ware_id;
                $supervisorIds = Suppervisor::where('ware_id', $ware_id)->pluck('supervisor_id');
                    $query->whereIn('id', $supervisorIds);
            }
        }


        if ($userDetails->role_id == 5) {
            $query->where('id', $loginUserId);
        }

        // if ($userDetails->role_id == 4) {
        //     $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
        //     if ($warehouse) {
        //         $ware_id = $warehouse->ware_id;
        //         $country = $warehouse->country;
        //         $supervisorIds = Suppervisor::where('ware_id', $ware_id)->where('country', $country)->pluck('supervisor_id');
        //         if ($supervisorIds->isNotEmpty()) {
        //             $users =  $query->whereIn('id', $supervisorIds)
        //             ->latest()->paginate($perPage);
        //         }
        //     }
        // }

        // if($userDetails->role_id == 5){
        //     $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
        //     // dd($supervisor);
        //     if ($supervisor) {
        //         $ware_id = $supervisor->ware_id;
        //         $country = $supervisor->country;
        //         // dd($ware_id);
        //         $salesmanIds = Salesman::where('ware_id', $ware_id)
        //             ->where('ware_id', $ware_id)
        //             ->where('country', $country)
        //             ->pluck('salesman_id');
        //             // dd($salesmanIds);
        //         if ($salesmanIds->isNotEmpty()) {
        //             $users = $this->with(['region'])
        //                           ->whereIn('id', $salesmanIds)
        //                           ->latest()
        //                           ->paginate($perPage);
        //         }
        //     }
        // }

        if ($sort) {
            $query->orderBy($sort_name, $sort);
        }

        if($userDetails->role_id ==1 ||  $userDetails->role_id  == 2){
            $country = $userDetails->country;
            $query->where('country', $country);
            // $users = $query->where('country', $country)
            //                ->latest()
            //                ->paginate($perPage);
            // return $users;
        }
        return $query->latest()->paginate($perPage);
    }



    public function getSalesmans($perPage, $search = null,  $sort = null, $sort_name = null)
    {
        $loginUserId = Auth::id();
        $userDetails = Auth::user();
        $loginUser = User::find($loginUserId);

        $query = User::with(['region'])
        ->where('role_id', 6);

        if ($search) {
            $query->where('role_id', 6)
            ->where(function ($q) use ($search) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if($userDetails->role_id == 3){
            $distributor = User::where('id', $loginUserId)->first();
            if($distributor){
                $distributor_id = $distributor->id;
                $country = $distributor->country;
                $salesmanIds = Salesman::where('distributor_id', $distributor_id)
                ->where('distributor_id', $distributor_id)
                ->where('country', $country)
                ->pluck('salesman_id');

                // if ($salesmanIds->isNotEmpty()) {
                    // $users = $this->with(['region'])
                    //               ->whereIn('id', $salesmanIds)
                    //               ->latest()
                    //               ->paginate($perPage);
                    $query->whereIn('id', $salesmanIds);
                // }


            }
        }

        if ($userDetails->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $ware_id = $warehouse->ware_id;
                $country = $warehouse->country;
                $salesmanIds = Salesman::where('ware_id', $ware_id)
                    ->where('ware_id', $ware_id)
                    ->where('country', $country)
                    ->pluck('salesman_id');
                // if ($salesmanIds->isNotEmpty()) {
                    // $users = $this->with(['region'])
                    //               ->whereIn('id', $salesmanIds)
                    //               ->latest()
                    //               ->paginate($perPage);
                    $query->whereIn('id', $salesmanIds);
                // }
            }
        }


        if($userDetails->role_id == 5){
            $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
            // dd($supervisor);
            if ($supervisor) {
                $ware_id = $supervisor->ware_id;
                $country = $supervisor->country;
                // dd($ware_id);
                $salesmanIds = Salesman::where('ware_id', $ware_id)
                    ->where('ware_id', $ware_id)
                    ->where('country', $country)
                    ->pluck('salesman_id');
                    // dd($salesmanIds);
                // if ($salesmanIds->isNotEmpty()) {
                    // $users = $this->with(['region'])
                    //               ->whereIn('id', $salesmanIds)
                    //               ->latest()
                    //               ->paginate($perPage);
                    $query->whereIn('id', $salesmanIds);
                // }
            }
        }

        if($userDetails->role_id ==1 ||  $userDetails->role_id  == 2){
            $country = $userDetails->country;
            // $users = $query->where('country', $country)
            //                ->latest()
            //                ->paginate($perPage);
            $query->where('country', $country);
            // return $users;
        }
        // return $users ??[];

        $query->when($sort, function ($q) use ($sort, $sort_name) {
            $q->orderBy($sort_name, $sort);
        }, function ($q) {
            $q->latest();
        });

        return $query->paginate($perPage);
    }



    public function getSupervisors($search = null)
    {
        $loginUserId = Auth::id();
        $userDetails = Auth::user();
        $loginUser = User::find($loginUserId);
        $countryCode = $userDetails->country;

        $query = User::with(['countryDetails', 'regionDetails', 'areaDetails'])
        ->where('status', 1)
        ->where('role_id',5);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }


        if ($userDetails->role_id == 3) {
            $distributor = User::where('id', $loginUserId)->first();
            if ($distributor) {
                $distributor_id = $distributor->id;
                $country = $distributor->country;
                $supervisorIds = Suppervisor::where('distributor_id', $distributor_id)
                ->where('country', $country)
                ->pluck('supervisor_id');
                if ($supervisorIds->isNotEmpty()) {
                    $users = $this->with(['countryDetails', 'regionDetails', 'areaDetails'])
                    ->whereIn('id', $supervisorIds)
                    ->get();
                    $users = $query->latest()->get();
                }
            }
        }

        if ($userDetails->role_id == 4) {
            $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
            if ($warehouse) {
                $ware_id = $warehouse->ware_id;
                $country = $warehouse->country;
                $supervisorIds = Suppervisor::where('ware_id', $ware_id)->pluck('supervisor_id');
                if ($supervisorIds->isNotEmpty()) {
                    $users = $this->with(['countryDetails', 'regionDetails', 'areaDetails'])
                    ->whereIn('id', $supervisorIds)
                    ->where('country', $country)
                    ->latest()->get();
                    $users = $query->latest()->get();
                }
            }
        }

        // if ($userDetails->role_id == 4) {
        //     $warehouse = Warehouse::where('ware_id', $loginUserId)->first();
        //     if ($warehouse) {
        //         $ware_id = $warehouse->ware_id;
        //         $country = $warehouse->country;
        //         $supervisorIds = Suppervisor::where('ware_id', $ware_id)->where('country', $country)->pluck('supervisor_id');
        //         if ($supervisorIds->isNotEmpty()) {
        //             $users =  $query->whereIn('id', $supervisorIds)
        //             ->latest()->get();
        //         }
        //     }
        // }

        if ($userDetails->role_id == 5) {
            $users = $this->with(['countryDetails', 'regionDetails', 'areaDetails'])
                    ->where('id', $loginUserId)
                    ->where('country', $countryCode)
                    ->latest()->get();
                    $users = $query->latest()->get();
        }

        // if($userDetails->role_id == 5){
        //     $supervisor = Suppervisor::where('supervisor_id', $loginUserId)->first();
        //     // dd($supervisor);
        //     if ($supervisor) {
        //         $ware_id = $supervisor->ware_id;
        //         $country = $supervisor->country;
        //         // dd($ware_id);
        //         $salesmanIds = Salesman::where('ware_id', $ware_id)
        //             ->where('ware_id', $ware_id)
        //             ->where('country', $country)
        //             ->pluck('salesman_id');
        //             // dd($salesmanIds);
        //         if ($salesmanIds->isNotEmpty()) {
        //             $users = $this->with(['region'])
        //                           ->whereIn('id', $salesmanIds)
        //                           ->latest()
        //                           ->paginate($perPage);
        //         }
        //     }
        // }

        if($userDetails->role_id ==1 ||  $userDetails->role_id  == 2){
            $country = $userDetails->country;
            $users = $query->where('country', $country)
                           ->latest()
                           ->get();
            return $users;
        }
        return $users ??[];
    }








    public function updatePassword(array $input): User
    {
        /** @var User $user */
        $user = Auth::user();
        if (! Hash::check($input['current_password'], $user->password)) {
            throw new UnprocessableEntityHttpException('Current password is invalid.');
        }
        $input['password'] = Hash::make($input['new_password']);
        $user->update($input);

        return $user;
    }
}
