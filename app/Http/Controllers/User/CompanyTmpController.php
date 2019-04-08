<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanyTmp;

class CompanyTmpController extends Controller
{
    /**
     * 商户列表
     *
     * @param Request $request
     * @return void
     */
    protected function list(Request $request)
    {
        delHtmlTag($request);
        $this->validate($request,config('rules.companyTmpList.rule'),config('rules.companyTmpList.self'));
        $company = CompanyTmp::select('company','fxh_rank')->where('company','like','%'.$request->name.'%')->take(20)->get();
        return $this->json(compact('company'));
    }
}
