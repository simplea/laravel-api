<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditConfirm extends Model
{
    //
    protected $table   = 'com_auditconfirm';
    protected $guarded = [];

    const CREATED_AT   = 'writetime';
    const UPDATED_AT   = 'updatetime';

    const STATE_INIT   = 0; // 提交状态
    const STATE_PASS   = 1; // 通过
    const STATE_UNPASS = 2; // 不通过

    /**
     * 添加com申请
     *
     * @param integer $userId
     * @param integer $vid
     * @return void
     */
    protected function add($userId = 0, $vid = 0)
    {
        if(empty($userId) or empty($vid)){
            return false;
        }

        return self::updateOrCreate([
            'vid' => $vid
        ],[
            'uid' => $userId
        ]);
    }

    protected function info($vid = 0)
    {
        return self::where('vid', $vid)->first();
    }
}