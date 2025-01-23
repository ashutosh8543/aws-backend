<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftItems extends Model
{
    use HasFactory;
    protected $casts = [
        'gift_details' => 'array',
    ];

    
}
