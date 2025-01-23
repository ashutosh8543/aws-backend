<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyHistory extends Model
{
    use HasFactory;
    protected $fillable=['survey_id','question','option','question_image'];

}
