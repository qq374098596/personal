<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 18:22
 */

namespace app\validate;


use think\Validate;

class node extends Validate {
    protected $rule =   [
        'name'  => 'require|max:25',
        'title'  => 'require|max:25',
        'pid'  => 'require',
        'level'  => 'require',
        'display'  => 'require',
    ];

    protected $message  =   [
        'name.require' => '节点名称必须',
        'name.max'     => '节点名称最多不能超过25个字符',
        'title.require' => '菜单名称必须',
        'title.max'     => '菜单名称最多不能超过25个字符',
        'pid.require'     => '级菜单必须',
        'level.require'     => '节点类型必须',
        'display.require'     => '菜单类型必须',
    ];
}