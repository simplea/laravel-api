<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Txlist;
use App\Models\Vul;
use App\Models\User;
use App\Models\CashFlow;
use App\Models\Company;
use App\Models\Withdraw;
use App\Models\Token;
use App\Models\TokenList;
use App\Models\WithdrawVul;
use App\Models\VulTag;
use App\Models\Eth;
use App\Models\CompanyTmp;
use App\Models\CompanyInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SkillUid;
use App\Models\News;
use App\Models\VulToken;
use App\Models\VulCate;
use App\Models\CompanyHistory;

class CommonController extends Controller
{
    /**
     * 总数接口
     *
     * @param Request $request
     * @return void
     */
    protected function total()
    {
        $txlistTotal   = Txlist::count();    // 交易量
        $vulPriceTotal = Vul::totalPrice();  // 累计金额
        $userTotal     = User::count();      // 注册用户
        $vulTotal      = Vul::where('state',1)->count();
        return $this->json(compact('txlistTotal','vulPriceTotal','userTotal','vulTotal'));
    }

    /**
     * 漏洞动态列表接口
     *
     * @param Request $request
     * @return void
     */
    protected function vulTrend(Request $request)
    {
        delHtmlTag($request);
        $type     = $request->input('type',4);
        $language = $request->input('language','zh_CN');
        $like     = $request->input('like','');
        $id       = '';

        if(!empty($like)){
            if (!preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]{2,}$/u",$like)) {
                $like = '';
            }
            $pos = stripos($like,'com-');

            if($pos !== false){
                $pocnum = $like;
                $id     = numberVulId($pocnum);
                $like   = '';
            }
        }

        if(!in_array($type,['0', '1', '2', '3', '5'])){
            $type = 4;
        }

        $trendList = Vul::vulTrendList($type, $like, $id);
        if($trendList->isNotEmpty()){
            foreach($trendList as $item){
                $item->redirect = 1;
                if(!empty($item->company_id)){
                    $company        = Company::info($item->company_id);
                    $item->company  = $company['company_name'];
                    $item->publish  = $company['publish'];
                    $item->comState = $company['state'];
                }
                $companyInfo = Company::infoByName($item->company);
                if($companyInfo){
                    $item->publish  = $companyInfo['publish'];
                    $item->comState = $companyInfo['state'];
                }
                if(empty($companyInfo->id)){
                    $item->redirect = 0;
                }

                if($companyInfo['state'] != 1){
                    $item->redirect = 0;
                }

                if($companyInfo['publish'] != 1){
                    $item->redirect = 0;
                }
                
                if($language == 'en_US'){
                    $item->title = getTitle($item, $language);
                }else{
                    $item->title = getTitle($item);
                }
                $userInfo   = User::info($item->uid);
                $frameWhere = ['type' => CashFlow::TYPE_FOUR, 'uid' => $item->uid];
                $cashFlow   = CashFlow::frame($frameWhere);
                
                unset($item->title_en);
                unset($item->title_zh);
                unset($item->com_title);
                $item->num  = vulNumber($item->id, $item->wirte_time);
                $item->cash = intval($cashFlow->price/100);
                $item->hid  = $userInfo->hid;
                $item->team = $userInfo->team;
            }
        }
        return $this->json(compact('trendList'));
    }

    /**
     * 公开漏洞详情
     *
     * @param Request $requst
     * @return void
     */
    protected function publicVulInfo(Request $request)
    {
        $this->validate($request,config('rules.vulInfo.rule'),config('rules.vulInfo.self'));
        $id       = $request->input('id');
        $language = $request->input('language','zh_CN');
        $where[]  = ['id','=',$id];
        $info     = Vul::publicInfo($where);
        if($info->is_open != 1){
            return $this->json([], 0, '无权访问');
        }
        $info->vulSign = vulNumber($info->id,$info->write_time);
        if($language == 'en_US'){
            $info->title = getTitle($info, $language);
            $info->target_name = VulCate::infoById($info->target_type)->name_en;
            $info->attack_name = VulCate::infoById($info->attack_type)->name_en;
        }else{
            $info->title = getTitle($info);
            $info->target_name = VulCate::infoById($info->target_type)->name;
            $info->attack_name = VulCate::infoById($info->attack_type)->name;
        }
        
        unset($info->title_en);
        unset($info->title_zh);
        unset($info->com_title);
        if($info->company_id){
            $companyInfo   = Company::info($info->company_id);
            $userInfo      = USER::info($info->uid);
            $info->company = $companyInfo['company_name'];
            $info->hid     = $userInfo->hid;
        }
        return $this->json(compact('info'));
    }

    /**
     * 漏洞赏金
     *
     * @return void
     */
    protected function companyAuditList(Request $request)
    {
        delHtmlTag($request);
        $num   = $request->input('num', 10);
        $sort  = $request->input('sort', 1);
        $type  = $request->input('type', 0);
        $like  = $request->input('like', '');

        $intNum  = (int)$num;

        if(!empty($like)){
            if (!preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]{2,}$/u",$like)) {
                $like = '';
            }
        }

        if($intNum <= 0){
            return $this->json([], 0, '参数错误');
        }

        if(!in_array($sort, [1, 2])){
            $sort = 1;
        }

        if(!in_array($type, [0, 1, 2, 3, 4, 5, 6, 7])){
            $type = 0;
        }
        
        $auditList = Company::auditList($intNum, $sort, $like, $type);
        return $this->json(compact('auditList'));
    }

    /**
     * 生态节点
     *
     * @return void
     */
    protected function companyNodeList()
    {
        $nodeList = Company::nodeList();
        return $this->json(compact('nodeList'));
    }

    /**
     * 基金动态
     *
     * @return void
     */
    protected function fund()
    {
        $txList = Txlist::orderBy('tx_time','desc')->paginate(20);
        foreach($txList as $val){
            $hashInfo = Withdraw::where('transaction_number', $val->hash);
            if(empty($hashInfo)){
                $val->jyh = 0;
            }else{
                $val->jyh = 1;
            }

            $val->diffTime = diffTime($val->tx_time);
            $val->price    = Ethprice($val->price);
            $val->gasPrice = Ethprice($val->gasPrice*$val->gasUsed);
        }

        $ethAddr = env('ETHADDR');
        $info = Eth::first();
        $total = Withdraw::getTotalMoneyWithDrawByUid(['state' => 1, 'type' => 0]);
        $info->balance = Ethprice($info->balance);
        return $this->json(compact('txList', 'ethAddr', 'info', 'total'));
    }

    /**
     * 基金详情
     *
     * @param Request $request
     * @return void
     */
    protected function fundInfo(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.fundInfo.rule'),config('rules.fundInfo.self'));
        $hash         = $request->input('hash','');
        $language     = $request->input('language','zh_CN');
        $withDrawInfo = Withdraw::where('transaction_number',$hash)->first();
        $vids         = WithdrawVul::where('aid', $withDrawInfo->aid)->get()->pluck('vid')->toArray();
        $vul          = Vul::commonList($vids);
        foreach($vul as $val){
            $val->vulSign = vulNumber($val->id,$val->write_time);
            if($language == 'en_US'){
                $val->title   = getTitle($val, $language);
            }else{
                $val->title   = getTitle($val);
            }
            if($val->company_id){
                $companyInfo   = Company::info($val->company_id);
                $val->company = $companyInfo['company_name'];
            }
            unset($val->title_en);
            unset($val->title_zh);
            unset($val->com_title);
        }
        return $this->json(compact('withDrawInfo','vul'));
    }

    /**
     * 新闻列表
     *
     * @param Request $request
     * @return void
     */
    protected function news(Request $request)
    {
        $num  = $request->input('num', 10);
        $type = $request->input('type', News::TYPE_NEW);

        if(!in_array($type, ['1', '2']) ){
			return $this->json([], 0, '参数错误');
		}

        $intNum  = (int)$num;

        if($intNum <= 0){
            return $this->json([], 0, '参数错误');
        } 
        $news = News::list($intNum, $type);
        return $this->json(compact('news'));
    }

    /**
     * token基金
     *
     * @return void
     */
    protected function fundToken()
    {
        $tokenList = TokenList::orderBy('tx_time','desc')->paginate(20);
        $total     = 0;
        $total2    = 0;
        if($tokenList->isNotEmpty()){
            foreach($tokenList as $val){
                $total        += Ethprice($val->price);
                $val->diffTime = diffTime($val->tx_time);
                $val->price    = Ethprice($val->price);
                $val->gasPrice = Ethprice($val->gasPrice*$val->gasUsed);
            }
        }

        $token = Token::orderBy('tx_time','desc')->paginate(20);
        if($token->isNotEmpty()){
            foreach($token as $val){
                $total2 += Ethprice($val->price);
            }
        }
        $balance = number_format(5000000000-$total2);
        $ethAddr = env('ETHADDR');
        return $this->json(compact('tokenList', 'balance', 'total', 'ethAddr'));
    }

    /**
     * 安全态势风险率
     *
     * @param Request $request
     * @return void
     */
    protected function vulTag(Request $request)
    {
        $this->validate($request,config('rules.vulTag.rule'),config('rules.vulTag.self'));
        $type = $request->input('type');
        $data = VulTag::fourWeekTotal($type);

        // 增幅
        if($data['num'][2] == 0){
            $data['amp'] = 0;
        }else{
            $dift = round($data['num'][3]/$data['num'][2],4);
            if($dift == 0){
                $data['amp'] = 0;
            }elseif($dift > 0){
                $data['amp'] = $dift -1;
            }else{
                $data['amp'] = 1 - $dift;
            }
        }
        // 占比
        $webTotal    = Vul::webTotal();
        $tagWhere[]  = ['tag_type', '=', $type];
        $tagTotal    = vulTag::vulTagTotal($tagWhere);
        $data['per'] = round(($tagTotal/$webTotal)*100,2);
        return $this->json(compact('data'));
    }

    /**
     * 厂商黑榜数据
     *
     * @param Request $requst
     * @return void
     */
    protected function companyMark(Request $requst)
    {
        $data = Vul::weekTotal();
        $list = array();
        // 增幅
        $dift = $data['num'][3] - $data['num'][2];
        $data['num'][3] = (int) $data['num'][3];
        $data['num'][2] = (int) $data['num'][2];
        if($dift == 0){
            $list['amp'] = 0;
        }elseif($dift < 0){
            $dift = abs($dift);
            $list['amp'] = -round($dift/$data['num'][2],4);
        }else{
            if($data['num'][2] == 0){
                $list['amp'] = round($dift,2);
            }else{
                $list['amp'] = round($dift/$data['num'][2],4);
            }
        }
        $list['list'] = $data;
        $list['num']  = Vul::companyTotal();
        return $this->json(compact('list'));
    }

    /**
     * 月份统计漏洞厂商类型
     *
     * @param Request $request
     * @return void
     */
    protected function companyTypeMonth(Request $request)
    {
        delHtmlTag($request);
        $month = $request->input('month');
        $year  = $request->input('year', '2018');
        if (!preg_match ( "/^([0-9]{2})$/", $month)) {
			return $this->json([], 0, '月份的格式错误!');
        }
		if(!in_array($month, array('00','01','02','03','04','05','06','07','08','09','10','11','12'), true)){
			return $this->json([], 0, '月份的格式错误!');
        }
		if(!in_array($year, array('2018', '2019'), true)){
            $year = '2018';
        }
        
        $companyTypeMonth = Vul::companyTypeMonth($month, $year);
        if($companyTypeMonth->isEmpty()){
            $companyTypeMonth[] = ['company_type' => 1,'num' => 0];
            $companyTypeMonth[] = ['company_type' => 2,'num' => 0];
            $companyTypeMonth[] = ['company_type' => 3,'num' => 0];
            $companyTypeMonth[] = ['company_type' => 4,'num' => 0];
            $companyTypeMonth[] = ['company_type' => 5,'num' => 0];
        }
        return $this->json(compact('companyTypeMonth'));
    }

    /**
     * 月份统计漏洞类型
     *
     * @param Request $request
     * @return void
     */
    protected function targetTypeMonth(Request $request)
    {
        delHtmlTag($request);
        $month    = $request->input('month');
        $year     = $request->input('year', '2018');
        $language = $request->input('language','zh_CN');
        if (!preg_match ( "/^([0-9]{2})$/", $month)) {
			return $this->json([], 0, '月份的格式错误!');
        }
		if(!in_array($month, array('00','01','02','03','04','05','06','07','08','09','10','11','12'), true)){
			return $this->json([], 0, '月份的格式错误!');
        }
        if(!in_array($year, array('2018', '2019'), true)){
            $year = '2018';
        }
        
        $targetTypeMonth = Vul::targetTypeMonth($month, $year);
        if($targetTypeMonth->isEmpty()){
            $vulCate = VulCate::list();
            foreach($vulCate as $key => $val){
                $targetCate[$key]['num'] = 0;
                $targetCate[$key]['target_type'] = $val->id;
                if($language == 'en_US'){
                    $targetCate[$key]['name'] = $val->name_en;
                }else{
                    $targetCate[$key]['name'] = $val->name;
                }
            }
            return $this->json(compact('targetCate'));
        }else{
            foreach ($targetTypeMonth as $key => $value) {
                if($language == 'en_US'){
                    $value->name = $value->name_en; 
                }else{
                    $value->name = $value->name; 
                }
            }
        }
        return $this->json(compact('targetTypeMonth'));
    }

    /**
     * 风险厂商列表
     *
     * @param Request $request
     * @return void
     */
    protected function companyRiskList(Request $request)
    {
        delHtmlTag($request);
        $month = $request->input('month');
        $year  = $request->input('year', '2018');
        if (!preg_match ( "/^([0-9]{2})$/", $month)) {
			return $this->json([], 0, '月份的格式错误!');
        }
		if(!in_array($month, array('00','01','02','03','04','05','06','07','08','09','10','11','12'), true)){
			return $this->json([], 0, '月份的格式错误!');
        }
		if(!in_array($year, array('2018', '2019'), true)){
            $year = '2018';
        }

        $riskList = Vul::companyRIskList($month, $year);

        foreach($riskList as $val){
            $serious = Vul::companyVulTotalYear($year, $month, $val->company, Vul::FIX_FALSE, 3);
            $high    = Vul::companyVulTotalYear($year, $month, $val->company, Vul::FIX_FALSE, 2);
            $mid     = Vul::companyVulTotalYear($year, $month, $val->company, Vul::FIX_FALSE, 1);
            $low     = Vul::companyVulTotalYear($year, $month, $val->company, Vul::FIX_FALSE, 0);
            $fixNums = Vul::companyVulTotalYear($year, $month, $val->company, Vul::FIX_TRUE);
            
            $val->redirect = 0;
            $val->total   = $serious + $high + $mid + $low;
            $val->fixNums = $fixNums;
            $val->score   = score(['serious' => $serious, 'high' => $high, 'mid' => $mid, 'low' => $low]);
            $val->risk    = Vul::companyVulTag($val->company);
            $val->r_count = count($val->risk);
            $val->r_score = 0;
            if($val->risk && in_array(0, $val->risk->toArray())){
                $val->r_score += 2;
            }
            if(in_array(1, $val->risk->toArray())){
                $val->r_score += 3;
            }
            if(in_array(2, $val->risk->toArray())){
                $val->r_score += 5;
            }

            $companyTmp = CompanyTmp::infoByName($val->company);
            if($companyTmp && $companyTmp->state == 1){
                $val->handing_type = 1;
            }else{
                $where = ['state' => Vul::STATE_SUCCESS, 'is_mail' => Vul::MAIL_TRUE, 'company' => $val->company];
                $vul   = Vul::info($where);
                if($vul){
                    $val->handing_type = 1;
                }else{
                    $val->handing_type = 0;
                }
            }

        }

        $risk = $riskList->sortByDesc('r_score')->values()->all();
        return $this->json(compact('risk'));
    }

    /**
     * 漏洞总数
     *
     * @param Request $request
     * @return void
     */
    protected function vulAllTotal(Request $request)
    {
        $level = $request->input('level', 0);
        if(!in_array($level, [Vul::comLEVEL_ZERO, Vul::comLEVEL_ONE, Vul::comLEVEL_TWO, Vul::comLEVEL_THREE])){
            return $this->json([], 0, '参数错误');
        }
        $count = Vul::vulAllTotal($level);
        return $this->json(compact('count'));
    }

    /**
     * 漏洞修复列表
     *
     * @param Request $request
     * @return void
     */
    protected function vulFixList(Request $request)
    {
        $language    = $request->input('language','zh_CN');
        $fixList = Vul::vulFixList();
        foreach($fixList as $val){
            if($language == 'zh_CN'){
                Carbon::setLocale('zh');
            }
            $val->diffTime = Carbon::parse($val->fix_time)->diffForHumans();
            $val->vulSign  = vulNumber($val->id,$val->write_time);
            if($val->company_id != 0){
                $val->companyName = Company::info($val->company_id)['company_name'];
                $companyInfo      = CompanyInfo::info($val->company_id);
                $val->logo        = $companyInfo->logo;
            }
        }
        return $this->json(compact('fixList'));
    }

    /**
     * 厂商不入驻信息
     *
     * @return void
     */
    protected function companyNon(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.companyNon.rule'),config('rules.companyNon.self'));
        $company = $request->input('company');
        $month   = $request->input('month','00');

        $ser  = Vul::companyVulTotal($month, $company, Vul::FIX_FALSE, 3);
        $high = Vul::companyVulTotal($month, $company, Vul::FIX_FALSE, 2);
        $mid  = Vul::companyVulTotal($month, $company, Vul::FIX_FALSE, 1);
        $low  = Vul::companyVulTotal($month, $company, Vul::FIX_FALSE, 0);
        return $this->json(compact('ser','high','mid','low'));
    }

    /**
     * 厂商入驻信息
     *
     * @return void
     */
    protected function companyInfo(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.companyInfo.rule'),config('rules.companyInfo.self'));
        $companyName = $request->input('company');

        $num     = $request->input('num', 10);
        $intNum  = (int)$num;

        if($intNum <= 0){
            return $this->json([], 0, '参数错误');
        }

        $company = Company::commonInfoByName($companyName);
        if($company->state != 1){
            return $this->json([], 0, '无权访问');
        }

        if($company->publish != 1){
            return $this->json([], 0, '无权访问');
        }

        $vulCount               = Vul::calimCount($company->id);
        $companyInfo            = CompanyInfo::where('company_id',$company->id)->first();
        $companyHistory['2019'] = CompanyHistory::list($company->id, '2019');
        $companyHistory['2018'] = CompanyHistory::list($company->id, '2018');
        $historyCount           = CompanyHistory::getAllCount($company->id);

        return $this->json(compact('company', 'companyInfo', 'vulCount', 'companyHistory', 'historyCount'));
    }

    /**
     * 用户排行
     *
     * @param Request $request
     * @return void
     */
    protected function whiteRank(Request $request)
    {
        $month = $request->input('month','00');
        $year  = $request->input('year','00');
        $page  = $request->input('page');
        $language  = $request->input('language', 'zh_CN');
        if($page >= 6){
            $rankList['data'] = [];
            return $this->json(compact("rankList"));
        }
        if(!in_array($month, array('00','01','02','03','04','05','06','07','08','09','10','11','12'), true)){
            $month = '00';
        }
        if(!in_array($year, array('2018','2019','00'), true)){
            $year = '00';
        }
        $url      = 'https://api.coinmarketcap.com/v2/ticker/1027/?convert=CNY';
        $response = urlGet($url);
        $result   = json_decode($response);
        $rate     = $result->data->quotes->CNY->price;

        $rankList = CashFlow::whiteRank($month, $year);

        foreach($rankList as $key => $val){
            if($val->fame < 100){
                unset($rankList[$key]);
                continue;
            }
            $comWhere   = [];
            $comWhere[] = ['uid', '=', $val->uid];
            $comWhere[] = ['type', '=', CashFlow::TYPE_ONE];
            if($year != '00'){
                $comWhere[] = [DB::RAW('year(writetime)'), '=', $year];
                if($month != '00'){
                    $comWhere[] = [DB::RAW('month(writetime)'), '=', $month];
                }
            }

            $ethWhere   = [];
            $ethWhere[] = ['uid', '=', $val->uid];
            $ethWhere[] = ['type', '=', CashFlow::TYPE_ZERO];
            if($year != '00'){
                $ethWhere[] = [DB::RAW('year(writetime)'), '=', $year];
                if($month != '00'){
                    $ethWhere[] = [DB::RAW('month(writetime)'), '=', $month];
                }
            }

            $val->fame   = intval($val->fame/100);
            $val->skill  = SkillUid::skillList($val->uid);
            $val->com    = (int)CashFlow::frame($comWhere)->price;
            $val->ether  = CashFlow::frame($ethWhere)->price;
            $val->cny    = intval($val->com*0.26 + $val->ether*$rate);
            $val->num    = (int)Vul::userVulTotal($val->uid, $month, $year);

            foreach ($val->skill as $k => $value) {
                if($language == 'en_US'){
                    $value->name = $value->name_en;
                }
            }
        }
        return $this->json(compact('rankList'));
    }

    /**
     * 用户漏洞详情
     *
     * @param Request $request
     * @return void
     */
    protected function whiteVulDetail(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.whiteVulDetail.rule'),config('rules.whiteVulDetail.self'));
        $userName = $request->input('userName');
        $month    = $request->input('month', '00');
        $year     = $request->input('year', '00');
        if(!in_array($month, array('00','01','02','03','04','05','06','07','08','09','10','11','12'), true)){
            $month = '00';
        }
        if(!in_array($year, array('2018','2019','00'), true)){
            $year = '00';
        }

        $whiteVulDetail = User::whiteVulDetail($userName, $month, $year);
        foreach($whiteVulDetail as $val){
            $val->vulSign = vulNumber($val->id,$val->write_time);
            $val->hid     = User::info($val->uid)->hid;
            if(!empty($val->company_id)){
                $companyInfo  = Company::infoById($val->company_id);
                $val->company = $companyInfo['company_name'];
            }
            $val->title   = getTitle($val);
            unset($val->title_en);
            unset($val->title_zh);
            unset($val->com_title);
        }
        return $this->json(compact('whiteVulDetail'));
    }

    /**
     * 商户漏洞总数列表
     *
     * @param Request $request
     * @return void
     */
    protected function companyVulTotalList(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.companyVulTotalList.rule'),config('rules.companyVulTotalList.self'));
        $companyName = $request->input('companyName');
        $month       = $request->input('month', '00');
        $year        = $request->input('year', '00');
        $isFix       = $request->input('is_fix', 'all');
        $level       = $request->input('level', 'all');
        $language    = $request->input('language','zh_CN');

        if(!in_array($year, array('00','2018','2019'))){
            $year = '00';
        }

        $companyVulTotalList = Vul::companyVulTotalList($year, $month, $companyName, 0, $level);
        foreach($companyVulTotalList as $val){
            $val->vulSign = vulNumber($val->id,$val->write_time);
            $val->hid     = User::info($val->uid)->hid;
            if($language == 'en_US'){
                $val->title   = getTitle($val, $language);
            }else{
                $val->title   = getTitle($val);
            }
            unset($val->title_en);
            unset($val->title_zh);
            unset($val->com_title);
        }
        return $this->json(compact('companyVulTotalList'));
    }


    protected function tokenVulDetail(Request $request)
    {
        delHtmlTag($request);
        $token    = $request->input('token', '');
        $language = $request->input('language','zh_CN');
        if(empty($token)){
            return $this->json([], 0, '参数错误');
        }

        if (!preg_match ("/^[0-9A-Za-z]{30,50}$/", $token)) {
            return $this->json([], 0, '参数错误');
        }
        
        $vulToken = VulToken::where('token', $token)->first();
        if(empty($vulToken)){
            return $this->json([], 0, '参数错误');
        }

        $vid = $vulToken->vid;
        if(empty($vid)){
            return $this->json([], 0, '参数错误');
        }

        $vulWhere[] = ['id','=',$vid];
        $vulInfo = Vul::tokenInfo($vulWhere);

        if(empty($vulInfo)){
            return $this->json([], 0, '参数错误');
        }
        $diffTime = time()-strtotime($vulToken->writetime);
        if($diffTime >= 7*24*3600 || $diffTime < 0){
			return $this->json([], 5001, '超时');
        }
        $vulInfo->vulSign = vulNumber($vulInfo->id,$vulInfo->write_time);
        if($vulInfo->company_id){
            $companyInfo    = Company::info($vulInfo->company_id);
            $vulInfo->company = $companyInfo->company_name;
        }
        if($language == 'en_US'){
            $vulInfo->title    = getTitle($vulInfo, $language);
            $vulInfo->target_name = VulCate::infoById($vulInfo->target_type)->name_en;
            $vulInfo->attack_name = VulCate::infoById($vulInfo->attack_type)->name_en;
        }else{
            $vulInfo->title = getTitle($vulInfo);
            $vulInfo->target_name = VulCate::infoById($vulInfo->target_type)->name;
            $vulInfo->attack_name = VulCate::infoById($vulInfo->attack_type)->name;
        }
        
        unset($vulInfo->title_en);
        unset($vulInfo->title_zh);
        unset($vulInfo->com_title);

        $count = mb_strlen(strip_tags($vulInfo->description));
        $num   = ceil($count/3);
        $vulInfo->description = cut_html_str($vulInfo->description, $num);
        return $this->json(compact('vulInfo'));
    }

    /**
     * 获取年月
     *
     * @param Request $request
     * @return void
     */
    protected function yearMonth(Request $request)
    {
        $year  = [];
        $monthOne = range(7, 12);
        $year[2018][0] = ['id' => '00', 'labelEn' => 'all', 'label' => '全部'];
        foreach($monthOne as $val){
            switch ($val) {
                case 7:
                    $year[2018][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'July', 'label' => '7月'];
                    break;

                case 8:
                    $year[2018][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'August', 'label' => '8月'];
                    break;
                
                
                case 9:
                    $year[2018][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'September', 'label' => '9月'];
                    break;

                case 10:
                    $year[2018][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'October', 'label' => '10月'];
                    break;

                case 11:
                    $year[2018][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'November', 'label' => '11月'];
                    break;

                case 12:
                    $year[2018][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'December', 'label' => '12月'];
                    break;
            }
        }

        $nowMonth = date('m');
        $nowDay   = date('d');
        $intMonth = (int)$nowMonth;
        if($nowDay >= 15){
            $intMonth = (int)$nowMonth;
        }else{
            $intMonth = (int)$nowMonth-1;
        }

        if($intMonth >= 1){
            $range = range(0, $intMonth);
            foreach($range as $val){
                switch ($val) {
                    case 0:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'all', 'label' => '全部'];
                        break;
                    case 1:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'January', 'label' => '1月'];
                        break;
                    case 2:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'February', 'label' => '2月'];
                        break;
                    case 3:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'March', 'label' => '3月'];
                        break;
                    case 4:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'April', 'label' => '4月'];
                        break;
                    case 5:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'May', 'label' => '5月'];
                        break;
                    case 6:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'June', 'label' => '6月'];
                        break;
                    case 7:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'July', 'label' => '7月'];
                        break;
                    case 8:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'August', 'label' => '8月'];
                        break;
                    case 9:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'September', 'label' => '9月'];
                        break;
                    case 10:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'October', 'label' => '10月'];
                        break;
                    case 11:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'November', 'label' => '11月'];
                        break;
                    case 12:
                        $year[2019][] = ['id' => str_pad($val, 2, '0', STR_PAD_LEFT), 'labelEn' => 'December', 'label' => '12月'];
                        break;
                }
            }
        }
        return $this->json(compact('year'));
    }

    /**
     * 悬赏管理统计详情
     *
     * @param Request $request
     * @return void
     */
    protected function rewardHistoryInfo(Request $request)
    {
        $this->validate($request,config('rules.rewardHistoryInfo.rule'),config('rules.rewardHistoryInfo.self'));
        $id      = $request->input('id');
        $info    = CompanyHistory::info($id);
        $preInfo = CompanyHistory::prevInfo($id, $info->company_id);
        $language = $request->input('language','zh_CN');

        $testRange  = '测试范围 \n ';
        $testRangeEn  = 'Test Range \n ';
        $vulRate    = ' \n 漏洞评级';
        $vulRateEn    = ' \n Vulnerability Rating';
        $lowVulRate = ' \n 低危漏洞 \n ';
        $lowVulRateEn = ' \n low risk \n ';
        $midVulRate = ' \n mid risk \n ';
        $midVulRateEn = ' \n 中危漏洞 \n ';
        $higVulRate = ' \n 高危漏洞 \n ';
        $higVulRateEn = ' \n high risk \n ';
        $graVulRate = ' \n 严重漏洞 \n ';
        $graVulRateEn = ' \n serious risk \n ';

        if($language == 'en_US'){
            $current = $testRangeEn.$info->test_range.$vulRateEn.$graVulRateEn.$info->gra_vul_rate.$higVulRateEn.$info->high_vul_rate.$midVulRateEn.$info->mid_vul_rate.$lowVulRateEn.$info->low_vul_rate;
        }else{
            $current = $testRange.$info->test_range.$vulRate.$graVulRate.$info->gra_vul_rate.$higVulRate.$info->high_vul_rate.$midVulRate.$info->mid_vul_rate.$lowVulRate.$info->low_vul_rate;
        }
        if($preInfo){
            if($language == 'en_US'){
                $prev = $testRangeEn.$preInfo->test_range.$vulRateEn.$graVulRateEn.$preInfo->gra_vul_rate.$higVulRateEn.$preInfo->high_vul_rate.$midVulRateEn.$preInfo->mid_vul_rate.$lowVulRateEn.$preInfo->low_vul_rate;
            }else{
                $prev = $testRange.$preInfo->test_range.$vulRate.$graVulRate.$preInfo->gra_vul_rate.$higVulRate.$preInfo->high_vul_rate.$midVulRate.$preInfo->mid_vul_rate.$lowVulRate.$preInfo->low_vul_rate;
            }
            $prevTime    = $preInfo->updatetime;
        }else{
            $prev = '';
            $prevTime    = '';
        }

        $currentTime = $info->updatetime;

        return $this->json(compact('current', 'prev', 'currentTime', 'prevTime'));
    }
}
