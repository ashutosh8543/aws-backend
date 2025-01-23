<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GiftInventory;
use App\Models\Salesman;

class GiftInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'warehouse_id',
        'user_id',
        'gift_id',
        'distributor_quantities',
        'warehouse_quantities',
        'country',

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }


    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }


    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'warehouse_id', 'ware_id');
    }


}
