<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompanyHistory extends Model
{
    //
    protected $table   = 'com_company_history';
    protected $guarded = [];
    public $timestamps = false;

    protected function list($userId, $year = '2019')
    {
        return self::select(
            'id',
            'updatetime'
        )
        ->where([
            ['company_id' ,'=', $userId],
            [DB::RAW('year(updatetime)'), '=', $year]
        ])
        ->orderBy('updatetime', 'desc')
        ->groupBy('updatetime')
        ->get();
    }

    protected function info($id)
    {
        return self::select(
            'id',
            'test_range',
            'low_vul_rate',
            'mid_vul_rate',
            'high_vul_rate',
            'gra_vul_rate',
            'company_id',
            'updatetime'
        )
        ->where([
            ['id', '=', $id]
        ])
        ->first();
    }

    /**
     * 前一个悬赏
     *
     * @param [type] $id
     * @return void
     */
    protected function prevInfo($id, $companyId)
    {
        return self::select(
            'id',
            'test_range',
            'low_vul_rate',
            'mid_vul_rate',
            'high_vul_rate',
            'gra_vul_rate',
            'updatetime'
        )
        ->where([
            ['id', '<', $id],
            ['company_id', '=', $companyId],
        ])
        ->orderBy('id', 'desc')
        ->first();
    }

    protected function getAllCount($userId)
    {
        return self::where('company_id', $userId)->count();
    }
}