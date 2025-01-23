<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable=['salesman_id','order_id','survey_date','question','option','unique_code','warehouse_id','customer_id'];

    public function salesmanDetails(){
        return $this->hasOne(User::class ,'id','salesman_id');
    }
    public function customerDetails(){
        return $this->hasOne(Customer::class ,'id','customer_id');
    }

    public function surveyHistory(){
        return $this->hasMany(SurveyHistory::class ,'survey_id','id');
    }

    public function getSurveyDateAttribute($value)
    {
        return date('d-m-Y H:i:s',strtotime($value));
    }



}
