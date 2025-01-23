<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnloadProductFromSaleman extends Model
{
    use SoftDeletes,HasFactory;

    protected $fillable=['salesman_id','quantity','total_quantity','product_id','products','unique_code'];

    public $casts = [
        'products' => 'array',
    ];

    public function salesman()
    {
        return $this->hasOne(User::class,'id','salesman_id');
    }

    public function product()
    {
        return $this->hasOne(MainProduct::class,'id','product_id');
    }



}
