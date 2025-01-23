<?php

namespace App\Models;

use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GiftInventory;
use App\Models\ProductInventory;

/**
 * App\Models\Warehouse
 *
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $country
 * @property string $city
 * @property string|null $email
 * @property string|null $zip_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse query()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereZipCode($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Purchase> $purchases
 * @property-read int|null $purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 *
 * @mixin \Eloquent
 */
class Warehouse extends BaseModel
{
    use SoftDeletes, HasFactory, HasJsonResourcefulData;

    protected $table = 'warehouses';

    const JSON_API_TYPE = 'warehouses';

    protected $fillable = [
        'ware_id',
        'name',
        'phone',
        'country',
        'city',
        'email',
        'zip_code',
        'user_id',
        'area',
        'status'
    ];

    public static $rules = [
        'name' => 'required|unique:warehouses',
        'phone' => 'required|numeric',
        'country' => 'required',
        'city' => 'required',
        'email' => 'nullable|email|unique:warehouses',
        'zip_code' => 'nullable|numeric',
    ];

    public function prepareLinks(): array
    {
        return [
            'self' => route('warehouses.show', $this->id),
        ];
    }

    public function prepareAttributes(): array
    {
        $fields = [
            'user_id'=>$this->user_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'unique_code'=>$this->unique_code,
            'country' => $this->country,
            'city' => $this->city,
            'email' => $this->email,
            'zip_code' => $this->zip_code,
            'created_at' => $this->created_at,
            'ware_id'=>$this->ware_id,
            'warehouseDetails'=>$this->warehouseDetails,
            'manageStock'=>$this->ManageStock,
            'area'=>$this->area,
            'areaDetails'=>$this->areaDetails,
            'distributor'=>$this->distributor,
            'countryDetails'=>$this->countryDetails,
             'regionDetails'=>$this->areaDetails->region,
            'user'=>$this->user,
            'status'=>$this->status
        ];

        return $fields;
    }

    public function prepareWarehouses(): array
    {
        $fields = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        return $fields;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'warehouse_id', 'id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'warehouse_id', 'id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'warehouse_id', 'id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'warehouse_id', 'id');
    }

    public function warehouseDetails(){

        return $this->hasOne(User::class,'id','ware_id');
    }


    public function ManageStock(){
        return $this->hasMany(ManageStock::class, 'warehouse_id', 'id');
    }
    public function areaDetails(){

        return $this->hasOne(Area::class,'id','area');
    }

    public function distributor() {
        return $this->belongsTo(Distributor::class);
    }

    public function user()
    {
       return $this->belongsTo(User::class);
    }

    public function countryDetails(){

        return $this->hasOne(Country::class,'id','country');
    }

    public function regionDetails(){

        return $this->hasOne(Region::class,'id','country');
    }

    public function giftInventories()
    {
        return $this->hasMany(GiftInventory::class);
    }

    public function inventories()
    {
        return $this->hasMany(ProductInventory::class);
    }

    public function suppervisors(){

        return $this->hasMany(Suppervisor::class,'ware_id','ware_id');
    }



}
