<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 11:24 AM
 */

namespace app\model;


class Turns extends Base{
    protected $pk = 'id';
    protected $table = 'fanxiang_turnimgs';
    public function getTurnByPage($page='pinpaidianping'){
        return $this->where(array('page'=>$page,'status'=>1))->field('src,id,version')->order('id desc')->select();
    }
}