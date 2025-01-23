<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Salesman;
use App\Models\Gift;

class AssignGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'gift_id',
        'quantity',
        'salesman_id',
        'assign_for_date',
        'gifts',
        'warehouse_id'
    ];

    public $casts = [
        'gifts' => 'array',
    ];


    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function gift()
    {
       return $this->belongsTo(Gift::class);
    }


    function giftdetails(){

        return $this->hasOne(Gift::class,'id','gift_id');
    }


}
