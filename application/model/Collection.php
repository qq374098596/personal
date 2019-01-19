<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 2:36 PM
 */

namespace app\model;


/***
 * Class Collection 收藏表
 * @package app\model
 */
class Collection extends Base{
    protected $pk = 'id';
    protected $table = 'fanxiang_shoucang';
    public function getCollectionByUidId($id,$uid){
        return $this->where(array('mid'=>$id,'uid'=>$uid))->field('status')->find();
    }
}