<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyOrder;
use App\Models\CompanyInfo;
use App\Models\CompanyTrade;
use App\Models\EmailToken;
use App\Models\EmailTokenRepass;
use Illuminate\Support\Facades\Storage;
use App\Events\CompanyRegister;
use App\Events\CompanyForgetPass;
use App\Models\Vul;
use App\Models\Msg;
use App\Models\User;
use App\Models\Withdraw;
use App\Models\WithdrawVul;
use JWTAuth;

class UserController extends Controller
{
    /**
     * 厂商注册
     *
     * @param Request $request
     * @return void
     */
    protected function register(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.companyRegister.rule'),config('rules.companyRegister.self'));
        $comRes = Company::add($request);
        if($comRes->id){
            $comInfoRes = CompanyInfo::addInfo($comRes->id, $request->contact);
            if($comInfoRes->id){
                $tk = '已经删除';

                $origin           = $request->server('HTTP_ORIGIN');
                $host             = $origin ?? $httpHost;
                $emailToken       = EmailToken::add($comRes->id,$comRes->email,$tk);
                $emailToken->host = $host;
                event(new CompanyRegister($emailToken));

                $token = JWTAuth::fromUser($comRes);
                if(empty($comRes->email) || $comRes->email_real == 0){
                    $emailReal = 0;
                }else{
                    $emailReal = $comRes->email_real;
                }
                $email    = $comRes->email ?? '';
                $emailUrl = emailUrl($email);
                return $this->json(compact('token','emailReal','email','emailUrl'));
            }
        }else{
            return $this->json([],0,'注册失败');
        }
    }
    /**
     * 厂商登录
     *
     * @param Request $request
     * @return void
     */
    protected function login(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.comanyLogin.rule'),config('rules.comanyLogin.self'));

        $company_name   = $request->input('company_name');

        $user  = Company::where('company_name',$company_name)->first();
        if(encodePassword($request->pwd) != $user->pwd){ return $this->json([],0,'密码错误'); }

        $token = JWTAuth::fromUser($user);

        if(empty($user->email) || $user->email_real == 0){
            $emailReal = 0;
        }else{
            $emailReal = $user->email_real;
        }
        $email    = $user->email ?? '';
        $emailUrl = emailUrl($email);
        Company::where('id',$user->id)->update([
            'lastlogin_ip'   => $request->getClientIp(),
            'lastlogin_time' => date('Y-m-d H:i:s',time())
        ]); 
        return $this->json(compact('token','emailReal','email','emailUrl'));
    }

    /**
     * 厂商忘记密码
     *
     * @param Request $request
     * @return void
     */
    protected function forgetPass(Request $request)
    {
        delHtmlTag($request);

        $this->validate($request,config('rules.comanyForgetPass.rule'),config('rules.comanyForgetPass.self'));

        $companyName = $request->input('companyName');
        $email       = $request->input('email');

        $companyInfo = Company::infoByName($companyName);

        if($companyInfo->state == Company::STATE_TWO){
            return $this->json([], 0, '权限不足');
        }

        if($companyInfo->email != $email){
            return $this->json([], 0, '信息错误');
        }

        $tk = '已经删除';

        $origin                  = $request->server('HTTP_ORIGIN');
        $host                    = $origin ?? $httpHost;
        $emailToken              = EmailTokenRepass::add($companyInfo->id,$companyInfo->email,$tk);
        $emailToken->host        = $host;
        $emailToken->companyName = $companyInfo->company_name;
        event(new CompanyForgetPass($emailToken));

        return $this->json();
    }

    /**
     * 忘记密码token信息
     *
     * @return void
     */
    protected function forgetPassTokenInfo(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.comanyForgetPassTokenInfo.rule'),config('rules.comanyForgetPassTokenInfo.self'));
        $token = $request->input('token', '');

        $tokenInfo = EmailTokenRepass::infoByToken($token);
        if(!$tokenInfo){
            return $this->json([], 0, '获取失败');
        }
        if($tokenInfo->state == 1){
            return $this->json([], 2, 'token失效');
        }
        $companyInfo = Company::info($tokenInfo->cid);
        if(!$companyInfo){
            return $this->json([], 0, '无法获取信息');
        }
        $companyName = $companyInfo->company_name;
        if($companyInfo->state == 2){
            return $this->json([], 0, '无权访问');
        }

        $diffTime = time()-strtotime($tokenInfo->writetime);
		if($diffTime >= 7*24*3600 || $diffTime < 0){
            return $this->json([], 3, 'token失效');
        }
        return $this->json(compact('companyName'));
    }

    /**
     * 修改密码
     *
     * @param Request $request
     * @return void
     */
    protected function forgetRepass(Request $request)
    {
        $this->validate($request,config('rules.forgetRepass.rule'),config('rules.forgetRepass.self'));
        $token      = $request->input('token', '');
        $password   = $request->input('password', '');
        $tokenInfo  = EmailTokenRepass::infoByToken($token);

        if(!$tokenInfo){
            return $this->json([], 0, '获取失败');
        }
        if($tokenInfo->state == 1){
            return $this->json([], 2, 'token失效');
        }

        $companyInfo = Company::info($tokenInfo->cid);
        if(!$companyInfo){
            return $this->json([], 0, '无法获取信息');
        }
        $companyName = $companyInfo->company_name;
        if($companyInfo->state == 2){
            return $this->json([], 0, '无权访问');
        }

        $diffTime = time()-strtotime($tokenInfo->writetime);
		if($diffTime >= 7*24*3600 || $diffTime < 0){
            return $this->json([], 3, 'token失效');
        }

        $res = Company::updatePassword($companyInfo->id, $password);
        if($res){
            EmailTokenRepass::where('id',$tokenInfo->id)->update(['state' => 1]);
            return $this->json();
        }else{
            return $this->json([], 0, '修改失败');
        }
    }

    protected function info(Request $request)
    {
        $userId = $request->get('userId');
        $user   = Company::info($userId);
        unset($user->pwd);
        if($user->publish == 0){
            $user->gra_end = 0;
        }
        if(empty($user->email) || $user->email_real == 0){
            $emailReal = 0;
        }else{
            $emailReal = $user->email_real;
        }
        $emailUrl = emailUrl($user->email);

        $where = ['obj_id' => $userId, 'type' => Msg::TYPE_COMPANY, 'status' => 0];
        $msgCount = Msg::where($where)->count();
        $msgList  = Msg::where($where)->get();
        $vulCount = Vul::calimCount($userId);
        $user->rewardTotal = CompanyTrade::getAllTradePrice($userId, CompanyTrade::TYPE_TWO)->total ?? 0;
        $user->logo = $user->logo ?? '';

        return $this->json(compact('user', 'emailReal', 'emailUrl', 'vulCount', 'msgCount', 'msgList')); 
    }

    /**
     * 消息列表
     *
     * @param Request $request
     * @return void
     */
    protected function msgList(Request $request)
    {
        $this->validate($request,config('rules.msgList.rule'),config('rules.msgList.self'));
        $status = $request->input('status');
        $type   = $request->input('type');
        $userId = $request->get('userId');
        $num    = (int)$request->input('num', 20);
        if($type != 1){
            return $this->json([], 0, '参数错误');
        }
        $where  = [
            'obj_id' => $userId,
            'type'   => $type,
            'status' => $status
        ];
        $msgList = Msg::msgList($where, $num);

        if($status == Msg::STATS_UNREAD && !$msgList->isEmpty()){
            Msg::unreadUpdate($type,$userId);
        }
        return $this->json(compact('msgList'));
    }

    /**
     * 厂商发送邮件
     *
     * @param Request $request
     * @return void
     */
    protected function sendEmail(Request $request)
    {
        $userId = $request->get('userId');
        $companyInfo = Company::info($userId);
        
        if($companyInfo->email and $companyInfo->email_real == 0){
            $origin = $request->server('HTTP_ORIGIN');
            $host   = $origin ?? $httpHost;
            $token  = '已经删除';
            $emailToken = EmailToken::add($companyInfo->id,$companyInfo->email,$token);
            $emailToken->host = $host;
            event(new CompanyRegister($emailToken));
            return $this->json();
        }else{
            return $this->json([],0,'未获取邮件地址');
        }
    }

    /**
     * 厂商验证邮件
     *
     * @param Request $request
     * @return void
     */
    protected function activeEmail(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.comanyActiveEmail.rule'),config('rules.comanyActiveEmail.self'));
        $token       = $request->input('token');
        $emailToken  = EmailToken::infoByToken($token);
        $companyInfo = Company::info($emailToken->cid);
        if($companyInfo->email_real != 0){
            return $this->json([], 0,'此邮箱已经激活');
        }

        $res = Company::updateEmail($companyInfo->id, $companyInfo->email, Company::EMAIL_REAL);
        if($res){
            return $this->json();
        }else{
            return $this->json([],0,'激活失败');
        }
    }

    /**
     * 保证金
     *
     * @param Request $request
     * @return void
     */
    protected function deposit(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.deposit.rule'),config('rules.deposit.self'));
        $order  = $request->input('order');
        $userId = $request->get('userId');

        $startTime = date('Y-m-d 00:00:00');
        $stopTime  = date('Y-m-d 00:00:00',strtotime('+1'));
        $count = CompanyOrder::where('com_id',$userId)->whereBetween('writetime',[$startTime,$stopTime])->count();

        if($count >= 5){
            return $this->json([],0,'每日最多充值5次');
        }

        $order = CompanyOrder::add($userId, $order);

        if($order->id){
            return $this->json();
        }else{
            return $this->json([],0,'提交失败');
        }
    }

    protected function logout(Request $request)
    {
        JWTAuth::parseToken()->invalidate();
        return $this->json();
    }

    /**
     * 账户明细
     *
     * @param Request $request
     * @return void
     */
    protected function trade(Request $request)
    {
        $type   = $request->input('type', 'all');
        $userId = $request->get('userId');
        $num    = (int)$request->input('num', 20);
        if(!in_array($type, ['all', '0', '1', '2', '3'], true)){
            return $this->json([], 0, '参数错误');
        }

        if($type != 'all'){
            $where[] = ['type', '=', $type];
        }
        $where[] = ['com_id', '=', $userId];

        $tradeList = CompanyTrade::list($where, $num);
        return $this->json(compact('tradeList'));
    }

    /**
     * 账号明细详情
     *
     * @param Request $request
     * @return void
     */
    protected function tradeInfo(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.tradeInfo.rule'),config('rules.tradeInfo.self'));
        $userId    = $request->get('userId');
        $id        = $request->input('id');
        $tradeInfo = CompanyTrade::info($id);
        if($tradeInfo->com_id != $userId){
            return $this->json([], 0, '无权访问');
        }

        $tradeInfo->vul = '';
        if($tradeInfo->type == 2 || $tradeInfo->type == 3){
            $where[] = ['id','=',$tradeInfo->trade_id];
            $tradeInfo->vul = Vul::tradeInfo($where);
            $tradeInfo->vul->hid = User::info($tradeInfo->vul->uid)->hid;
            $withdrawVul = WithdrawVul::where('vid', $tradeInfo->vul->id)->first();
            if($withdrawVul){
                $tradeInfo->order = Withdraw::where('aid', $withdrawVul->aid)->first()->transaction_number;
            }
        }

        if($tradeInfo->type == 0){
            $order = CompanyOrder::info($tradeInfo->trade_id);
            $tradeInfo->order = $order->txhash;
        }
        return $this->json(compact('tradeInfo'));
    }

    /**
     * 商户修改密码
     *
     * @param Request $request
     * @return void
     */
    protected function updatePassword(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.updatePassword.rule'),config('rules.updatePassword.self'));
        $userId = $request->get('userId');
        $info = Company::info($userId);

        if($info->pwd != encodePassword($request->input('oldPassword'))){
            return $this->json([], 0, '旧密码错误');
        }

        $res = Company::updatePassword($userId, $request->newPassword);

        if($res){
            return $this->json();
        }else{
            return $this->json([], 0, '修改失败');
        }
    }

    /**
     * 修改个人资料
     *
     * @param Request $request
     * @return void
     */
    protected function update(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.companyUpdate.rule'),config('rules.companyUpdate.self'));
        $logo_url = $request->input('logo_url');
        $contact = $request->input('contact');
        $introduction = $request->input('introduction');

        if(empty($logo_url)){
            return $this->json([], 0, '厂商logo不能为空');
        }
        if(empty($contact)){
            return $this->json([], 0, '厂商联系方式不能为空');
        }

        if(empty($introduction)){
            return $this->json([], 0, '厂商简介不能为空');
        }

        //验证LOGO格式
        if(!empty($logo_url) && is_string($logo_url)){
	        if (!preg_match("/^\/upload\/[a-zA-Z0-9]{20,50}\.(jpg|png)$/",$logo_url)) {
	        	return $this->json([], 0, '图片格式错误');
            }
            $item['logo'] = $logo_url;
        }

        $userId = $request->get('userId');
        $userInfo = Company::info($userId);

        if($userInfo->state == 1 && $userInfo->company_name != $request->company_name){
            return $this->json([], 0, '名称不允许修改');
        }

        if($userInfo->email_real != 0 && $userInfo->email != $request->email){
            return $this->json([], 0, '邮箱不允许修改');
        }

        if(!empty($request->eth_addr) && !preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{42,}$/u",$request->eth_addr)){
            return $this->json([], 0, '地址格式错误');
        }

        $companyRes = Company::updateInfo($userId, $request);

        if($companyRes){
            $item['introduction'] = clean($introduction);
            $item['contact']      = $request->input('contact');
            CompanyInfo::where('company_id',$userId)->update($item);
            return $this->json();
        }else{
            return $this->json([], 0, '修改失败');
        }
    }

    /**
     * 漏洞上传图片
     *
     * @param Request $request
     * @return void
     */
    protected function upload(Request $request)
    {
        $this->validate($request,config('rules.vulUpload.rule'),config('rules.vulUpload.self'));

        $file = $request->file('file');
        if($file->isValid()){
            $extension = $file->getClientOriginalExtension();
            $realPath  = $file->getRealPath();
            $filename  = md5(mt_rand(10000,99999).microtime()).'.'.strtolower($extension);
            if(!in_array(strtolower($extension),['jpg','png','jpeg','gif'])){
                return $this->json([], 0, '图片错误');
            }
            $bool = Storage::disk('public')->put($filename, file_get_contents($realPath));
            if($bool){
                $imgpath = '/upload/'.$filename;
                return $this->json(compact('imgpath'));
            }else{
                return $this->json([],0,'上传失败');
            }
        }else{
            return $this->json([],0,'无效的上传');
        }
    }
}
