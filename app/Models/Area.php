<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SubArea;

class Area extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable =['region_id','name', 'status'];

    public function region(){
      return $this->hasOne(Region::class,'id','region_id');
    }

    public function subAreas(){
        return $this->hasMany(SubArea::class, 'area_id', 'id');
    }




}
