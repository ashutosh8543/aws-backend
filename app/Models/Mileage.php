<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Salesman;

class Mileage extends Model
{
    use HasFactory;

    protected $table="mileage_record";

    public function sales_man(){

        return $this->hasOne(User::class, 'id', 'sales_man_id');

     }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

}
