<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 19:18
 */

namespace app\validate;


use think\Validate;

class story extends Validate {
    protected $rule =   [
        'title'  => 'require',
        'banner'  => 'require',
        'daoshiid'  => 'require',
        'status'  => 'require',
    ];
    protected $message  =   [
        'title.require' => '标题必填',
        'banner.require' => 'banner必填',
        'daoshiid.confirm'     => '导师必填',
        'status.require'     => '状态必须',
    ];
}