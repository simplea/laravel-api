<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillUid extends Model
{
    //
    protected $table   = "com_skill_uid";
    protected $guarded = [];
    public $timestamps = false;

    protected function info($uid = '')
    {
        return self::where('uid',$uid)->first();
    }

    protected function del($uid)
    {
        return self::where('uid',$uid)->delete();
    }

    protected function list($where = [])
    {
        return self::where($where)->orderBy('id','desc')->get();
    }

    protected function skillList($userId)
    {
        return self::from('com_skill_uid as a')
        ->select([
            'b.name',
            'b.name_en',
            'b.id'
        ])
        ->leftJoin('com_skill as b','a.sid','=','b.id')
        ->where('a.uid',$userId)
        ->get();
    }
}
