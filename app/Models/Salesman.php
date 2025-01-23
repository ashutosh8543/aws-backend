<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;
use App\Models\Distributor;
use App\Models\GiftInventory;

class Salesman extends Model
{
    use SoftDeletes,HasFactory;
    protected $fillable=['salesman_id','distributor_id','ware_id', 'country', 'supervisor_id', 'status'];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'salesman_id');
    }
    public function area()
    {
        return $this->hasMany(Warehouse::class, 'ware_id','ware_id');
    }

    public function distributor() {
        return $this->belongsTo(Distributor::class, 'distributor_id', 'id');
    }

    public function user()
    {
       return $this->belongsTo(User::class);
    }

    public function salesManDetails()
    {
       return $this->hasOne(User::class, 'id','salesman_id');
    }

    public function warehouse()
    {
        return $this->hasMany(Warehouse::class, 'ware_id','ware_id');
    }


    public function salesmanDistributor()
    {
       return $this->hasOne(User::class,'id','distributor_id');
    }


    public function giftInventories()
    {
        return $this->hasMany(GiftInventory::class, 'warehouse_id', 'ware_id');
    }


}
