<?php

namespace App\Models;

use App\Models\Contracts\JsonResourceful;
use App\Notifications\ResetPasswordNotification;
use App\Traits\HasJsonResourcefulData;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use App\Models\PriceInventory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Role;
use App\Models\Product;
use App\Models\Region;
use App\Models\GiftInventory;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $status
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 *
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 *
 * @property-read string $image_url
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property string $language
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLanguage($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements HasMedia, JsonResourceful, CanResetPassword
{
    use SoftDeletes, HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia, HasJsonResourcefulData;

    const JSON_API_TYPE = 'users';

    public const PATH = 'user_image';

    protected $appends = ['image_url'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'language',
        'country',
        'region',
        'fcm_tocken',
        'added_by',
        'user_id',
        'unique_code',
        'area',
        'distributor_id',
        'address',
        'latitude',
        'longitude',
        'supervisor_id',
        'status',
    ];

    // public static $rules = [
    //     // 'country'=>'required',
    //     // 'region'=>'required',
    //     'first_name' => 'required',
    //     // 'last_name' => 'required',
    //     'email' => 'required|email|unique:users',
    //     'phone' => 'required|numeric',
    //     'password' => 'required|min:6',
    //     'confirm_password' => 'required|min:6|same:password',
    //     'image' => 'image|mimes:jpg,jpeg,png',


    // ];

    public static function rules($request)
    {
        return [
        // 'country'=>'required',
        // 'region'=>'required',
        'first_name' => 'required',
        // 'last_name' => 'required',
        // 'email' => 'required|email|unique:users',
        'email' => [
            'required',
            'email',
            Rule::unique('users')->where(function ($query) use ($request) {
                $query->where('role_id', $request['role_id']);
            }),
          ],
        'phone' => 'required|numeric',
        'password' => 'required|min:6',
        'confirm_password' => 'required|min:6|same:password',
        'image' => 'image|mimes:jpg,jpeg,png',
       ];
}

public static function updaterules($request)
{
    $userId = $request['user_id'];
    return [
        'first_name' => 'required',
        'email' => [
            'required',
            'email',
            Rule::unique('users')->where(function ($query) use ($request) {
                $query->where('role_id', $request['role_id']);
            })->ignore($userId),
        ],
        'phone' => 'required|numeric',
        'password' => 'required|min:6',
        'confirm_password' => 'required|min:6|same:password',
        'image' => 'image|mimes:jpg,jpeg,png',
    ];
}

    public function getImageUrlAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(User::PATH)->first();
        if (! empty($media)) {
            return $media->getFullUrl();
        }

        return '';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function prepareLinks(): array
    {
        return [
            'self' => route('users.show', $this->id),
        ];
    }

    public function prepareAttributes(): array
    {
        $fields = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'unique_code'=> $this->unique_code,
            'supervisor_id'=>$this->supervisor_id,
            'role_id'=>$this->role_id,
            'region'=>$this->region,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image_url,
            'role' => $this->roles,
            'created_at' => $this->created_at,
            'language' => $this->language,
            'country'=>$this->country,
            'fcm_tocken'=>$this->fcm_tocken,
            'distributorDetails'=>$this->supervisor,
            'distributor'=> $this->distributorQuantity,
            'supervisor'=>$this->supervisor,
            'salesmen'=>$this->salesmen,
            'salesman_details'=>$this->salesman,
            // 'region'=>$this->region,
            'countryDetails'=>$this->countryDetails,
            'regionDetails'=>$this->regionDetails,
            'areaDetails'=>$this->areaDetails,
            'warehouse'=>$this->warehouse,
            'salesmanDistributorDetail'=>$this->salesmanDistributorDetail,
            'salemanWarehouseDetail'=>$this->salemanWarehouseDetail,
            'latitude'=>$this->latitude,
            'longitude'=>$this->longitude,
            'lastLoginDetails'=>$this->lastLoginDetails($this->id),
            'status'=>$this->status,
            'salesmanSupervisor'=>$this->salesmanSupervisor($this->supervisor_id)
        ];

        return $fields;
    }

    public function sendPasswordResetNotification($token)
    {
        $url = url('/#/reset-password/'.$token);

        $this->notify(new ResetPasswordNotification($url));
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }


    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function products() {
        return $this->hasMany(Product::class, 'distributor_id');
    }

    public function warehouse()
    {
       return $this->hasMany(Warehouse::class);
    }

    public function distributorDetails()
    {
      return $this->hasOne(Suppervisor::class, 'distributor_id', 'id' );
    }

    public function distributorQuantity(){
        return $this->hasOne(Distributor::class, 'distributor_id', 'id');
    }

    public function supervisor(){
        return $this->hasOne(Suppervisor::class,'supervisor_id','id');
    }

    public function salesmen() {
        return $this->hasMany(Salesman::class, 'distributor_id', 'id');
    }

    public function region()
    {
      return $this->belongsTo(Region::class);
    }


    public function assignedCustomers()
    {
        return $this->hasMany(AssignCustomer::class, 'assign_by');
    }

    public function salesman(){
        return $this->hasMany(Salesman::class, 'salesman_id', 'id');
    }

    public function countryDetails(){
        return $this->hasOne(Country::class, 'id','country');
    }

    public function regionDetails(){
        return $this->hasOne(Region::class, 'id','region');
    }

    public function areaDetails(){
        return $this->hasOne(Area::class, 'id', 'area');
    }


    public function salesmanDistributorDetail(){
        return $this->hasOne(User::class, 'id', 'distributor_id');
    }

    public function giftInventories()
    {
        return $this->hasMany(GiftInventory::class, 'distributor_id', 'id')
        ->where('gift_id');
    }

    public function productInventories()
    {
        return $this->hasMany(ProductInventory::class, 'distributor_id');
    }

    // public function salemanWarehouseDetail(){
    //     return $this->hasOne(Warehouse::class, 'sale',  'id');
    // }

    public function lastLoginDetails($user_id){
        return UserLogin::where('user_id', $user_id)->orderBy('id','desc')->first();
    }

    public function salesmanSupervisor($supervisor_id){
        return $this->where('id',$supervisor_id)->first();
    }




}
