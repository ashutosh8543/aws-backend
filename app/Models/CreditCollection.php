<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCollection extends Model
{
    use  SoftDeletes,HasFactory;
    
    const COMPLETED = 1;
    const PENDING = 2;
    // payment status
    const PAID = 1;
    const UNPAID = 2;
    const PARTIAL_PAID = 3;


    //  const PAYMENT_METHOD = [
    //     self::CASH => 'Cash',
    //     self::CHEQUE => 'Cheque',
    //     self::CREDIT_LIMIT => 'Credit limit',
    // ];
    // const status=[

    // ];   
     
     protected $fillable=['unique_code','customer_id','amount','order_id','salesman_id','order_type','type','payment_type','status','collected_date','collection_payment_type'];
     
    //  protected $casts=[
    //     'collected_date'=>'timestamp',
    // ];
     public function customer()
    {
        return $this->hasOne(Customer::class,'id','customer_id');
    }
    public function orderDetails()
    {
        return $this->hasOne(Sale::class, 'id','order_id');
    }
    public function salesman()
    {
        return $this->hasOne(User::class, 'id','salesman_id');
    }

    
}
