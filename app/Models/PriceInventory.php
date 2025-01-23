<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Chanel;
use App\Models\Distributor;

class PriceInventory extends Model
{
    use HasFactory;

    protected $fillable = ['chanel_id', 'user_id', 'product_id', 'price'];

    public function product(){
        return $this->belongsTo(product::class);
    }

    public function channel() // Correctly naming the method
    {
        return $this->belongsTo(Chanel::class, 'chanel_id'); // Specify the foreign key
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'user_id'); // Assuming User is the model for distributors
    }

}
