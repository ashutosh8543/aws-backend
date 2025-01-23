<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class LoadProductsHitory extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable=['load_id', 'warehouse_id','quantity','product_id','products','assign_for_date'];

    public $casts = [
        'products' => 'array',
    ];


}
