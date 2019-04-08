<?php


return [
    /**
     * -------------------------------------------
     * 白帽子规则
     * -------------------------------------------
     */

    // 白帽子登陆规则
    'userLogin' => [
        'rule' => [
            'captcha' => 'required|captcha',
            'hid'     => 'required|between:2,20|exists:com_user,hid',
            'pwd'     => 'required|between:6,20',
        ],
        'self' => [
            'captcha.required' => '验证码不能为空',
            'captcha.captcha'  => '验证码错误',
            'hid.required'     => '标题不能为空',
            'hid.between'      => '账号必须是2~20位之间',
            'hid.exists'       => '用户不存在',
            'pwd.required'     => '密码不能为空',
            'pwd.between'      => '密码必须是6~20位之间',
        ]
    ],

    // 白帽子注册规则
    'userRegister' => [
        'rule' => [
            'captcha' => 'required|captcha',
            'hid'     => 'required|between:2,20|unique:com_user,hid',
            'pwd'     => ['required','between:6,20','regex:/[0-9]/','regex:/[a-zA-Z]+/','regex:/[~|_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/'],
            'repwd'   => 'required|same:pwd',
        ],
        'self' => [
            'hid.required'    => '账号不能为空',
            'hid.unique'      => '账号已经存在',
            'hid.between'     => '账号必须是2~20位之间',
            'captcha.required'=> '验证码不能为空',
            'captcha.captcha' => '验证码错误',
            'pwd.required'    => '密码不能为空',
            'pwd.between'     => '密码必须是6~20位之间',
            'pwd.regex'       => '密码必须包含数字、字母、特殊字符组成，例如： ._~!@#$%',
            'repwd.same'      => '密码不一致',
        ]
    ],

    // 白帽子用户名称验证
    'userHid' => [
        'rule' => [
            'hid' => 'required|between:2,20|unique:com_user,hid',
        ],
        'self' => [
            'hid.required'    => '账号不能为空',
            'hid.unique'      => '账号已经存在',
            'hid.between'     => '账号必须是2~20位之间',
        ]
    ], 

    // 白帽子擅长领域验证
    'userSkill' => [
        'rule' => [
            'ids'   => 'array|required',
            'ids.*' => 'required|numeric|between:1,1000000000|exists:com_skill,id'
        ],
        'self' => [
            'ids.array'      => '请选择擅长领域',
            'ids.*.required' => '请选择擅长领域',
            'ids.*.numeric'  => '请选择数字',
            'ids.*.between'  => '数字超出最大值',
            'ids.*.exists'   => '找不到此领域',
        ]
    ],

    // 白帽子漏洞类型验证
    'vulCate' => [
        'rule' => [
            'id' => 'required|numeric|between:1,1000000000|exists:com_cate,id'
        ],
        'self' => [
            'id.required' => '请选择分类',
            'id.numeric'  => '非法参数',
            'id.between'  => '参数超出最大值',
            'id.exists'   => '找不到此分类',
        ]
    ],

    // 搜索临时商户验证
    'companyTmpList' => [
        'rule' => [
            'name' => ['required','between:1,30','regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]{1,}$/u'],
        ],
        'self' => [
            'name.required' => '请选择分类',
            'name.between'  => '参数超出最大值',
            'name.regex'    => 'error request',
        ]
    ],

    // 提交漏洞验证
    'vulAdd' => [
        'rule' => [
            'captcha'         => 'required|captcha',
            'title'           => ['required','between:1,30','regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]{1,}$/u'],
            'target_type'     => 'required|between:1,1000000000|numeric|exists:com_cate,id',
            'attack_type'     => 'required|between:1,1000000000|numeric|exists:com_cate,id',
            'level'           => 'required|numeric|in:0,1,2,3',
            'description'     => 'required',
            'company_id'      => 'required|between:0,1000000000|numeric',
            'company_type'    => 'required|numeric|in:0,1,2,3,4,5,6',
        ],
        'self' => [
            'captcha.required'     => '验证码不能为空',
            'captcha.captcha'      => '验证码错误',
            'title.required'       => '请输入标题',
            'title.between'        => '标题最大字符30',
            'title.regex'          => '标题格式错误',
            'target_type.required' => '请选择类型',
            'target_type.between'  => '参数错误',
            'company_type.in'      => 'company_type参数错误',
            'description.required' => '详情不能为空',
            'company_id.required'  => '请选择厂商',
            'company_type.required'=> '厂商类型不能为空',
        ]
    ],

    // 补充漏洞信息

    'vulReUpdate' => [
        'rule' => [
            'id'          => 'required|numeric|exists:com_vul,id',
            'description' => 'required'
        ],
        'self' => [
            'id.required'          => '参数为空',
            'id.numeric'           => '参数错误',
            'id.exists'            => '此信息不存在',
            'description.required' => '请填写补充信息'
        ]
    ],

    // 漏洞详情
    'vulInfo' => [
        'rule' => [
            'id' => 'required|numeric|exists:com_vul,id',
        ],
        'self' =>[
            'id.required' => '参数为空',
            'id.numeric'  => '参数错误',
            'id.exists'   => '此信息不存在',
        ]
    ],

    // 上传图片验证
    'vulUpload' => [
        'rule' => [
            'file' => 'required|image|mimes:jpg,png,jpeg,gif|mimetypes:image/pjpeg,image/jpeg,image/gif,image/png|max:2048'
        ],
        'self' => [
            'file.required' => '请选择图片',
            'file.mimes'    => '上传图片格式错误',
            'file.mimetypes'=> '上传图片类型错误',
            'file.max'      => '上传图片太大',
            'file.image'    => '图片格式错误'
        ]
    ],

    // 白帽子提现验证
    'userWithdraw' => [
        'rule' => [
            'type' => 'required|in:0,1',
            'ids'   => 'array|required',
            'ids.*' => 'required|numeric|distinct|between:1,1000000000|exists:com_vul,id'
        ],
        'self' => [
            'type.required' => '参数错误',
            'ids.array'      => '请选择提现列表',
            'ids.*.distinct' => '重复的漏洞id',
            'ids.*.required' => '请选择漏洞',
            'ids.*.numeric'  => '参数错误',
            'ids.*.between'  => '参数超出最大值',
            'ids.*.exists'   => '找不到此漏洞',
        ]
    ],

    // 白帽子修改地址验证
    'addressUpdate' => [
        'rule' => [
            'receipt' => ['required','regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{42,}$/u'],
        ],
        'self' => [
            'receipt.required' => '地址不能为空',
            'receipt.regex'    => '地址格式错误',
        ]
    ],

    // 白帽子修改地址验证
    'withdrawDetail' => [
        'rule' => [
            'aid' => 'required|exists:com_cash_flow,id',
        ],
        'self' => [
            'aid.required' => '参数为空',
            'aid.exists'   => '此信息不存在',
        ]
    ],

    // 消息列表
    'msgList' => [
        'rule' => [
            'status' => 'required|numeric|in:0,1',
            'type'   => 'required|numeric|in:0,1',
        ],
        'self' => [
            'status.required' => '参数错误',
            'status.numeric'  => '参数错误',
            'status.in'       => '参数错误',
            'type.required'   => '参数错误',
            'type.numeric'    => '参数错误',
            'type.in'         => '参数错误',
        ]
    ],

    // 申请com认领
    'comAudit' => [
        'rule' => [
            'vid' => 'required|exists:com_vul,id'
        ],
        'self' => [
            'vid.required' => '参数错误',
            'vid.exists'   => '无此漏洞',
        ]
    ],

    /**
     * -------------------------------------------------
     * 厂商规则
     * -------------------------------------------------
     */

     // 厂商注册规则
    'companyRegister' => [
        'rule' => [
            'captcha'      => 'required|captcha',
            'company_name' => 'required|between:2,50|unique:com_company,company_name,2,state',
            'domain'       => 'required|active_url',
            'contact'      => 'required',
            'email'        => 'required|email|unique:com_company,email,2,state',
            'pwd'          => ['required','between:6,50','regex:/^[\w\d\-\._~!@#$%]{6,50}$/'],
            'repwd'        => 'required|same:pwd',
        ],
        'self' => [
            'company_name.required' => '账号不能为空',
            'company_name.unique'   => '账号已经存在',
            'company_name.between'  => '账号必须是2~50位之间',
            'domain.required'       => '域名不能为空',
            'domain.active_url'     => '域名不合法',
            'contact.required'      => '联系方式不能为空',
            'email.required'        => '邮箱不能为空',
            'email.email'           => '邮箱不合法',
            'email.unique'          => '此邮箱已经注册',
            'captcha.required'      => '验证码不能为空',
            'captcha.captcha'       => '验证码错误',
            'pwd.required'          => '密码不能为空',
            'pwd.between'           => '密码必须是6~50位之间',
            'pwd.regex'             => '密码必须包含数字、字母、特殊字符组成，例如： ._~!@#$%',
            'repwd.same'            => '密码不一致',
        ]
    ],


    // 商户登陆规则
    'comanyLogin' => [
        'rule' => [
            'captcha'      => 'required|captcha',
            'company_name' => 'required|between:2,20|exists:com_company,company_name',
            'pwd'          => 'required|between:6,20',
        ],
        'self' => [
            'captcha.required'      => '验证码不能为空',
            'captcha.captcha'       => '验证码错误',
            'company_name.required' => '标题不能为空',
            'company_name.between'  => '账号必须是2~20位之间',
            'company_name.exists'   => '用户不存在',
            'pwd.required'          => '密码不能为空',
            'pwd.between'           => '密码必须是6~20位之间',
        ]
    ],

    // 商户激活邮箱
    'comanyActiveEmail' =>[
        'rule' => [
            'token' => ['required','regex:/^[0-9A-Za-z]{30,50}$/','exists:com_email_token,token'],
        ],
        'self' => [
            'token.required' => 'token不能为空',
            'token.regex'    => '参数格式错误',
            'token.exists'   => 'token错误'
        ]
    ],

    // 保证金
    'deposit' => [
        'rule' => [
            'order' => ['required','regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]{42,}$/u','unique:com_company_order,txhash']
        ],
        'self' => [
            'order.required' => '交易号不能为空',
            'order.regex'    => '交易号格式错误',
            'order.unique'   => '该交易号已被使用，请重新输入正确的交易号',
        ]
    ],

    // 漏洞厂商认领
    'vulClaim' => [
        'rule' => [
            'type' => 'required|numeric|in:0,1',
            'id'   => 'required|numeric|exists:com_vul,id'
        ],
        'self' => [
            'type.required' => '缺少参数',
            'type.numeric'  => '参数错误',
            'type.in'       => '参数错误',
            'id.required'   => '缺少参数',
            'id.numeric'    => '参数错误',
            'id.exists'     => '此漏洞不存在',
        ]
    ],

    // 账户明细详情
    'tradeInfo' => [
        'rule' => [
            'id' => 'required|numeric|exists:com_company_trade,id',
        ],
        'self' => [
            'id.required' => '缺少参数',
            'id.numeric'  => '参数错误',
            'id.exists'   => '此订单不存在'
        ]
    ],
    
    // 厂商修改密码
    'updatePassword' => [
        'rule' => [
            'oldPassword'   => ['required','between:6,50','regex:/[0-9]/','regex:/[a-zA-Z]+/','regex:/[~|_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/'],
            'newPassword'   => ['required','between:6,50','regex:/[0-9]/','regex:/[a-zA-Z]+/','regex:/[~|_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/'],
            'reNewPassword' => 'required|same:newPassword',
        ],
        'self' => [
            'oldPassword.required'   => '旧密码不能为空',
            'oldPassword.between'    => '旧密码必须是6~50位之间',
            'oldPassword.regex'      => '旧密码必须包含数字、字母、特殊字符组成，例如： ._~!@#$%',
            'newPassword.required'   => '新密码不能为空',
            'newPassword.between'    => '新密码必须是6~50位之间',
            'newPassword.regex'      => '新密码必须包含数字、字母、特殊字符组成，例如： ._~!@#$%',
            'reNewPassword.required' => '确认密码不能为空',
            'reNewPassword.same'     => '两次密码不一致',
        ]
    ],

    // 厂商个人资料修改
    'companyUpdate' => [
        'rule' => [
            'contact' => 'required',
            'domain'  => 'required|active_url',
        ],
        'self' => [
            'contact.required'  => '联系方式不能为空',
            'domain.required'   => '域名不能为空',
            'domain.active_url' => '域名不合法',
        ]
    ],

    //商户忘记密码（用户名邮箱验证）
    'comanyForgetPass' =>[
        'rule' => [
            'companyName' => ['required', 'regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9@\-_]+$/u', 'exists:com_company,company_name'],
            'email'       => 'required|email',
        ],
        'self' => [
            'companyName.required' => '参数错误',
            'companyName.regex'    => '参数格式错误',
            'companyName.exists'   => '信息错误',
            'email.required'       => '邮箱不能为空',
            'email.email'          => '邮箱不合法',
        ]
    ],

    //商户忘记密码（token信息）
    'comanyForgetPassTokenInfo' =>[
        'rule' => [
            'token' => ['required', 'regex:/^[0-9A-Za-z]{30,50}$/u', 'exists:com_email_token_repass,token'],
        ],
        'self' => [
            'token.required'  => '参数错误',
            'token.regex'     => '参数错误',
            'token.exists'    => '参数错误',
        ]
    ],
    
    //商户忘记密码（token信息）
    'forgetRepass' =>[
        'rule' => [
            'token'      => ['required', 'regex:/^[0-9A-Za-z]{30,50}$/u', 'exists:com_email_token_repass,token'],
            'password'   => ['required','between:6,50','regex:/[0-9]/','regex:/[a-zA-Z]+/','regex:/[~|_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/'],
            'rePassword' => 'required|same:password',
        ],
        'self' => [
            'token.required'      => '参数错误',
            'token.regex'         => '参数错误',
            'token.exists'        => '参数错误',
            'password.required'   => '新密码不能为空',
            'password.between'    => '新密码必须是6~50位之间',
            'password.regex'      => '新密码必须包含数字、字母、特殊字符组成，例如： ._~!@#$%',
            'rePassword.required' => '确认密码不能为空',
            'rePassword.same'     => '两次密码不一致',
        ]
    ],


    /**
     * -----------------------------------------------------
     * 公共页面路由
     * -----------------------------------------------------
     */

    // 基金详情
    'fundInfo' => [
        'rule' => [
            'hash' => ['required','regex:/^[a-zA-Z0-9]{30,}$/u','exists:com_withdraw,transaction_number']
        ],
        'self' => [
            'hash.required' => '参数不能为空',
            'hash.regex'    => '参数格式错误',
            'hash.exists'   => '此信息不存在'
        ]
    ],

    // 态势风险
    'vulTag' => [
        'rule' => [
            'type' => 'required|in:0,1,2'
        ],
        'self' => [
            'type.required' => '参数不能为空',
            'type.in' => '参数错误',
        ]
    ],

    // 未入住
    'companyNon' => [
        'rule' => [
            'company' => 'required|exists:com_company_tmp,company'
        ],
        'self' => [
            'company.required' => '请输入厂商名称',
            'company.exists'   => '厂商不存在'
        ]
    ],

    // 入驻
    'companyInfo' => [
        'rule' => [
            'company' => 'required|exists:com_company,company_name'
        ],
        'self' => [
            'company.required' => '请输入厂商名称',
            'company.exists'   => '厂商不存在'
        ]
    ],

    // 白帽子漏洞详情
    'whiteVulDetail' => [
        'rule' => [
            'userName' => ['required', 'regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9@\-_]+$/u', 'exists:com_user,hid'],
            'month'    => 'required|in:00,01,02,03,04,05,06,07,08,09,10,11,12',
        ],
        'self' => [
            'userName.required' => '参数错误',
            'userName.regex'    => '参数错误',
            'userName.exists'   => '用户不存在',
            'month.required'    => '参数错误',
            'month.in'          => '参数错误',
        ]
    ],

    // 商户漏洞总数列表
    'companyVulTotalList' => [
        'rule' => [
            'companyName' => ['required', 'regex:/^[\x{4e00}-\x{9fa5}a-zA-Z0-9@\-_\.]+$/u', 'exists:com_company_tmp,company'],
            'month'       => 'required|in:00,01,02,03,04,05,06,07,08,09,10,11,12',
            'is_fix'      => 'required|in:1,all',
            'level'       => 'required|in:0,1,2,3,all'
        ],
        'self' => [
            'companyName.required' => '参数错误',
            'companyName.regex'    => '参数错误',
            'companyName.exists'   => '商户不存在',
            'month.required'       => '参数错误',
            'month.in'             => '参数错误',
            'is_fix.required'      => '参数错误',
            'is_fix.in'            => '参数错误',
            'level.required'       => '参数错误',
            'level.in'             => '参数错误',
        ]
    ],

    // 白帽子漏洞类型验证
    'rewardHistoryInfo' => [
        'rule' => [
            'id' => 'required|numeric|between:1,1000000000|exists:com_company_history,id'
        ],
        'self' => [
            'id.required' => '参数错误',
            'id.numeric'  => '非法参数',
            'id.between'  => '参数超出范围',
            'id.exists'   => '无此内容',
        ]
    ],
];