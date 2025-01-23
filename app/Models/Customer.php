<?php

namespace App\Models;

use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Salesman;
use App\Models\AssignCustomersList;

/**
 * App\Models\Customer
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $country
 * @property string $city
 * @property string $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereUpdatedAt($value)
 *
 * @property string|null $dob
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Quotation[] $quotations
 * @property-read int|null $quotations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sale[] $sales
 * @property-read int|null $sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SaleReturn[] $salesReturns
 * @property-read int|null $sales_returns_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereDob($value)
 *
 * @mixin \Eloquent
 */
class Customer extends BaseModel
{
    use SoftDeletes, HasFactory, HasJsonResourcefulData;

    protected $table = 'customers';

    const JSON_API_TYPE = 'customers';


    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'city',
        'address',
        'latitude',
        'longitude',
        'credit_limit',
        'dob',
        'user_id',
        'postal_code',
        'image',
        'warehouse_id',
        'added_by',
        'chanel_id',
        'unique_code',
        'area_id',
        'salesman_id',
        'distributor_id',
        'sub_area_id',
        'status'

    ];

    public static $rules = [
        'name' => 'required',
        // 'email' => 'required|email|unique:customers',
        'phone' => 'required|numeric|unique:customers',
        'country' => 'required',
        // 'city' => 'required',
        'address' => 'required',
        'postal_code'=>'required',
        'latitude' =>'required' ,
        'longitude' => 'required',
        'dob' => 'nullable|date',
        'user_id'=>'required',
        'chanel_id'=>'required',

    ];

    public function prepareLinks(): array
    {
        return [
            'self' => route('customers.show', $this->id),
        ];
    }

    public function prepareAttributes(): array
    {
        $fields = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'distributor_id'=>$this->distributor_id,
            'distributorDetails' => $this->distributorDetails,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'dob' => $this->dob,
            'image'=>$this->image,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'postal_code' => $this->postal_code,
            'user_id' => $this->user_id,
            'chanel_id'=>$this->chanel_id,
            'credit_limit' => $this->credit_limit,
            'created_at' => $this->created_at,
            'unique_code'=>$this->unique_code,
            'channelDetails'=>$this->channelDetails,
            'area_id'=>$this->area_id,
            'sub_area_id'=>$this->sub_area_id,
            'sub_area_id'=>$this->sub_area_id,
            'areaDetails'=>$this->areaDetails,
            'subAreaDetails'=>$this->subAreaDetails,
            'regionDetails'=>$this->regionDetails,
            'distributor'=>$this->distributor,
            'warehouse'=>$this->warehouse,
            'countryDetails'=>$this->countryDetails,
            'status'=>$this->status,
            'assignCustomerList'=>$this->assignCustomerList
        ];

        return $fields;
    }

    public function prepareCustomers(): array
    {
        $fields = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'image'=>$this->image,
            'status'=>$this->status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'postal_code' => $this->postal_code,
            'user_id' => $this->user_id,
            'chanel_id'=>$this->chanel_id,
            'added_by' => $this->added_by,
            'credit_limit' => number_format((float)$this->credit_limit,2),
            'unique_code'=>$this->unique_code,
            'distributorDetails' => $this->distributorDetails,
            'channelDetails'=>$this->channelDetails,
            'area_id'=>$this->area_id,
            'sub_area_id'=>$this->sub_area_id,
            'sub_area_id'=>$this->sub_area_id,
            'areaDetails'=>$this->areaDetails,
            'subAreaDetails'=>$this->subAreaDetails,
            'countryDetails'=>$this->countryDetails,
            'userDetails'=>$this->userDetails,
            'distributor'=>$this->distributor,
            'warehouse'=>$this->warehouse,
            'regionDetails'=>$this->regionDetails,
            'assignCustomerList'=>$this->assignCustomerList
        ];

        return $fields;
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'customer_id', 'id');
    }
    
    public function gifts(): HasMany
    {
        return $this->hasMany(GiftSubmit::class, 'outlet_id', 'id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'customer_id', 'id');
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class, 'customer_id', 'id');
    }
    public function channelDetails()
    {
        return $this->hasOne(Chanel::class, 'id', 'chanel_id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'user_id')->where('role_id', 3);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

     public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id');
    }

    public function areaDetails()
    {
        return $this->hasOne(Area::class, 'id', 'area_id');
    }

    public function subAreaDetails(){
        return $this->hasOne(SubArea::class,'id','sub_area_id');
    }

    public function countryDetails(){
        return $this->hasOne(Country::class, 'id','country');
    }

    public function userDetails()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function regionDetails(){
        return $this->hasOne(Region::class, 'id','region');
    }

    public function distributorDetails()
   {
    return $this->belongsTo(User::class, 'distributor_id')->where('role_id', 3);
   }


    public function assignCustomerList()
    {
        return $this->hasMany(AssignCustomersList::class, 'customer_id', 'id');
    }



}
