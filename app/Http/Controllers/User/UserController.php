<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Msg;
use App\Models\Vul;
use App\Models\Withdraw;
use App\Models\CashFlow;
use App\Models\SkillUid;
use kornrunner\Keccak;
use Elliptic\EC;
use JWTAuth;
use Captcha;

class UserController extends Controller
{
    /**
     * 用户注册
     *
     * @param Request $request
     * @return json
     */
    protected function register(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.userRegister.rule'),config('rules.userRegister.self'));

        $receipt = $request->input('receipt', '');
        if(!empty($receipt)){
            if (!preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{42,}$/u",$receipt)) {
                return $this->json([],0,'地址格式错误');
            } 
        }

        $user = User::create([
            'hid' => $request->hid,
            'pwd' => encodePassword($request->pwd),
            'receipt' => $receipt,
        ]);

        if($user->id){
            $token = JWTAuth::fromUser($user);
            return $this->json(compact('token'));
        }else{
            return $this->json([],0,'注册失败');
        }
    }

    /**
     * 用户登录
     *
     * @param Request $request
     * @return json
     */
    protected function login(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.userLogin.rule'),config('rules.userLogin.self'));

        $hid   = $request->input('hid');

        $user  = User::where('hid',$hid)->first();
        if(encodePassword($request->pwd) != $user->pwd){ return $this->json([],0,'密码错误'); }

        $sinfo = SkillUid::info($user->id);

        $skill = $sinfo ? 1 : 0;

        $token = JWTAuth::fromUser($user);
        User::where('id',$user->id)->update([
            'lastlogin_ip' => $request->getClientIp(),
            'lastlogin_time' => date('Y-m-d H:i:s',time())
        ]);
        return $this->json(compact('token','skill'));
    }

    /**
     * 设置擅长领域
     *
     * @return json
     */
    protected function setSkill(Request $request)
    {
        $this->validate($request,config('rules.userSkill.rule'),config('rules.userSkill.self'));

        $hid    = $request->input('hid');
        $userId = $request->get('userId');
        $userInfo = User::info($userId);
        if(count($request->ids)>5){
            return $this->json([],0,'最多选择5个擅长领域');
        }

        if($hid && $userInfo->hid != $hid){
            $this->validate($request,config('rules.userHid.rule'),config('rules.userHid.self'));
            User::where('id',$userId)->update(['hid' => $hid]);
        }
    
        SkillUid::del($userId);

        $status = array_map(function($item) use ($userId){
            return SkillUid::create(['uid' =>$userId,'sid' => $item]);
        },$request->ids);

        if($status){
            return $this->json();
        }else{
            return $this->json([],0,'修改失败');
        }
    }

    /**
     * 用户信息
     *
     * @param Request $request
     * @return 用户信息
     */
    protected function info(Request $request)
    {
        $userId           = $request->get('userId');
        $info             = User::info($userId);
        $info->avatar_url = $info->avatar_url;
        $language         = $request->input('language', 'zh_CN');

        // ether余额
        $vulWhere       = ['uid' => $userId,'state' => Vul::STATE_SUCCESS];
        $totalVulPrice  = Vul::getTotalVulPriceByUid($vulWhere);
        $withWhere      = ['uid' => $userId,'state' => Withdraw::STATE_TRUE, 'type' => Withdraw::TYPE_FALSE];
        $totalWithPrice = Withdraw::getTotalMoneyWithDrawByUid($withWhere);
        $ethPrice       = $totalVulPrice->price-$totalWithPrice->price;

        // com余额
        $totalVulcom  = Vul::getTotalVulcomByUid($vulWhere);
        $withcomWhere = ['uid' => $userId,'state' => Withdraw::STATE_TRUE, 'type' => Withdraw::TYPE_TRUE];
        $totalWithcom = Withdraw::getTotalMoneyWithDrawByUid($withcomWhere);
        $comPrice     = $totalVulcom->price-$totalWithcom->price;

        $vulCount = Vul::where('uid',$userId)->count();

        $vulIds = Vul::where([
            'uid' => $userId,
            'state' => Vul::COMPANY_STATE_PASS
        ])
        ->whereIn('company_state', [Vul::COMPANY_STATE_INIT, Vul::COMPANY_STATE_UNPASS, Vul::COMPANY_STATE_UNcom])
        ->get()
        ->pluck('id');
        $cashWhere = ['uid' => $userId,'type' => CashFlow::TYPE_FOUR];
        $cashPrice = CashFlow::frame($cashWhere, $vulIds);
        $frame = $cashPrice->price/100;

        $skillList = SkillUid::skillList($userId);
        if($language == 'en_US'){
            foreach($skillList as $val){
                $val->name = $val->name_en;
            }
        }

        $where = ['obj_id' => $userId, 'type' => Msg::TYPE_USER, 'status' => 0];
        $msgCount = Msg::where($where)->count();
        $msgList  = Msg::where($where)->get();
        return $this->json(compact('info','ethPrice','comPrice','vulCount','frame','skillList','msgCount','msgList'));
    }

    /**
     * 验证码
     *
     * @return img
     */
    protected function captcha()
    {
        return Captcha::create();
        return $this->json(compact('captcha'));
    }

    /**
     * 个人钱包地址修改
     *
     * @param Request $request
     * @return void
     */
    protected function addressUpdate(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.addressUpdate.rule'),config('rules.addressUpdate.self'));
        $userId = $request->get('userId');
        $receipt = $request->input('receipt');

        $res = User::where('id',$userId)->update(['receipt' => $receipt]);

        if($res){
            return $this->json();
        }else{
            return $this->json([],0,'修改失败');
        }
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
        if($type != 0){
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

    protected function logout(Request $request)
    {
        JWTAuth::parseToken()->invalidate();
        return $this->json();
    }

    /**
     * 头像上传图片
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
            $filename  = md5(mt_rand(10000,99999).microtime()).'.'.$extension;
            if(!in_array(strtolower($extension),['jpg','png','jpeg','gif'])){
                return $this->json([], 0, '图片错误');
            }
            $bool = Storage::disk('public')->put($filename, file_get_contents($realPath));
            if($bool){
                $imgpath = '/upload/'.$filename;
                $userId  = $request->get('userId');
                User::saveLogo($userId, $imgpath);
                return $this->json(compact('imgpath'));
            }else{
                return $this->json([],0,'上传失败');
            }
        }else{
            return $this->json([],0,'无效的上传');
        }
    }

    /**
     * metamask获取nonce
     *
     * @return string nonce
     */
    protected function nonce(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.addressUpdate.rule'),config('rules.addressUpdate.self'));
        $receipt = $request->input('receipt', '');

        $user = User::where('receipt', $receipt)->first();
        if(!$user){
            return $this->json([], 0, '无此用户');
        } 

        $lastTime = $user->lastlogin_time;
        $id       = $user->id;

        $nonce = substr(md5(strtotime($lastTime).$id), -9, -1);

        return $this->json(compact('nonce'));
    }

    /**
     * metamask nonce 登录
     *
     * @param Request $request
     * @return void
     */
    protected function nonceAuth(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.addressUpdate.rule'),config('rules.addressUpdate.self'));

        $receipt   = $request->input('receipt', '');
        $signature = $request->input('signature', '');

        $user = User::where('receipt', $receipt)->first();
        if(!$user){
            return $this->json([], 0, '无此用户');
        }

        $lastTime = $user->lastlogin_time;
        $id       = $user->id;

        $nonce = substr(md5(strtotime($lastTime).$id), -9, -1);

        $message = 'I am signing my one-time nonce: '.$nonce;

        $verifySignature = $this->verifySignature($message , $signature, $receipt);

        if($verifySignature){
            $token = JWTAuth::fromUser($user);
            if($token){
                $user->fill([
                    'lastlogin_time' => date('Y-m-d H:i:s')
                ])->save();
            }
            return $this->json(compact('token'));
        }else{
            return $this->json([], 0, '授权失败');
        }
    }

    protected function pubKeyToAddress($pubkey) {
        $keccak = new Keccak;
        return "0x" . substr($keccak::hash(substr(hex2bin($pubkey->encode("hex")), 1), 256), 24);
    }

    protected function verifySignature($nonce, $signature, $address) {
        $msglen = strlen($nonce);
        $keccak = new Keccak;
        $hash   = $keccak::hash("\x19Ethereum Signed Message:\n{$msglen}{$nonce}", 256);
        $sign   = [
            "r" => substr($signature, 2, 64),
            "s" => substr($signature, 66, 64)
        ];
        $recid  = ord(hex2bin(substr($signature, 130, 2))) - 27;
        if ($recid != ($recid & 1))
        return false;

        $ec = new EC('secp256k1');
        $pubkey = $ec->recoverPubKey($hash, $sign, $recid);
        return $address == $this->pubKeyToAddress($pubkey);
    }
}
