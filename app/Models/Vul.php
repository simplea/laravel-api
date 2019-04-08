<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyTrade;
use App\Models\Company;
use App\Models\VulCate;
use App\Models\VulLog;

class Vul extends Model
{ //
    protected $table     = "com_vul";
    protected $guarded   = [];

    const CREATED_AT            = 'write_time';
    const UPDATED_AT            = null;

    const STATE_UNAUDITED       = 0;  // 未审核
    const STATE_SUCCESS         = 1;  // 审核同过
    const STATE_FAILED          = 2;  // 审核同过

    const COMPANY_STATE_INIT    = 0;  // 未处理
    const COMPANY_STATE_PASS    = 1;  // 已确认
    const COMPANY_STATE_UNPASS  = 2;  // 不认领
    const COMPANY_STATE_com     = 3;  // com平台认领
    const COMPANY_STATE_UNcom   = 4;  // com平台不认领
    const COMPANY_STATE_COM_INIT = 5;  // 厂商默认认领
    const COMPANY_STATE_COM_UNINIT = 6;  // 厂商默认认领，金额不足

    const FIX_FALSE             = 0;  // 未修复
    const FIX_TRUE              = 1;  // 厂商修复
    const FIX_TOKEN             = 2;  // token修复

    const FLAG_NORMAL           = 0;  // 普通漏洞
    const FLAG_PERFECT          = 1;  // 精华漏洞

    const OPEN_FALSE            = 0;  // 未公开
    const OPEN_TRUE             = 1;  // 公开

    const MAIL_FALSE            = 0;  // 未发送
    const MAIL_TRUE             = 1;  // 已发送

    const comLEVEL_ZERO         = 0;
    const comLEVEL_ONE          = 1;
    const comLEVEL_TWO          = 2;
    const comLEVEL_THREE        = 3;

    const COMPANY_ID_FALSE      = 0;

    const REEDIT_FALSE          = 0;
    const REEDIT_TRUE           = 1;

    const LEVEL_ZONE            = 0;  // 低危
    const LEVEL_ONE             = 1;  // 中危
    const LEVEL_TWO             = 2;  // 高危
    const LEVEL_THREE           = 3;  // 严重

    const COMPANY_HIDE_TRUE     = 1;  // 显示
    const COMPANY_HIDE_FALSE    = 0;  // 隐藏

    const LEVEL_DECODE          = [
        self::LEVEL_ZONE  => '低危',
        self::LEVEL_ONE   => '中危',
        self::LEVEL_TWO   => '高危',
        self::LEVEL_THREE => '严重'
    ];

    protected function add($request){
        $id = $request->id ? $request->id : 0;
        $userId = $request->get('userId');
        $addItem = array(
            'uid'             => $userId,
            'title'           => $request->title,
            'target_type'     => $request->target_type,
            'attack_type'     => $request->attack_type,
            'level'           => $request->level,
            'description'     => clean($request->description),
            'company_type'    => $request->company_type,
            'company_contact' => $request->company_contact,
        );
        if($request->company_id == 0){
            $addItem['company'] = $request->company;
        }else{
            $addItem['company_id'] = $request->company_id;
        }

        return self::updateOrCreate(array('id' => $id), $addItem);
    }

    protected function list($where = [], $num = 20, $orWhere = [], $orWhereMore = [])
    {
        return self::select(
            'id',
            'uid',
            'title',
            'company',
            'level',
            'com_level',
            'price',
            'points',
            'com',
            'write_time',
            'update_time',
            'state',
            'company_state',
            'company_type',
            'transaction_number',
            'company_time',
            'title_zh',
            'title_en',
            'reedit',
            'reedit_time',
            'is_fix',
            'fix_time',
            'is_open',
            'company_id'
        )->where($where)->orWhere($orWhere)->orWhere($orWhereMore)->orderBy('id','desc')->paginate($num);
    }

    /**
     * 查询漏洞日志
     *
     * @param array $where
     * @return void
     */
    protected function vulLogList($where = [], $num = 20)
    {
        return self::from('com_vul as a')
        ->select(
            'a.id',
            'a.uid',
            'a.title',
            'a.company',
            'a.level',
            'a.com_level',
            'a.price',
            'a.points',
            'a.com',
            'a.write_time',
            'a.update_time',
            'a.state',
            'a.company_state',
            'a.company_type',
            'a.price_state',
            'a.transaction_number',
            'a.company_time',
            'a.title_zh',
            'a.title_en',
            'a.reedit',
            'a.reedit_time',
            'a.is_fix',
            'a.fix_time',
            'a.is_open',
            'a.company_id'
        )
        ->leftJoin('com_vul_log as b', 'a.id', '=', 'b.vid')
        ->where($where)
        ->whereNotNull('b.vid')
        ->groupBy('a.id')
        ->orderBy('a.id','desc')
        ->paginate($num);
    }

    protected function info($where = [])
    {
        return self::where($where)->first();
    }

    protected function companyVulInfo($where = [])
    {
        return self::select(
            'id',
            'uid',
            'title',
            'com_title',
            'target_type',
            'attack_type',
            'company',
            'level',
            'com_level',
            'description',
            'detail',
            'price',
            'points',
            'com',
            'write_time',
            'update_time',
            'state',
            'company_state',
            'company_type',
            'company_time',
            'title_zh',
            'title_en',
            'is_mail',
            'reedit',
            'reedit_time',
            'flag',
            'is_fix',
            'fix_time',
            'audittext',
            'is_open',
            'open_time',
            'company_id',
            'company_hide',
            'transaction_number'
        )->where($where)->first();
    }

    protected function tokenInfo($where = [])
    {
        return self::select(
            'id',
            'uid',
            'title',
            'com_title',
            'target_type',
            'attack_type',
            'company',
            'level',
            'com_level',
            'price',
            'write_time',
            'update_time',
            'state',
            'company_state',
            'company_type',
            'company_time',
            'description',
            'title_zh',
            'title_en',
            'reedit',
            'reedit_time',
            'flag',
            'is_fix',
            'fix_time',
            'is_open',
            'open_time',
            'company_id',
            'transaction_number'
        )->where($where)->first();
    }

    protected function tradeInfo($where = [])
    {
        return self::select(
            'id',
            'uid',
            'title',
            'com_title',
            'target_type',
            'attack_type',
            'company',
            'level',
            'com_level',
            'price',
            'write_time',
            'update_time',
            'state',
            'company_state',
            'company_type',
            'company_time',
            'title_zh',
            'title_en',
            'reedit',
            'reedit_time',
            'flag',
            'is_fix',
            'fix_time',
            'is_open',
            'open_time',
            'company_id',
            'transaction_number'
        )->where($where)->first();
    }

    protected function publicInfo($where = [])
    {
        return self::select(
            'id',
            'uid',
            'title',
            'com_title',
            'target_type',
            'attack_type',
            'company',
            'level',
            'com_level',
            'description_open',
            'price',
            'write_time',
            'update_time',
            'state',
            'company_state',
            'company_type',
            'company_time',
            'title_zh',
            'title_en',
            'reedit',
            'reedit_time',
            'flag',
            'is_fix',
            'fix_time',
            'is_open',
            'open_time',
            'company_id'
        )->where($where)->first();
    }

    /**
     * 提现方法
     *
     * @return void
     */
    protected function passList($num = 20, $where = [],$whereNotIn = [],$whereIn = [self::COMPANY_STATE_PASS,self::COMPANY_STATE_com])
    {
        return self::select(
            'id',
            'uid',
            'title',
            'com_title',
            'target_type',
            'attack_type',
            'company',
            'level',
            'com_level',
            'price',
            'com',
            'write_time',
            'update_time',
            'state',
            'company_state',
            'company_type',
            'company_time',
            'title_zh',
            'title_en',
            'reedit',
            'reedit_time',
            'flag',
            'is_fix',
            'fix_time',
            'is_open',
            'open_time',
            'company_id',
            'transaction_number'
        )->where($where)->whereIn('company_state',$whereIn)->whereNotIn('id',$whereNotIn)->orderBy('id','desc')->paginate($num);
    }
    
    /**
     * 提现详情列表
     *
     * @param [type] $aid
     * @return void
     */
    protected function getDrawInfoList($aid)
    {
        if(!$aid){ return '参数错误';}
        return self::from("com_withdraw_vul as a")
        ->select('b.*')
        ->leftJoin('com_vul as b','a.vid','=','b.id')
        ->where('a.aid',$aid)
        ->paginate(15);
    }

    /**
     * 获取用户ether总价格
     *
     * @param [type] $where
     * @return void
     */
    protected function getTotalVulPriceByUid($where)
    {
        return DB::table('com_vul')
        ->select(DB::RAW('cast(sum(price) as DECIMAL (19, 2)) as price'))
        ->where($where)
        ->whereIn('company_state',[self::COMPANY_STATE_PASS,self::COMPANY_STATE_com])
        ->first();
    }

    /**
     * 获取用户com总价格
     *
     * @param [type] $where
     * @return void
     */
    protected function getTotalVulcomByUid($where)
    {
        return DB::table('com_vul')
        ->select(DB::RAW('cast(sum(com) as DECIMAL (19, 2)) as price'))
        ->where($where)
        ->whereIn('company_state',[self::COMPANY_STATE_PASS,self::COMPANY_STATE_com])
        ->first();
    }

    protected function sureFix($id = 0, $update = [])
    {
        if(!$id){return false;} 
        return self::where('id',$id)->update($update);
    }

    /**
     * 商户漏洞认领不认领
     *
     * @param [type] $companyId
     * @param [type] $vulId
     * @param [type] $type
     * @param string $companyReason
     * @return void
     */
    protected function updateCompanyVul($companyId, $vulId, $type, $companyReason = '')
    {
        $vulInfo = self::where(['id' => $vulId,'company_id' => $companyId])->first();
        if($vulInfo){
            
            if($type == 1){
                if($vulInfo->state == 0){
                    $item = [
                        'company_state' => self::COMPANY_STATE_PASS,
                        'state' => self::STATE_SUCCESS,
                        'update_time' => date('Y-m-d H:i:s'),
                        'company_time'  => date('Y-m-d H:i:s')
                    ];
                }else{
                    $item = [
                        'company_state' => self::COMPANY_STATE_PASS,
                        'company_time'  => date('Y-m-d H:i:s')
                    ]; 
                }
                self::where('id',$vulId)->update($item);

                CompanyTrade::create([
                    'com_id'     => $companyId,
                    'trade_id'   => $vulId,
                    'price'      => $vulInfo->price,
                    'type'       => CompanyTrade::TYPE_TWO,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                CompanyTrade::create([
                    'com_id'     => $companyId,
                    'trade_id'   => $vulId,
                    'price'      => $vulInfo->price*0.1,
                    'type'       => CompanyTrade::TYPE_THREE,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $totalPrice = $vulInfo->price + $vulInfo->price*0.1;

                DB::table('com_company')->where('id',$companyId)->update(['balance' => DB::RAW("balance - $totalPrice")]);
                return true;
            }else{
                VulLog::add($vulId, clean($companyReason), VulLog::STATUS_REFUSE);
                if($vulInfo->state == 0){
                    $item = [
                        'state' => self::STATE_SUCCESS,
                        'update_time' => date('Y-m-d H:i:s'),
                        'company_state'   => self::COMPANY_STATE_com,
                        'company_time'    => date('Y-m-d H:i:s'),
                        'company_reason'  => $companyReason
                    ];
                }else{
                    $item = [
                        'company_state'   => self::COMPANY_STATE_com,
                        'company_time'    => date('Y-m-d H:i:s'),
                        'company_reason'  => $companyReason
                    ]; 
                }
                self::where('id',$vulId)
                ->update($item);
                return true;
            }
        }else{
            return false;
        }
    }
    
    /**
     * 商户漏洞默认认领
     *
     * @param [type] $companyId
     * @param [type] $vulId
     * @param [type] $type
     * @param string $companyReason
     * @return void
     */
    protected function companyVulClaim($companyId, $vulId)
    {
        $vulInfo = self::where(['id' => $vulId,'company_id' => $companyId])->first();
        if($vulInfo){
                $item = [
                    'company_state' => self::COMPANY_STATE_COM_INIT,
                    'company_time'  => date('Y-m-d H:i:s')
                ];
                self::where('id',$vulId)->update($item);

                CompanyTrade::create([
                    'com_id'     => $companyId,
                    'trade_id'   => $vulId,
                    'price'      => $vulInfo->price,
                    'type'       => CompanyTrade::TYPE_TWO,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                CompanyTrade::create([
                    'com_id'     => $companyId,
                    'trade_id'   => $vulId,
                    'price'      => $vulInfo->price*0.1,
                    'type'       => CompanyTrade::TYPE_THREE,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $totalPrice = $vulInfo->price + $vulInfo->price*0.1;

                DB::table('com_company')->where('id',$companyId)->update(['balance' => DB::RAW("balance - $totalPrice")]);
                return true;
        }else{
            return false;
        }
    }

    /**
     * 总发放前数
     *
     * @return void
     */
    protected function totalPrice()
    {
        return DB::table('com_vul')
        ->select(DB::RAW('cast(sum(price) as DECIMAL (19, 2)) as price'))->first()->price;
    }

    /**
     * 漏洞动态
     *
     * @param string $type
     * @param string $like
     * @param integer $id
     * @return void
     */
    protected function vulTrendList($type = 4, $like = '', $id = '')
    {
        $whereIn = [self::COMPANY_STATE_INIT, self::COMPANY_STATE_PASS, self::COMPANY_STATE_UNPASS, self::COMPANY_STATE_com, self::COMPANY_STATE_UNcom];
        $orderBy = 'update_time';
        $where[] = ['state', '=', self::STATE_SUCCESS];
        switch ($type) {
            case 1:
                $whereIn = [self::COMPANY_STATE_PASS, self::COMPANY_STATE_com];
                break;
            
            case 0:
                $where[] = ['company_state', '=', self::COMPANY_STATE_INIT];
                break;

            case 2:
                $where[] = ['flag', '=', self::FLAG_PERFECT];
                break;
            
            case 3:
                $where[] = ['is_open', '=', self::OPEN_TRUE];
                $orderBy = 'open_time';
                break;

            case 5:
                $where[] = ['is_fix', '=', self::FIX_TRUE];
                break;
            default:
                $whereIn = [self::COMPANY_STATE_INIT, self::COMPANY_STATE_PASS, self::COMPANY_STATE_com];
                break;
        }

        if(!empty($id)){
            $where[] = ['id', '=', $id];
        }

        $obj = self::select(
            'com_title',
            'title',
            'title_zh',
            'company',
            'price',
            'points',
            'com',
            'update_time',
            'write_time',
            'com_level',
            'level',
            'uid',
            'title_en',
            'flag',
            'is_open',
            'id',
            'state',
            'company_id',
            'company_state',
            'is_fix'
        )
        ->where($where)
        ->whereIn('company_state', $whereIn);
        if(!empty($like)){
            $companyIds = Company::where('company_name', 'like', '%'.$like.'%')->get()->pluck('id');
            if($companyIds->isNotEmpty()){
                $obj->whereRaw('(title like ? or title_zh like ? or title_en like ? or company like ? or company_id in (?))',['%'.$like.'%','%'.$like.'%', '%'.$like.'%', '%'.$like.'%',implode(',', $companyIds->toArray())]);
            }else{
                $obj->whereRaw('(title like ? or title_zh like ? or title_en like ? or company like ?)',['%'.$like.'%','%'.$like.'%', '%'.$like.'%', '%'.$like.'%']);
            }
        }
        return $obj->whereIn('company_state',$whereIn)
        ->orderBy($orderBy,'desc')
        // ->toSql();
        ->paginate(20);
    }

    /**
     * 商户漏洞个数
     *
     * @param [type] $companyId
     * @return void
     */
    protected function calimCount($companyId = 0)
    {
        return self::where([
            'state'         => self::STATE_SUCCESS,
            'company_state' => self::COMPANY_STATE_PASS,
            'company_id'    => $companyId
            ])
            ->count();
    }

    /**
     * 基金详情
     *
     * @param array $vids
     * @return void
     */
    protected function commonList($vids = [])
    {
        return self::from('com_vul as a')
        ->select(
            'a.com_title',
            'a.title',
            'a.title_zh',
            'a.company',
            'a.price',
            'a.update_time',
            'a.com_level',
            'a.level',
            'a.uid',
            'a.title_en',
            'a.company_id',
            'a.write_time',
            'a.id',
            'b.hid',
            'a.state',
            'a.com'
        )
        ->leftJoin('com_user as b', 'b.id', '=', 'a.uid')
        ->whereIn('a.id', $vids)
        ->orderBy('a.update_time', 'desc')
        ->get();
    }

    /**
     * web攻击个数
     *
     * @return void
     */
    protected function webTotal()
    {
        return self::where('state', self::STATE_SUCCESS)
        ->whereIn('attack_type',[22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33])
        ->count();
    }

    /**
     * 商户漏洞总数
     *
     * @param string $month
     * @param string $company
     * @param string $is_fix
     * @param string $level
     * @return void
     */
    protected function companyVulTotal($month = '00', $company = '', $is_fix = 'all', $level = 'all')
    {
        $where[] = ['state', '=', self::STATE_SUCCESS];
        $whereIn = [self::FIX_FALSE, self::FIX_TRUE, self::FIX_TOKEN];
        if("$level" != 'all'){
            $where[] = ['com_level', '=', $level];
        }
        $companyInfo = Company::infoByName($company);
        if(!empty($companyInfo->id)){
            $where[] = ['company_id', '=', $companyInfo->id];
        }else{
            $where[] = ['company', '=', $company];
        }
        if($is_fix == '0'){
            $whereIn = [$is_fix];
        }
        if($is_fix == '1'){
            $whereIn = [self::FIX_TRUE, self::FIX_TOKEN];
        }
        if($month != '00'){
            $where[] = [DB::RAW('MONTH(update_time)'), '=', $month];
        }

        return self::where($where)
        ->whereIn('is_fix',$whereIn)
        ->orderBy('update_time','desc')
        ->count();
    }

    /**
     * 商户漏洞总数
     *
     * @param string $month
     * @param string $company
     * @param string $is_fix
     * @param string $level
     * @return void
     */
    protected function companyVulTotalYear($year = '2019', $month = '00', $company = '', $is_fix = 'all', $level = 'all')
    {
        $where[] = ['state', '=', self::STATE_SUCCESS];
        $whereIn = [self::FIX_FALSE, self::FIX_TRUE, self::FIX_TOKEN];
        if("$level" != 'all'){
            $where[] = ['com_level', '=', $level];
        }
        $companyInfo = Company::infoByName($company);
        if(!empty($companyInfo->id)){
            $where[] = ['company_id', '=', $companyInfo->id];
        }else{
            $where[] = ['company', '=', $company];
        }
        if($is_fix == '0'){
            $whereIn = [$is_fix];
        }
        if($is_fix == '1'){
            $whereIn = [self::FIX_TRUE, self::FIX_TOKEN];
        }
        if($month != '00'){
            $where[] = [DB::RAW('MONTH(update_time)'), '=', $month];
        }
        $where[] = [DB::RAW('year(update_time)'), '=', $year];

        return self::where($where)
        ->whereIn('is_fix',$whereIn)
        ->orderBy('update_time','desc')
        ->count();
    }

    /**
     * 商户漏洞总数列表
     *
     * @param string $month
     * @param string $company
     * @param string $is_fix
     * @param string $level
     * @return void
     */
    protected function companyVulTotalList($year = '00', $month = '00', $company = '', $is_fix = 'all', $level = 'all')
    {
        $where[] = ['state', '=', self::STATE_SUCCESS];
        $whereIn = [self::FIX_FALSE, self::FIX_TRUE, self::FIX_TOKEN];
        if("$level" != 'all'){
            $where[] = ['com_level', '=', $level];
        }
        $companyInfo = Company::infoByName($company);
        if(!empty($companyInfo->id)){
            $where[] = ['company_id', '=', $companyInfo->id];
        }else{
            $where[] = ['company', '=', $company];
        }
        if($is_fix == '0'){
            $whereIn = [$is_fix];
        }
        if($is_fix == '1'){
            $whereIn = [self::FIX_TRUE, self::FIX_TOKEN];
        }
        if($month != '00'){
            $where[] = [DB::RAW('MONTH(update_time)'), '=', $month];
        }
        if($year != '00'){
            $where[] = [DB::RAW('year(update_time)'), '=', $year];
        }

        return self::select(
            'id',
            'com_title',
            'title',
            'title_zh',
            'company',
            'company_state',
            'price',
            'com',
            'points',
            'write_time',
            'update_time',
            'com_level',
            'level',
            'uid',
            'title_en',
            'company_id',
            'state'
        )
        ->where($where)
        ->whereIn('is_fix',$whereIn)
        ->orderBy('update_time','desc')
        ->get();
    }

    /**
     * 商户周总数
     *
     * @param [type] $type
     * @return void
     */
    protected function weekTotal()
    {
        $data = [];
        $data['week'] = [];
        $data['num']  = [];

        for ($i=3; $i > -1 ; $i--) { 
            $where = [];
            if($i == 0){
                $data['week'][] = date('Y-m-d');
                $where[] = ['is_fix', '=', self::FIX_FALSE];
                $where[] = ['state', '=', self::STATE_SUCCESS];
            }else{
                $data['week'][] = date('Y-m-d', strtotime("-$i week"));
                $where[] = ['write_time', '<=', DB::RAW("DATE_SUB(CURDATE(), INTERVAL $i WEEK)")];
                $where[] = ['is_fix', '=', self::FIX_FALSE];
                $where[] = ['state', '=', self::STATE_SUCCESS];
            }
            $res = self::where($where)->groupBy('company')->get();
            $data['num'][] = count($res);
        }
        return $data;
    }

    /**
     * 商户总数
     *
     * @return void
     */
    protected function companyTotal()
    {
        $res = self::where([
            'is_fix' => self::FIX_FALSE, 
            'state' => self::STATE_SUCCESS
            ])
            ->groupBy('company')
            ->get();
        return count($res);
    }

    /**
     * 月份统计漏洞厂商类型
     *
     * @param string $month
     * @return void
     */
    protected function companyTypeMonth($month = '00', $year = '2018')
    {
        $where[] = ['state', '=', self::STATE_SUCCESS];
        if($month != '00'){
            $where[] = [DB::RAW('month(write_time)'), '=', $month];
        }
        $where[] = [DB::RAW('year(write_time)'), '=', $year];
        return self::select(
            DB::RAW('count(*) as num'),
            'company_type'
        )
        ->where($where)
        ->whereIn('attack_type',[
            22,23,24,25,26,27,28,29,30,31,32,33
        ])
        ->whereIn('company_type',[
            0,1,2,3,4,5
        ])
        ->groupBy('company_type')
        ->get();
    }


   /**
     * 月份统计漏洞类型
     *
     * @param string $month
     * @return void
     */
    protected function targetTypeMonth($month = '00', $year = '2018')
    {
        $cateIds   = VulCate::info()->pluck('id')->toArray();
        $where[]   = ['a.state', '=', self::STATE_SUCCESS];
        if($month != '00'){
            $where[] = [DB::RAW('month(a.write_time)'), '=', $month];
        }
        $where[] = [DB::RAW('year(write_time)'), '=', $year];
        return self::from('com_vul as a')
        ->select(
            DB::RAW('count(a.id) as num'),
            'a.target_type',
            'b.name',
            'b.name_en'
        )
        ->leftJoin('com_cate as b', 'a.target_type', '=', 'b.id')
        ->where($where)
        ->whereIn('a.target_type',$cateIds)
        ->groupBy('a.target_type')
        ->get();
    }

    /**
     * 风险厂商列表
     *
     * @param string $month
     * @return void
     */
    protected function companyRiskList($month = '00', $year = '2018')
    {
        $where[] = ['a.state', '=', self::STATE_SUCCESS];
        $where[] = ['a.is_fix', '=', self::FIX_FALSE];
        $where[] = ['a.company_id', '=', self::COMPANY_ID_FALSE];
        $whereNotIn = ['某通用型交易所提供商','某通用交易所供应商'];
        if($month != '00'){
            $where[] = [DB::RAW('month(write_time)'), '=', $month];
        }
        $where[] = [DB::RAW('year(write_time)'), '=', $year];

        return self::from('com_vul as a')
        ->select(
            'c.company',
            'c.mark',
            'c.fxh_rank',
            'c.market',
            DB::RAW("SUM(case b.tag_type WHEN '0' THEN 2 WHEN '1' THEN 3 WHEN '2' THEN 5 END) as nums")
        )
        ->leftJoin('com_vul_tag as b', 'a.id', '=', 'b.vid')
        ->leftJoin('com_company_tmp as c', 'a.company', '=', 'c.company')
        ->where($where)
        ->whereNotIn('a.company',$whereNotIn)
        ->whereNotNull('c.company')
        ->orderBy('nums', 'desc')
        ->groupBy('a.company')
        ->paginate(20);
    }

    /**
     * 获取厂商风险标签
     *
     * @param string $company
     * @return void
     */
    protected function companyVulTag($company = '')
    {
        return self::from('com_vul as a')
        ->select('b.tag_type')
        ->leftJoin('com_vul_tag as b', 'a.id', '=', 'b.vid')
        ->where([
            'a.state' => self::STATE_SUCCESS,
            'a.is_fix' => self::FIX_FALSE,
            'a.company' => $company
        ])
        ->groupBy('b.tag_type')
        ->orderBy('b.tag_type', 'desc')
        ->get()
        ->pluck('tag_type');
    }

    /**
     * 漏洞总数
     *
     * @param [type] $comLevel
     * @return void
     */
    protected function vulAllTotal($comLevel)
    {
        return self::where([
            'state'     => self::STATE_SUCCESS,
            'com_level' => $comLevel
        ])
        ->count();
    }

    /**
     * 漏洞修复列表
     *
     * @return void
     */
    protected function vulFixList()
    {
        return self::select(
            'id',
            'company',
            'company_id',
            'fix_time',
            'title_zh'
        )
        ->where([
            ['state', '=', self::STATE_SUCCESS],
            ['is_fix', '!=', self::FIX_FALSE]
        ])
        ->orderBy('fix_time', 'desc')
        ->paginate(20);
    }

    /**
     * 用户漏洞数
     *
     * @param integer $uid
     * @param string $month
     * @return void
     */
    protected function userVulTotal($uid = 0, $month = '00', $year = '00')
    {
        $where[] = ['uid', '=', $uid];
        $where[] = ['state', '=', self::STATE_SUCCESS];
        if($year != '00'){
            if($month != '00'){
                $where[] = [DB::RAW('month(update_time)'), '=', $month];
            }
            $where[] = [DB::RAW('year(update_time)'), '=', $year];
        }
        return self::where($where)->count();
    }

    protected function selectInfo($where)
    {
        return self::select(
            'id',
            'title',
            'price',
            'com',
            'points',
            'company_time',
            'company',
            'company_id',
            'write_time',
            'state',
            'company_state',
            'level',
            'com_level'
        )->where($where)->first();
    }

    /**
     * 厂商漏洞数统计
     *
     * @return void
     */
    protected function vulCount($userId)
    {
        $count[0] = self::where([
            'company_id' => $userId,
            'state' => self::STATE_SUCCESS,
            'company_state' => self::COMPANY_STATE_INIT
        ])->orWhere([
            ['company_id', '=', $userId],
            ['state', '=', self::STATE_UNAUDITED],
            ['company_hide', '=', self::COMPANY_HIDE_TRUE]
        ])->orWhere([
            ['company_state' ,'=', self::COMPANY_STATE_COM_UNINIT],
            ['company_id','=',$userId],
            ['state','=', self::STATE_SUCCESS]
        ])->count();

        $count[2] = self::where([
            'company_id' => $userId,
            'state' => Vul::STATE_SUCCESS,
            'company_state' => Vul::COMPANY_STATE_com
        ])->count();

        $count[5] = self::where([
            ['company_id', '=', $userId],
            ['company_state' ,'!=', Vul::COMPANY_STATE_INIT],
            ['company_state' ,'!=', Vul::COMPANY_STATE_UNcom],
            ['is_fix', '=', Vul::FIX_FALSE],
            ['state', '=', Vul::STATE_SUCCESS]
        ])->count();

        $count[6] = self::where([
            ['company_id', '=', $userId],
            ['company_state', '!=', Vul::COMPANY_STATE_INIT],
            ['is_fix', '=', Vul::FIX_TRUE],
            ['state', '=', Vul::STATE_SUCCESS]
        ])->count();

        return $count;
    }

    /**
     * 用户漏洞数统计
     *
     * @param [type] $userId
     * @return void
     */
    protected function userVulCount($userId)
    {
        $count[0] = self::where([
            'uid' => $userId,
            'state' => self::STATE_UNAUDITED,
            'reedit' => self::REEDIT_FALSE
        ])->count();

        $count[2] = self::where([
            'uid' => $userId,
            'state' => self::STATE_FAILED
        ])->count();

        return $count;
    }
}
