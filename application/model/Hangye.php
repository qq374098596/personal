<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 21:01
 */

namespace app\model;


class Hangye extends Base{
    protected $pk = 'id';
    protected $table = 'fanxiang_hangye';
    public function getHangYes(){
        return $this->select();
    }
}