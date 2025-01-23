<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTemplate extends Model
{
    use  SoftDeletes,HasFactory;

    protected $fillable = [
        'title',
        'type',
        'cn_title',
        'bn_title',
        'content',
        'cn_content',
        'bn_content',

    ];
}
