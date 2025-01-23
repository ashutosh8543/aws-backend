<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Warehouse;
use App\Models\Product;

class ProductInventory extends Model
{
    use HasFactory;

    protected $table = 'product_inventories';

    protected $fillable = [
        'distributor_id',
        'warehouse_id',
        'product_id',
        'distributor_quantities',
        'warehouse_quantities',
        'user_id',
        'country'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

   


}
