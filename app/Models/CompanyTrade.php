<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompanyTrade extends Model
{
    //
    protected $table   = 'com_company_trade';
    protected $guarded = [];
    public $timestamps = false;

    const TYPE_ZERO  = 0; // 充值
    const TYPE_ONE   = 1; // 提现
    const TYPE_TWO   = 2; // 漏洞奖励
    const TYPE_THREE = 3; // 矿工费用

    protected function list($where = [], $num = 20)
    {
        return self::where($where)
        ->orderBY('id','desc')
        ->paginate($num);
    }

    protected function info($id = 0)
    {
        return self::where('id',$id)->first();
    }

    /**
     * 获取金额
     *
     * @param [type] $userId
     * @param [type] $type
     * @return void
     */
    protected function getAllTradePrice($userId = 0, $type = 0)
    {
        return self::select(
            DB::RAW('sum(price) as total')
        )
        ->where([
        'com_id' => $userId,
        'type'   => $type
        ])
        ->first();
    }
}
