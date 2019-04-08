<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * 商户列表
     *
     * @param Request $request
     * @return void
     */
    protected function list(Request $request)
    {
        $company = Company::list();
        return $this->json(compact('company'));
    }
}
