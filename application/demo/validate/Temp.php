<?php

namespace app\xiaoan\validate;
use think\Validate;
class Temp extends Validate
{
    //定义验证规则
    protected $rule = [
        'type|类型' => 'require',
        'name|名称'  => 'require',

    ];

    //定义验证提示
    protected $message = [
        'name.regex' => '行为标识由字母和下划线组成',
    ];
}
