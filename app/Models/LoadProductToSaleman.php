<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Product;
use App\Models\ProductInventory;

class LoadProductToSaleman extends Model
{

    use SoftDeletes,HasFactory;

    protected $fillable=['salesman_id', 'warehouse_id','quantity','total_quantity','product_id','products','assign_for_date','unique_code'];

    public $casts = [
        'products' => 'array',
    ];

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function LoadedProducts(){

        return $this->hasMany(LoadProductsHitory::class,'load_id','id');
    }

    public function productInventoriesDetails()
    {
        return $this->hasMany(ProductInventory::class, 'product_id', 'id');
    }

}
