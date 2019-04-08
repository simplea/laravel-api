<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyHistory;
class CompanyInfo extends Model
{
    //
    protected $table   = 'com_company_info';
    protected $guarded = [];
    public $timestamps = false;

    const DRAFT_TRUE   = 1; // 发布内容
    const DRAFT_FALSE  = 0; // 草稿箱

    protected function addInfo($companyId, $contact = '')
    {
        if(!$companyId){ return '参数错误'; }
        return self::create([
            'company_id'    => $companyId,
            'contact'       => $contact,
            'test_range'    => "1. 目前可测试的域名包括但不限于（*.xxx.cn、 *.xx.com…），xx集团客户端包括所有通过官方途径发布的客户端程序\n2. 超出范围 注意：超出范围视为无效漏洞处理.",
            'low_vul_rate'  => "• 轻微信息泄露。包括但不仅限于路径信息泄露、svn信息泄露、phpinfo、异常信息泄露。\n• 部分对业务只能造成轻微影响的漏洞，包括但不仅限于部分反射型 XSS（包括反射型 DOM-XSS）、普通CSRF、URL跳转漏洞。",
            'mid_vul_rate'  => "• 普通信息泄露。包括但不仅限于客户端明文密码存储。\n• 需交互才能获取用户身份信息的漏洞。包括但不限于反射型 XSS、JSON Hijacking、重要操作的 CSRF、普通业务的存储型XSS。",
            'high_vul_rate' => "• 越权访问。包括但不仅限于绕过认证直接访问管理后台可操作、核心业务非授权访问、核心业务后台弱密码等。\n• 能直接盗取关键业务等用户身份信息的漏洞。包括：重点页面的存储型安全漏洞评分标准XSS漏洞、普通系统的SQL注入漏洞。\n• 高风险的逻辑设计缺陷。包括但不仅限于查看任意用户信息、修改相关状态等。\n• 高风险的信息泄漏漏洞。包括但不限于普通源代码压缩包泄漏、配置信息泄露；",
            'gra_vul_rate'  => "• 直接获取系统权限（服务器端权限、客户端权限）的漏洞。包括但不仅限于：命令注入、远程命令执行、上传获取 WebShell、SQL注入获取系统权限、缓冲区溢出（包括可利用的 ActiveX缓冲区溢出）。\n• 直接导致拒绝服务的漏洞。包括通过该远程拒绝服务漏洞直接导致线上应用系统、网络设备、服务器无法继续提供服务的漏洞。\n• 直接获取系统权限（服务器端权限、客户端权限）的漏洞。包括但不仅限于：命令注入、远程命令执行、上传获取 WebShell、SQL注入获取系统权限、缓冲区溢出（包括可利用的 ActiveX缓冲区溢出）。\n• 直接导致拒绝服务的漏洞。包括通过该远程拒绝服务漏洞直接导致线上应用系统、网络设备、服务器无法继续提供服务的漏洞。\n• 严重的逻辑设计缺陷。包括但不仅限于共识机制缺陷、验证可绕过等严重问题。\n• 严重级别的信息泄漏。包括但不限于重要DB的SQL注入漏洞、包含配置信息等敏感信息的源代码压缩包泄漏、包含订单、用户信息的日志文件；\n• 严重的登录及验证安全风险。\n• 可对用户或官方造成严重经济损失的漏洞。",
            'remarks'       => "• 漏洞奖励标准仅针对xx的业务。目前xx的域名包括但不限于（*.xx.cn、*.xx.com）xx\n• 客户端包括所有通过官方途径发布的客户端程序；\n• 提交在其他漏洞披露平台已提交的漏洞不计奖励。\n• 开放平台的第三方应用漏洞不计奖励；\n• 同一漏洞最早提交者得奖励。",
            'updatetime'    => date('Y-m-d H:i:s',time())
        ]);
    }

    protected function addCompanyReward($request, $draft = self::DRAFT_TRUE)
    {
        return self::create([
            'company_id'    => $request->get('userId'),
            'gra_start'     => $request->input('gra_start', '6.00'),
            'gra_end'       => $request->input('gra_end', '22.00'),
            'high_start'    => $request->input('high_start', '3.00'),
            'high_end'      => $request->input('high_end', '6.00'),
            'mid_start'     => $request->input('mid_start', '0.20'),
            'mid_end'       => $request->input('mid_end', '3.00'),
            'low_start'     => $request->input('low_start', '0.05'),
            'low_end'       => $request->input('low_end', '0.20'),
            'test_range'    => $request->input('test_range', ''),
            'low_vul_rate'  => $request->input('low_vul_rate', ''),
            'mid_vul_rate'  => $request->input('mid_vul_rate', ''),
            'high_vul_rate' => $request->input('high_vul_rate', ''),
            'gra_vul_rate'  => $request->input('gra_vul_rate', ''),
            'remarks'       => $request->input('remarks', ''),
            'updatetime'    => date('Y-m-d H:i:s',time()),
            'draft'         => $draft
        ]);
    }

    protected function updateInfo($request, $draft = self::DRAFT_TRUE)
    {
        $res = self::where([
            'company_id' => $request->get('userId'),
            'draft'      => $draft
            ])
        ->update([
            'gra_start'     => $request->input('gra_start', '6.00'),
            'gra_end'       => $request->input('gra_end', '22.00'),
            'high_start'    => $request->input('high_start', '3.00'),
            'high_end'      => $request->input('high_end', '6.00'),
            'mid_start'     => $request->input('mid_start', '0.20'),
            'mid_end'       => $request->input('mid_end', '3.00'),
            'low_start'     => $request->input('low_start', '0.05'),
            'low_end'       => $request->input('low_end', '0.20'),
            'test_range'    => $request->input('test_range', ''),
            'low_vul_rate'  => $request->input('low_vul_rate', ''),
            'mid_vul_rate'  => $request->input('mid_vul_rate', ''),
            'high_vul_rate' => $request->input('high_vul_rate', ''),
            'gra_vul_rate'  => $request->input('gra_vul_rate', ''),
            'remarks'       => $request->input('remarks', ''),
            'updatetime'    => date('Y-m-d H:i:s',time())
        ]);

        if($draft == self::DRAFT_TRUE){
            $comInfo = self::info($request->get('userId'));
            CompanyHistory::create([
                'company_id'    => $comInfo->company_id,
                'gra_start'     => $comInfo->gra_start,
                'gra_end'       => $comInfo->gra_end,
                'high_start'    => $comInfo->high_start,
                'high_end'      => $comInfo->high_end,
                'mid_start'     => $comInfo->mid_start,
                'mid_end'       => $comInfo->mid_end,
                'low_start'     => $comInfo->low_start,
                'low_end'       => $comInfo->low_end,
                'test_range'    => $comInfo->test_range,
                'low_vul_rate'  => $comInfo->low_vul_rate,
                'mid_vul_rate'  => $comInfo->mid_vul_rate,
                'high_vul_rate' => $comInfo->high_vul_rate,
                'gra_vul_rate'  => $comInfo->gra_vul_rate,
                'url'           => $comInfo->url,
                'logo'          => $comInfo->logo,
                'introduction'  => $comInfo->introduction,
                'remarks'       => $comInfo->remarks,
                'contact'       => $comInfo->contact,
                'draft'         => $comInfo->draft,
                'updatetime'    => $comInfo->updatetime
            ]);
        }
        return $res;
    }

    protected function info($companyId = 0, $draft = self::DRAFT_TRUE)
    {
        if(!$companyId) return false;
        return self::where([
            'company_id' => $companyId,
            'draft'      => $draft
            ])->first();
    }
}
