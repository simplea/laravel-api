<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailToken extends Model
{
    //
    protected $table   = 'com_email_token';
    protected $guarded = [];
    public $timestamps = false;

    protected function add($cid, $email, $token)
    {
        return self::create([
            'cid' => $cid,
            'email' => $email,
            'token' => $token,
            'writetime' => date('Y-m-d H:i:s',time())
        ]);
    }

    protected function infoByToken($token = '')
    {
        return self::where('token',$token)->first();
    }
}
