<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 9:47
 */

namespace app\validate;


use think\Validate;

class tutor extends Validate{
    protected $rule =   [
        'name'  => 'require',
        'headimg'  => 'require',
        'hangyeid'  => 'require',
        'xiangmu'  => 'require',
        'licheng'  => 'require',
        'status'  => 'require',
        'sort'  =>  'number',
    ];
    protected $message  =   [
        'name.require' => '姓名必填',
        'headimg.require' => '头像必填',
        'hangyeid.confirm'     => '行业必选',
        'xiangmu.confirm'     => '创业项目必填',
        'licheng.confirm'     => '创业历程必填',
        'status.require'     => '状态必须',
        'sort.number'   => '排序必须为数字',
    ];
}