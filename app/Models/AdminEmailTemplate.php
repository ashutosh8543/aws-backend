<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminEmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'content',
        'template_type'
    ];
}
