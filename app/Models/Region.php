<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;



class Region extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'status','country'];
    protected $dates = ['deleted_at'];



    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function country()
    {
        return $this->hasOne(Country::class,'id','country');
    }

}
