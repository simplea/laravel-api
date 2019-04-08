<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\VulToken;

class Company extends Authenticatable implements JWTSubject
{
    //
    protected $table   = 'com_company';
    protected $guarded = [];
    protected $hidden  = 'password';
    const CREATED_AT   = 'writetime';
    const UPDATED_AT   = 'update_time';
    const STATE_ZERO   = 0;       // 待审核
    const STATE_ONE    = 1;       // 审核通过
    const STATE_TWO    = 2;       // 审核未通过

    const PUBLISH_ZERO = 0;     // 未发布
    const PUBLISH_ONE  = 1;     // 已发布

    const EMAIL_UNREAL = 0;     // 未验证
    const EMAIL_REAL   = 1;     // 已验证
    const EMAIL_TOKEN  = 2;     // token激活

    const NODE_FALSE   = 0;     // 不展示
    const NODE_TRUE    = 1;     // 展示

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

    /**
     * 添加商户
     *
     * @param [type] $request
     * @return void
     */
    protected function add($request)
    {
        $emailReal = self::EMAIL_UNREAL;
        if(!empty($request->token) && preg_match ("/^[0-9A-Za-z]{30,50}$/", $request->token)){
            $vulToken = VulToken::where('token',$request->token);
            if(!empty($vulToken)){
                $emailReal = self::EMAIL_TOKEN;
            }
        }
        return self::create([
            'company_name' => $request->input('company_name'),
            'pwd'          => encodePassword($request->input('pwd')),
            'email'        => $request->input('email'),
            'domain'       => $request->input('domain'),
            'email_real'   => $emailReal
        ]);
    }

    /**
     * 修改邮箱
     *
     * @param [type] $id
     * @param [type] $email
     * @param [type] $emailReal
     * @return void
     */
    protected function updateEmail($id, $email, $emailReal)
    {
        return self::where('id',$id)
        ->update([
            'email'      => $email,
            'email_real' => $emailReal
        ]);
    }

    /**
     * 商户列表
     *
     * @return void
     */
    protected function list()
    {
        return self::from('com_company as a')
        ->select([
            'a.id',
            'a.company_name',
            'a.type',
            'b.high_end',
            'b.logo',
            'b.gra_end'
        ])
        ->where([
            'a.state'   => self::STATE_ONE,
            'a.publish' => self::PUBLISH_ONE,
        ])
        ->leftJoin('com_company_info as b','a.id','=','b.company_id')
        ->groupBy('a.company_name')
        ->orderBy('a.update_time','asc')
        ->get();
    }

    /**
     * 商户个人信息
     *
     * @param [type] $userId
     * @return void
     */
    protected function info($userId)
    {
        return self::from('com_company as a')
        ->select('a.*','b.logo','b.introduction','b.high_end','b.gra_end','b.contact')
        ->leftJoin('com_company_info as b','a.id','=','b.company_id')
        ->where('a.id',$userId)->first();
    }


    /**
     * 修改发布
     *
     * @param [type] $userId
     * @return void
     */
    protected function updatePublish($userId)
    {
        return self::where('id',$userId)
        ->update([
            'publish'      => self::PUBLISH_ONE,
            'publish_time' => date('Y-m-d H:i:s',time())
        ]);
    }

    /**
     * 修改密码
     *
     * @param integer $id
     * @param string $newPassword
     * @return void
     */
    protected function updatePassword($id = 0, $newPassword = '')
    {
        return self::where('id',$id)->update([
            'pwd' => encodePassword($newPassword)
        ]);
    }

    /**
     * 修改信息
     *
     * @param [type] $id
     * @param [type] $request
     * @return void
     */
    protected function updateInfo($id,$request)
    {
        return self::where('id',$id)
        ->update([
            'eth_addr' => $request->input('eth_addr',''),
            'domain'   => $request->input('domain','') 
        ]);
    }

    protected function auditList($num = 10, $sort = 1, $like = '', $type = 0)
    {
        $where[] = ['a.state', '=', self::STATE_ONE];
        $where[] = ['a.publish', '=', self::PUBLISH_ONE];
        $where[] = ['b.draft', '=', CompanyInfo::DRAFT_TRUE];

        if($type != 0){
            $where[] = ['a.type', '=', $type];
        }
        if($sort == 1){
            $order = 'a.publish_time';
        }else{
            $order = 'b.gra_end';
        }

        if(!empty($like)){
            $where[] = ['a.company_name', 'like', '%'.$like.'%'];
        }

        return self::from('com_company as a')
        ->select(
            'a.id',
            'a.company_name',
            'a.balance',
            'a.publish_time',
            'a.type',
            'b.logo',
            'b.gra_end',
            'b.introduction',
            'b.draft'
        )
        ->leftJoin('com_company_info as b','a.id','=','b.company_id')
        ->where($where)
        ->orderBy($order, 'desc')
        ->paginate($num);
    }

    /**
     * 生态节点
     *
     * @return void
     */
    protected function nodeList()
    {
        return self::select(
            'id',
            'company_name',
            'url',
            'logo'
        )
        ->where('is_node',self::NODE_TRUE)
        ->get();
    }

    protected function infoByName($companyName = '')
    {
        return self::where('company_name',$companyName)->first();
    }

    protected function commonInfoByName($companyName = '')
    {
        return self::select(
            'id',
            'balance',
            'company_name',
            'domain',
            'email_real',
            'logo',
            'publish',
            'publish_time',
            'state',
            'update_time',
            'writetime',
            'url'
        )->where('company_name',$companyName)->first();
    }

    protected function infoById($userId = 0)
    {
        return self::where('id',$userId)->first();
    }
}
