<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;
use App\Models\AssignCustomer;

class AssignCustomersList extends Model
{
    use HasFactory;

    protected $table = "assign_customers_list";

    protected $fillable = [
        'assign_customer_id',
        'customer_id',
        'assigned_date',
        'salesman_id',
        'status'
    ];


    public function assignCustomer()
    {
        return $this->belongsTo(AssignCustomer::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
      return $this->hasOne(User::class, 'id','salesman_id');
    }


}
