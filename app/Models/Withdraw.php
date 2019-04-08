<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Withdraw extends Model
{
    //
    protected $table = 'com_withdraw';
    protected $guarded = [];
    const CREATED_AT   = 'writetime';
    const UPDATED_AT   = 'updatetime';

    const STATE_TRUE  = 1;      // 以打款
    const STATE_FALSE = 0;      // 未处理

    const TYPE_FALSE = 0;       // ether提现
    const TYPE_TRUE  = 1;       // com提现

    protected function getWithdrawVulByUid($uid, $type = 0)
    {
        return self::from('com_withdraw as a')
               ->select('b.vid')
               ->leftJoin('com_withdraw_vul as b','a.aid','=','b.aid')
               ->where([
                   'a.type' => $type,
                   'a.uid'  => $uid
                ])
               ->get();
    }

    protected function getWithdrawVulByVid($uid, $vid, $type = 0)
    {
        return self::from('com_withdraw as a')
               ->select('a.aid','a.type')
               ->leftJoin('com_withdraw_vul as b','a.aid','=','b.aid')
               ->where([
                   'a.uid'  => $uid,
                   'a.type' => $type,
                   'b.vid'  => $vid
                ])
               ->get();
    }

    /**
     * 获取用户总漏洞钱
     *
     * @param [type] $where
     * @return void
     */
    protected function getTotalMoneyWithDrawByUid($where)
    {
        return DB::table('com_withdraw')
        ->select(DB::RAW('cast(sum(price) as DECIMAL (19, 2)) as price'))
        ->where($where)
        ->first();
    }

    protected function info($aid)
    {
        return self::where('aid',$aid)->first();
    }
}
