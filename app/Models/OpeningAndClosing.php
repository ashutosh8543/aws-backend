<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpeningAndClosing extends Model
{
    use HasFactory;

   protected $fillable = [
    'unique_id',
    'sales_man_id',
    'created_by',
    'type',
    'cash',
    'country'
   ];

    public function sales_man(){

        return $this->hasOne(User::class, 'id', 'sales_man_id');

     }



}
