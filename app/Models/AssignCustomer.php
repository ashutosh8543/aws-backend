<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Area;
use App\Models\Salesman;
use App\Models\Customer;
use App\Models\User;

class AssignCustomer extends Model
{
    use HasFactory;

    protected $table = "assign_customers";

    protected $fillable = [
        'area_id',
        'salesman_id',
        'customer_ids',
        'assign_by',
        'assigned_date',
        'distributor_id',
        'warehouse_id',
        'sub_area_ids'
    ];

    public $casts = [
        'sub_area_ids' => 'array',
        'customer_ids' => 'array'
    ];



    public function area(){
        return $this->belongsTo(Area::class);
    }

    public function salesman()
    {
    return $this->belongsTo(User::class, 'salesman_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'assign_by');
    }


    public function assign_customers(){
        return $this->hasMany(AssignCustomersList::class,'assign_customer_id','id');
    }


    // public function customers()
    // {
    //     return $this->belongsTo(Customer::class, 'customer_id', 'id');
    // }


}
