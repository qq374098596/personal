<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 10:27
 */

namespace app\validate;


use think\Validate;

class login extends Validate{
    protected $rule =   [
        'username'  => 'require|max:20',
        'password'  => 'require',
        //'captcha' =>'require|captcha',
    ];

    protected $message  =   [
        'username.require' => '用户名称必填',
        'username.max'     => '用户名称最多不能超过20个字符',
        'password.require' => '用户密码必填',
        //'captcha.require'     => '验证码必填',
        //'captcha.captcha'     => '验证码错误',
    ];

}