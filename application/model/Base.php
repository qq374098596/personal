<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 18:20
 */

namespace app\model;


use think\Model;

class Base extends Model {
    public function db_insert($data){
        return $this->save($data);
    }
    public function db_update($id, $data){
        return $this->where('id',$id)->data($data)->update();
    }
    public function db_find($id){
        return $this->where('id',$id)->find();
    }
    public function db_del($id){
        return $this->where('id',$id)->delete();
    }
    public function db_select(){
        return $this->select();
    }
    public function getPageSelect($where = '',$order='',$pageSize = 15){
        return $this->where($where)->order($order)->paginate($pageSize);
    }
}