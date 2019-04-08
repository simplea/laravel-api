<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Skill;

class SkillController extends Controller
{
    /**
     * 擅长领域列表
     *
     * @return void
     */
    protected function list(Request $request)
    {
        $language = $request->input('language', 'zh_CN');
        $skill = Skill::list();
        if($language == 'en_US'){
            foreach($skill as $val){
                $val->name = $val->name_en; 
            }
        }
        return $this->json(compact('skill'));
    }
}
