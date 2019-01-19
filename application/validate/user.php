<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 16:24
 */

namespace app\validate;


use think\Validate;

class user extends Validate{
    protected $rule =   [
        'username'  => 'require|max:25',
        'password'  => 'require|confirm|max:25|min:6',
        'password_confirm'  => 'require|confirm:password',
        'status'  => 'require',
    ];

    protected $message  =   [
        'username.require' => '用户名称必须',
        'username.max'     => '用户名称最多不能超过25个字符',
        'password.require'     => '用户密码必须',
        'password.max'     => '用户密码最多不能超过25个字符',
        'password.min'     => '用户密码最少不能小于6个字符',
        'password_confirm.confirm'     => '两次输入的密码不相同',
        'status.require'     => '用户状态必须',
    ];
    protected $scene = [
        'edit'  =>  ['username','status'],
        'add'  =>  ['username','password','password_confirm','status'],
    ];

}