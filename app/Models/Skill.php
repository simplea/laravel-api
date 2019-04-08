<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    //
    protected $table   = 'com_skill';
    protected $guarded = [];
    public $timestamps = false;

    protected function list()
    {
        return self::get();
    }
}
