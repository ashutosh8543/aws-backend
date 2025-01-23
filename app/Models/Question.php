<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable=['question','status', 'type', 'distributor_id', 'warehouse_id'];

    public function options(){
        return $this->hasMany(QuestionOption::class,'question_id','id');
    }

    public function warehouse()
    {
    return $this->belongsTo(Warehouse::class);
    }

    // public function distributor()
    // {
    //    return $this->belongsTo(User::class);
    // }

}
