<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 11:14 AM
 */

namespace app\api\controller;


use app\model\Collection;
use app\model\Daoshi;
use app\model\Hangye;
use app\model\Turns;

class Tutor extends Api{
    protected $db_turn;
    protected $db_daoshi;
    protected $db_hangye;
    protected $db_collection;
    public function _initialize(){
        parent::_initialize();
        $this->db_turn = new Turns();
        $this->db_daoshi = new Daoshi();
        $this->db_hangye = new Hangye();
        $this->db_collection = new Collection();
    }
    //创业导师
    public function index(){
        $page = input('page', 1);
        $where = '';
        $order = 'id desc';
        $pageSize = 15;
        $data_daoshi =  $this->db_daoshi->getPageSelect($where,$order,$pageSize)->each(function ($item,$key){
            return $item;
        });
        if ($page > 1){
            return $this->jsonp(['daoshi'=>$data_daoshi->items()]);
        }
        $data_imgs = $this->db_turn->where(array('page'=>'chuangyedaoshi','status'=>1))->field('src,id,version')->order('id desc')->select();
        //dump($data_daoshi);exit();
        $data_hangye =  $this->db_hangye->select();
        $res = array(
            'turns' => $data_imgs,
            'daoshi' => $data_daoshi->items(),
            'hangye' => $data_hangye,
            'code' => 200
        );
        return $this->jsonp($res);
    }
    //搜索导师
    public function search(){
        $hangye = input('hangye');
        $paixu = input('paixu');
        $text = input('text');
        $where = ' 1=1 ';
        if ($text) {
           $where .= " and name like '%".$text."%'";
        }
        if ($hangye) {
            $where .= " and hangyeid = ".$hangye;
        }
        $order = 'id desc';
        if ($paixu) {
            $order = "zizhi desc";
        }
        $pageSize = 15;
        $data_daoshi =  $this->db_daoshi->getPageSelect($where,$order,$pageSize)->each(function ($item){
            return $item;
        });
        $res = array(
            'daoshi' => $data_daoshi->items(),
            'code' => 200
        );
        return $this->jsonp($res);
    }
    //导师详情
    public function detail(){
        $id = input('id');
        $uid = input('uid', 0);
        $data_daoshi =  $this->db_daoshi->where(array('id'=>$id))->find();
        $data_daoshi['imglist'] = json_decode($data_daoshi['imglist']);
        $shoucang =  $this->db_collection->where(array('mid'=>$id,'uid'=>$uid,'kind'=>4))->field('status')->find();
        if ($shoucang) {
            $shoucang = $shoucang['status'];
        }else{
            $shoucang = 0;
        }
        $res = array(
            'daoshi' => $data_daoshi,
            'is_shoucang' => $shoucang,
            'code' => 200
        );
        return $this->jsonp($res);
    }
}