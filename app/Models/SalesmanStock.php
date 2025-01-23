<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesmanStock extends Model
{
    use SoftDeletes,HasFactory;
    protected $fillable=['salesman_id','product_id','customer_id','quantity'];

}
