<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Vul;

class VulTag extends Model
{
    //
    protected $table   = 'com_vul_tag';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * 风险率
     *
     * @param [type] $type
     * @return void
     */
    protected function fourWeekTotal($type)
    {
        $data = [];
        $data['week'] = [];
        $data['num']  = [];
        $webTotal = Vul::webTotal();

        for ($i=3; $i > -1 ; $i--) { 
            $where = [];
            if($i == 0){
                $data['week'][] = date('Y-m-d');
                $where[] = ['tag_type', '=', $type];
            }else{
                $data['week'][] = date('Y-m-d', strtotime("-$i week"));
                $where[] = ['writetime', '<=', DB::RAW("DATE_SUB(CURDATE(), INTERVAL $i WEEK)")];
                $where[] = ['tag_type', '=', $type];
            }
            $count = self::where($where)->count();
            $data['num'][] = round(($count/$webTotal)*100,2);
        }
        return $data;
    }

    protected function vulTagTotal($where = [])
    {
        return self::where($where)->count();
    }
}