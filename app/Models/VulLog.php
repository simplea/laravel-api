<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Vul;
class VulLog extends Model
{
    //
    protected $table   = 'com_vul_log';
    protected $guarded = [];

    const STATUS_UNPASS   = 1;       // 未通过
    const STATUS_RESUBMIT = 2;       // 再提交审核
    const STATUS_REFUSE   = 3;       // 厂商不认领

    const VUL_STATUS_SUBMIT = 4;      // 漏洞提交状态
    const VUL_STATUS_PASS   = 5;      // 审核通过状态
    const VUL_STATUS_UNPASS = 6;      // 审核拒绝状态

    const VUL_STATUS_CLAIM        = 7;  // 厂商认领状态
    const VUL_STATUS_comCLAIM     = 8;  // com认领状态
    const VUL_STATUS_comINITCLAIM = 9;  // 7天com认领状态
    const VUL_STATUS_UNcomCLAIM   = 10;  // com不认领状态
    const VUL_COMPNAY_INIT        = 11;  // 厂商默认认领状态
    const VUL_COMPNAY_UNINIT      = 12;  // 厂商默认认领金额不足状态

    const VUL_FIX_SURE  = 1; // 厂商修复
    const VUL_FIX_TOKEN = 2; // token已修复

    const EN_STATUS_UNPASS   = 101;       // 未通过
    const EN_STATUS_RESUBMIT = 102;       // 再提交审核
    const EN_STATUS_REFUSE   = 103;       // 厂商不认领

    const EN_VUL_STATUS_SUBMIT = 104;      // 漏洞提交状态
    const EN_VUL_STATUS_PASS   = 105;      // 审核通过状态
    const EN_VUL_STATUS_UNPASS = 106;      // 审核拒绝状态

    const EN_VUL_STATUS_CLAIM        = 107;  // 厂商认领状态
    const EN_VUL_STATUS_comCLAIM     = 108;  // com认领状态
    const EN_VUL_STATUS_comINITCLAIM = 109;  // 7天com认领状态
    const EN_VUL_STATUS_UNcomCLAIM   = 110;  // com不认领状态
    const EN_VUL_COMPNAY_INIT        = 111;  // 厂商默认认领状态
    const EN_VUL_COMPNAY_UNINIT      = 112;  // 厂商默认认领金额不足状态

    const EN_VUL_FIX_SURE  = 101; // 厂商修复
    const EN_VUL_FIX_TOKEN = 102; // token已修复

    const USER_STATUS_DECODE   = [
        self::VUL_STATUS_SUBMIT       => '您提交了漏洞，请等待com审核确认',
        self::STATUS_UNPASS           => '您的漏洞未通过com审核，请补充信息重新提交。com审核说明：',
        self::STATUS_RESUBMIT         => '您补充信息并重新提交了漏洞，请等待com审核',
        self::STATUS_REFUSE           => '厂商不认领您的漏洞。理由是：',
        self::VUL_STATUS_PASS         => '您的漏洞通过了com审核，审核说明：',
        self::VUL_STATUS_UNPASS       => '您的漏洞审核结果为：未通过。审核说明：',
        self::VUL_STATUS_CLAIM        => '您的漏洞已被厂商认领，相应的漏洞已发放，快去个人中心查看吧。',
        self::VUL_STATUS_comCLAIM     => 'com认领了您的漏洞，相应的奖励已发放，快去个人中心查看吧。',
        self::VUL_STATUS_comINITCLAIM => '您的漏洞审7天内未被厂商认领，系统默认漏洞由com认领，相应的漏洞奖励已由com发放，快去个人中心查看吧。',

        self::EN_VUL_STATUS_SUBMIT       => 'You have submitted the loophole, please wait for the confirmation by com.',
        self::EN_STATUS_UNPASS           => 'Your loophole does not pass the verification process of com, please supplement the report and submit again. com verification rules:',
        self::EN_STATUS_RESUBMIT         => 'You have supplemented the report and submitted the loophole again, please wait for the confirmation by com.',
        self::EN_STATUS_REFUSE           => 'The blockchain firm fails to accept your loophole by the following reasons:',
        self::EN_VUL_STATUS_PASS         => 'Your loophole has passed the verification process of com. Detailed results are given in the following:',
        self::EN_VUL_STATUS_UNPASS       => 'Verification result: Failed. Reasons:',
        self::EN_VUL_STATUS_CLAIM        => 'Your loophole had been claimed by the blockchain firm with rewards. Check the status in personal center.',
        self::EN_VUL_STATUS_comCLAIM     => 'Your loophole had been claimed by com with rewards. Check the status in personal center.',
        self::EN_VUL_STATUS_comINITCLAIM => 'Since your loophole had not been claimed by the blockchain firm for seven days since the submission, by default, the loophole had been claimed by com with rewards. Check the status in personal center.',
    ];


    const COMPANY_STATUS_DECODE = [
        self::VUL_STATUS_PASS         => '您收到了该漏洞，com审核漏洞信息参考：',
        self::VUL_STATUS_CLAIM        => '您认领了该漏洞，并支付漏洞奖励：',
        self::STATUS_REFUSE           => '您忽略了该漏洞，理由：',
        self::VUL_STATUS_comCLAIM     => 'com认领了该漏洞：',
        self::VUL_STATUS_comINITCLAIM => '超7天后您未认领漏洞，系统默认com认领了该漏洞：',
        self::VUL_FIX_SURE            => '您修复了漏洞，并确定该漏洞已被修复。',
        self::VUL_COMPNAY_INIT        => '超7天后你未认领漏洞，系统默认你认领了该漏洞',
        self::VUL_COMPNAY_UNINIT      => '超7天后你未认领漏洞，由于你的担保金余额不足，系统无法默认你认领漏洞。为保障你的项目安全，请快认领并修复该漏洞。',

        self::EN_VUL_STATUS_PASS         => 'You have received the report of the loophole. The severity and rewards triaged by com are:',
        self::EN_VUL_STATUS_CLAIM        => 'You have claimed the loophole and made the payment for the corresponding rewards:',
        self::EN_STATUS_REFUSE           => 'You have ignored the loophole with following reasons:',
        self::EN_VUL_STATUS_comCLAIM     => 'com has claimed the loophole:',
        self::EN_VUL_STATUS_comINITCLAIM => 'Since you had not claimed the loophole for seven days since the submission, com had claimed the loophole by default:',
        self::EN_VUL_FIX_SURE            => 'You have patched the loophole and confirmed that the loophole has been fixed.',
        self::EN_VUL_COMPNAY_INIT        => 'You have not claimed the loophole for seven days since the submission. By default, com has claimed the loophole for you.',
        self::EN_VUL_COMPNAY_UNINIT      => 'You have not claimed the loophole for seven days since the submission. Since you do not have enough deposit fund in your balance, com can not claim the loophole for you. For your own safety, please claim the loophole immediately and fix it.'
    ];

    protected function list($where = [])
    {
        return self::where($where)->get();
    }

    /**
     * 用户消息列表
     *
     * @param [type] $where
     * @return void
     */
    protected function vulLogList($where)
    {
        $logList = self::where($where)->get()->toArray();
        return array_map(function($item){
            if($item['state'] != 2){
                return [$item['created_at'] => self::USER_STATUS_DECODE[$item['state']].'<span style="color: red;">'.$item['content'].'</span>'];
            }else{
                return [$item['created_at'] => self::USER_STATUS_DECODE[$item['state']]];
            }
        }, $logList);
    }

    /**
     * 厂商消息列表
     *
     * @param [type] $where
     * @return void
     */
    protected function vulComLogList($where, $level, $price = 0)
    {
        $logList = self::where($where)->get()->toArray();
        return array_map(function($item) use ($level,$price){
            if($item['state'] == 3){
                return [$item['created_at'] => '<span style="color: red;">'.self::COMPANY_STATUS_DECODE[$item['state']].$item['content'].'</span>。系统默认com认领漏洞：漏洞等级 '.Vul::LEVEL_DECODE[$level].'，漏洞奖励'.$price.'Ether,同时，该漏洞的归属权为com所有。'];
            }
        }, $logList);
    }

    /**
     * 发送消息
     *
     * @param string $vid       漏洞id
     * @param string $content   内容
     * @param integer $state    操作状态 1 漏洞未通过 2 用户再审核 3 厂商不认领
     * @return void
     */
    protected function add($vid = 0, $content = '', $state = 1)
    {
        return self::create([
            'vid'     => $vid,
            'state'   => $state,
            'content' => $content
        ]);
    }
}
