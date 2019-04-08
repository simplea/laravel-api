<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * --------------------------------------------------------------------------
 * 跨域中间件组
 * --------------------------------------------------------------------------
 */
Route::group(['middleware' => 'cors'], function () {
    /**
     * --------------------------------------------------------------------------
     * 公共接口
     * --------------------------------------------------------------------------
    */

    // 验证码
    Route::get('captcha','User\UserController@captcha')->name('user.captcha');
    // 白帽子、漏洞、交易量总量接口
    Route::get('common/total','Common\CommonController@total')->name('common.total');
    // 漏洞动态列表
    Route::get('common/vul/trend','Common\CommonController@vulTrend')->name('common.vul.trend');
    // 公开漏洞详情
    Route::get('common/public/vul/info','Common\CommonController@publicVulInfo')->name('common.public.vul.info');
    // 漏洞奖赏厂商列表
    Route::get('common/company/audit','Common\CommonController@companyAuditList')->name('common.company.audit');
    // 生态节点
    Route::get('common/company/node','Common\CommonController@companyNodeList')->name('common.company.node');
    // 基金动态
    Route::get('common/fund','Common\CommonController@fund')->name('common.fund');
    // 基金动态详情
    Route::get('common/fund/info','Common\CommonController@fundInfo')->name('common.fund.info');
    // 新闻列表
    Route::get('common/news','Common\CommonController@news')->name('common.news');
    // 基金token动态
    Route::get('common/fund/token','Common\CommonController@fundToken')->name('common.fund.token');
    // 资产损失风险率
    Route::get('common/vul/tag','Common\CommonController@vulTag')->name('common.vul.tag');
    // 厂商黑名单
    Route::get('common/company/mark','Common\CommonController@companyMark')->name('common.company.mark');
    // 厂商类型统计
    Route::get('common/company/type/month','Common\CommonController@companyTypeMonth')->name('common.company.type.month');
    // 漏洞类型统计
    Route::get('common/vul/type/month','Common\CommonController@targetTypeMonth')->name('common.vul.type.month');
    // 风险厂商列表
    Route::get('common/company/risk','Common\CommonController@companyRiskList')->name('common.company.risk');
    // 风险厂商总数详情
    Route::get('common/company/vul/total/list','Common\CommonController@companyVulTotalList')->name('common.company.vul.total.list');
    // 漏洞总数
    Route::get('common/vul/all/total','Common\CommonController@vulAllTotal')->name('common.vul.all.total');
    // 漏洞修复列表
    Route::get('common/vul/fix/list','Common\CommonController@vulFixList')->name('common.vul.fix.list');
    // 厂商未入驻信息
    Route::get('common/company/non','Common\CommonController@companyNon')->name('common.company.non');
    // 厂商入驻信息
    Route::get('common/company/info','Common\CommonController@companyInfo')->name('common.company.info');
    // 白帽子排行
    Route::get('common/white/rank','Common\CommonController@whiteRank')->name('common.white.rank');
    // 白帽子漏洞详情
    Route::get('common/white/vul/detail','Common\CommonController@whiteVulDetail')->name('common.white.vul.detail');
    // token页面
    Route::get('common/token/vul/detail','Common\CommonController@tokenVulDetail')->name('common.white.vul.detail');
    // 当前年月
    Route::get('common/year/month','Common\CommonController@yearMonth')->name('common.year.month');
    // 赏金发布历史
    Route::get('common/company/reward/history/info','Common\CommonController@rewardHistoryInfo')->name('common.company.reward.history.info');
    /**
     * --------------------------------------------------------------------------
     * 白帽子用户路由组
     * --------------------------------------------------------------------------
    */

    // 白帽子登陆
    Route::post('user/login','User\UserController@login')->name('user.login');
    // 白帽子注册
    Route::post('user/register','User\UserController@register')->name('user.register');
    // 擅长领域
    Route::get('user/skill','User\SkillController@list')->name('user.skill');
    // 漏洞列表
    Route::get('user/vul/cate','User\VulCateController@list')->name('user.vul.cate.list');
    // 搜索厂商临时表
    Route::get('company/tmp','User\CompanyTmpController@list')->name('company.tmp.list');
    Route::get('company','User\CompanyController@list')->name('company.list');
    // metamask登录获取nonce
    Route::post('user/metamask/nonce','User\UserController@nonce')->name('user.metamask.nonce');
    // metamask nonce 登录
    Route::post('user/metamask/nonce/auth','User\UserController@nonceAuth')->name('user.metamask.nonce.auth');
    
    Route::group(['middleware' => 'userBase'],function(){
        // 用户信息
        Route::get('user/info','User\UserController@info')->name('user.info');
        // 消息列表
        Route::get('user/msg/list','User\UserController@msgList')->name('user.msg.list');
        // 用户地址修改
        Route::post('user/address/update','User\UserController@addressUpdate')->name('user.update');
        // 用户设置擅长领域
        Route::post('user/skill/set','User\UserController@setSkill')->name('user.skill.set');
        // 漏洞提交
        Route::post('vul/add','User\VulController@add')->name('vul.add');
        // 漏洞图片上传
        Route::post('vul/upload','User\VulController@upload')->name('vul.upload');
        // 头像图片上传
        Route::post('user/upload','User\UserController@upload')->name('user.upload');
        // 漏洞列表
        Route::get('vul/list','User\VulController@list')->name('vul.list');
        // 漏洞详情
        Route::get('vul/info','User\VulController@info')->name('vul.info');
        // 重新补充内容
        Route::post('vul/reupdate','User\VulController@reupdate')->name('vul.reupdate');
        // 漏洞通过审核列表 提现列表
        Route::get('vul/pass/list','User\VulController@passList')->name('vul.list');
        // 漏洞提现
        Route::post('vul/withdraw','User\VulController@withdraw')->name('vul.withdraw');
        // 提现交易列表
        Route::get('vul/withdraw/trade','User\VulController@withdrawTrade')->name('vul.withdraw.trade');
        // 交易详情
        Route::get('vul/withdraw/detail','User\VulController@withdrawDetail')->name('vul.withdraw.detail');
        // 申请dvp认领
        Route::get('vul/dvp/audit','User\VulController@dvpAudit')->name('vul.dvp.audit');
        // 退出登录
        Route::get('user/logout','User\UserController@logout')->name('user.logout');
    });


    /**
     * --------------------------------------------------------------------------
     * 厂商路由组
     * --------------------------------------------------------------------------
    */

    // 厂商登录
    Route::post('company/login','Company\UserController@login')->name('company.login');
    // 厂商注册
    Route::post('company/register','Company\UserController@register')->name('company.register');
    // 邮箱激活
    Route::get('company/active/email','Company\UserController@activeEmail')->name('company.active.email');
    // 忘记密码
    Route::post('company/forget/password','Company\UserController@forgetPass')->name('company.forget.pass');
    // 忘记密码token信息
    Route::get('company/forget/token/info','Company\UserController@forgetPassTokenInfo')->name('company.forget.token.info');
    // 重置密码
    Route::post('company/forget/repassword','Company\UserController@forgetRepass')->name('company.forget.repassword');
        // 发送漏洞详情邮件
        Route::get('company/vul/info/send/email','Company\VulController@infoSendMail')->name('company.vul.info.send.email');

    Route::group(['middleware' => 'companyBase'],function(){
        // 厂商信息
        Route::get('company/info','Company\UserController@info')->name('company.info');
        // 消息列表
        Route::get('company/msg/list','Company\UserController@msgList')->name('company.msg.list');
        // 厂商发送注册邮件
        Route::get('company/send/email','Company\UserController@sendEmail')->name('company.send.email');
        // 漏洞管理页面
        Route::get('company/vul','Company\VulController@list')->name('company.list');
        // 漏洞详情
        Route::get('company/vul/info','Company\VulController@info')->name('company.vul.info');
        // 确认漏洞
        Route::get('company/vul/sure/fix','Company\VulController@sureFix')->name('company.vul.sure.fix');
        // 悬赏管理
        Route::get('company/reward','Company\VulController@reward')->name('company.reward');
       // 充值保证金
        Route::post('company/deposit','Company\UserController@deposit')->name('company.deposit');
        // 商户认领
        Route::post('company/vul/claim','Company\VulController@claim')->name('company.vul.claim');
        // 退出登录
        Route::get('company/logout','Company\UserController@logout')->name('company.logout');
        // 漏洞管理修改
        Route::post('company/reward/update','Company\VulController@rewardUpdate')->name('company.reward.update');
        // 赏金草稿箱
        Route::post('company/reward/draft/update','Company\VulController@rewardDraftUpdate')->name('company.reward.draft.update');
        // 赏金草稿箱预览
        Route::get('company/reward/draft/view','Company\VulController@rewardDraftView')->name('company.reward.draft.view');
        // 账户明细
        Route::get('company/trade','Company\UserController@trade')->name('company.trade');
        // 账户明细详情
        Route::get('company/trade/info','Company\UserController@tradeInfo')->name('company.trade.info');
        // 修改密码
        Route::post('company/update/password','Company\UserController@updatePassword')->name('company.update.password');
        // 修改信息
        Route::post('company/update','Company\UserController@update')->name('company.update');
        // 商户图片上传
        Route::post('company/upload','Company\UserController@upload')->name('company.upload');
    });
});
