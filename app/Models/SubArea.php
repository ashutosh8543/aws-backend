<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_area_name',
        'area_id',
        'status'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }

    public function areaDetails()
{
    return $this->belongsTo(Area::class);
}



}
