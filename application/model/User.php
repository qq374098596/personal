<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 16:32
 */

namespace app\model;


class User extends Base {
    protected $pk = 'id';
    protected $table = "fanxiang_admin_user";
    public function findByUserName($username){
        return $this->where('username',$username)->where('status', 1)->find();
    }
}