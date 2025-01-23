<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CheckIn extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable=['unique_code','salesman_id','customer_id','image','location','uploaded_date'];

    // protected $casts=[
    //     'uploaded_date'=>'timestamp',
    // ];
    
    public function customer()
    {
        return $this->hasOne(Customer::class, 'id','customer_id');
    }
    public function salesman()
    {
        return $this->hasOne(User::class, 'id','salesman_id');
    }

    public function getUploadedDateAttribute($value)
    {
        return date('d-m-Y H:i:s');
    }

}
