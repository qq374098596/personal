<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 16:45
 */

namespace app\validate;


use think\Validate;

class member extends Validate{
    protected $rule =   [
        'name'  => 'require',
        'nickname'  => 'require',
        'headimg'  => 'require',
        'sex'  => 'require',
    ];

    protected $message  =   [
        'name.require' => '姓名必填',
        'nickname.require' => '昵称必填',
        'headimg.require'  => '头像必填',
        'sex.require'      => '性别必填',
    ];
}