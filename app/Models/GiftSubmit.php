<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftSubmit extends Model
{
    use HasFactory;
    
  
    protected $casts = [
        'gitf_details' => 'array',
    ];

     public function outlets(){         
        return $this->hasOne(Customer::class, 'id', 'outlet_id');
     }
     public function salesman(){         
        return $this->hasOne(User::class,'id','sales_man_id');
     }

   public function salesman_details()
   {
      return $this->hasOne(User::class,'id','sales_man_id');
   }

   public function customer(){         
      return $this->hasOne(Customer::class, 'id', 'outlet_id');
   }
   public function giftItem(){         
      return $this->hasMany(GiftItems::class, 'submited_gift_id', 'id');
   }
   

}
