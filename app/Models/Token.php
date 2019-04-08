<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    //
    protected $table   = 'com_token';
    protected $guarded = [];
    public $timestamps = false;
}