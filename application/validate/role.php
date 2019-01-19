<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 14:06
 */

namespace app\validate;


use think\Validate;

class role extends Validate{
    protected $rule =   [
        'name'  => 'require|max:25',
    ];

    protected $message  =   [
        'name.require' => '角色名称必须',
        'name.max'     => '角色名称最多不能超过25个字符',
    ];
}