<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VulCate extends Model
{
    //
    protected $table   = "com_cate";
    protected $guarded = [];

    const STATE_TRUE   = 1;

    const PID_PARENT   = 0;

    protected function info($pid = self::PID_PARENT)
    {
        return self::where('pid',$pid)->get();
    }

    protected function infoById($id = self::PID_PARENT)
    {
        return self::where('id',$id)->first();
    }

    protected function list()
    {
        return self::where('state', self::STATE_TRUE)->get();
    }
}
