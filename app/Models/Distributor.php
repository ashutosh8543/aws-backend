<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PriceInventory;

class Distributor extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'product_id',
        'quantity'

    ];

    public function product(){
        return $this->belongsTo(product::class);
    }

    public function priceInventories()
    {
        return $this->hasMany(PriceInventory::class);
    }


    public function warehouses() {
     return $this->hasMany(Warehouse::class);
    }

    public function salesmen()
    {
        return $this->hasMany(Salesman::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'distributor_id', 'id');
    }


}
