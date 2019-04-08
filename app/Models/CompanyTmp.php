<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTmp extends Model
{
    //
    protected $table   = "com_company_tmp";
    protected $guarded = [];

    protected function infoByName($companyName = '')
    {
        return self::where('company',$companyName)->first();
    }
}
