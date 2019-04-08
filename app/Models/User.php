<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use App\Models\Vul;

class User extends Authenticatable implements JWTSubject
{
    //
    protected $table   = 'com_user';
    protected $guarded = [];
    protected $hidden  = 'password';
    const CREATED_AT   = 'register_time';
    const UPDATED_AT   = 'update_time';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected function info($id = 0)
    {
        if(!$id){ return '无法获取id'; }
        $select = ["id","hid","register_time","state","update_time","team","receipt","avatar_url"];
        return self::select($select)->where('id',$id)->first();
    }

    protected function saveLogo($userId = 0, $url = '')
    {
        return self::where('id',$userId)->update(['avatar_url'=>$url]);
    }

    /**
     * 用户漏洞详情
     *
     * @param string $userName
     * @param string $month
     * @return void
     */
    protected function whiteVulDetail($userName = '', $month = '00', $year = '00')
    {
        $where[] = ['a.hid', '=', $userName];
        $where[] = ['b.state', '=', Vul::STATE_SUCCESS];
        if($year != '00'){
            $where[] = [DB::RAW('year(b.update_time)'), '=', $year];
            if($month != '00'){
                $where[] = [DB::RAW('month(b.update_time)'), '=', $month];
            }
        }
        
        return self::from('com_user as a')
        ->select(
            'b.id',
            'b.com_title',
            'b.title',
            'b.title_zh',
            'b.company',
            'b.company_state',
            'b.price',
            'b.com',
            'b.points',
            'b.write_time',
            'b.update_time',
            'b.com_level',
            'b.level',
            'b.uid',
            'b.title_en',
            'b.company_id',
            'b.state'
        )
        ->leftJoin('com_vul as b', 'b.uid', '=', 'a.id')
        ->where($where)
        ->orderBy('b.update_time', 'desc')
        ->get();
    }
}
