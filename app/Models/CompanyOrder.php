<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOrder extends Model
{
    //
    protected $table   = "com_company_order";
    protected $guarded = [];
    const CREATED_AT   = 'writetime';
    const UPDATED_AT   = 'updatetime';

    const STATE_INIT    = 0;    // 支付初始状态
    const STATE_SUCCESS = 1;    // 支付成功
    const STATE_FALSE   = 2;    // 支付失败

    protected function list($where = [],$orWhere = [])
    {
        return self::where($where)->orWhere($orWhere)->get();
    }

    protected function add($userId = 0, $txhash = '', $price = 100)
    {
        return self::create([
            'com_id'  => $userId,
            'price'   => $price,
            'txhash'  => $txhash
        ]);
    }

    protected function info($id = 0)
    {
        return self::where('id',$id)->first();
    }
}
