<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 18:19
 */

namespace app\model;


class Node extends Base{
    protected $pk = 'id';
    protected $table = "fanxiang_admin_node";
    // 获取所有节点信息
    public function getAllNode($where = '' , $order = 'sort DESC') {
        return $this->where($where)->order($order)->select();
    }
    // 获取单个节点信息
    public function getNode($where = '',$field = '*') {
        return $this->field($field)->where($where)->find();
    }

    // 删除节点
    public function delNode($where) {
        if($where){
            return $this->where($where)->delete();
        }else{
            return false;
        }
    }

    // 更新节点
    public function upNode($data) {
        if($data){
            return $this->data($data)->isUpdate(true)->save();
        }else{
            return false;
        }
    }

    // 子节点
    public function childNode($id){
        return $this->where(array('pid'=>$id))->select();
    }
}