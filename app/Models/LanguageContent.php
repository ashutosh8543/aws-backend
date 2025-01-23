<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageContent extends Model
{
    use HasFactory;
    protected $table    = 'language_contents';
    protected $fillable = ['string','en', 'cn', 'bn', 'haiti', 'pt', 'active'];
    public $timestamps  = false;

    
}

