<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PriceInventory;

class Chanel extends Model
{
    use SoftDeletes,HasFactory;
    protected $fillable=['name','status'];


    public function priceInventories()
    {
        return $this->hasMany(PriceInventory::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


}
