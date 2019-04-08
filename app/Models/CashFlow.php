<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CashFlow extends Model
{
    //
    protected $table   = 'com_cash_flow';
    protected $guarded = [];
    public $timestamps = false;

    const TYPE_ZERO  = 0;       // 漏洞奖励ether
    const TYPE_ONE   = 1;       // 漏洞奖励com
    const TYPE_TOW   = 2;       // 提现ether
    const TYPE_THREE = 3;       // 提现com
    const TYPE_FOUR  = 4;       // 积分points

    const PAY_STATE_TRUE  = 1;       // 支出
    const PAY_STATE_FALSE = 0;       // 收入

    protected function list($where = [],$whereIn = [], $vulIds = [])
    {
        $obj = self::where($where);
        if($whereIn){
            $obj = $obj->whereIn('type',$whereIn); 
        }
        if($vulIds){
            $obj = $obj->whereNotIn('field_id',$vulIds); 
        }
        return $obj->orderBy('writetime','desc')->paginate(20);
    }

    protected function frame($where = [], $vulIds = [])
    {
        $obj = DB::table('com_cash_flow');
        if($vulIds){
            $obj = $obj->whereNotIn('field_id',$vulIds); 
        }
        return $obj->select(DB::RAW('cast(sum(price) as DECIMAL (19, 2)) as price'))
        ->where($where)
        ->first();
    }

    /**
     * 用户排行
     *
     * @param string $month
     * @return void
     */
    protected function whiteRank($month = '00', $year = '00')
    {
        $where[] = ['a.type', '=', self::TYPE_FOUR];
        if($year != '00'){
            if($month != '00'){
                $where[] = [DB::RAW('month(a.writetime)'), '=', $month];
            }
            $where[] = [DB::RAW('year(a.writetime)'), '=', $year];
        }
        return self::from('com_cash_flow as a')
        ->select(
            DB::RAW('sum(a.price) as fame'),
            'a.uid',
            'b.hid',
            'b.avatar_url',
            'b.team'
        )
        ->leftJoin('com_user as b', 'a.uid', '=', 'b.id')
        ->where($where)
        ->groupBy('a.uid')
        ->orderBy('fame', 'desc')
        ->paginate(20);
    }

    /**
     * 获取明细详情
     *
     * @return void
     */
    protected function getCash($uid, $type, $fidleId)
    {
        return self::where([
            'uid'      => $uid,
            'type'     => $type,
            'field_id' => $fidleId
        ])
        ->first();
    }
}
