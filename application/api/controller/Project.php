<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 1:23 PM
 */

namespace app\api\controller;

use app\lib\qr;
use app\model\Collection;
use app\model\Hangye;
use app\model\Product;
use app\model\Turns;

/***
 * Class Project 项目(严选项目和普通项目)
 * @package app\api\controller
 */
class Project extends Api{
    protected $db_turn;
    protected $db_product;
    protected $db_hangye;
    protected $db_collection;
    public function _initialize(){
        parent::_initialize();
        $this->db_turn = new Turns();
        $this->db_product = new Product();
        $this->db_hangye = new Hangye();
        $this->db_collection = new Collection();
    }
    public function index(){
        $list = $this->db_product->getProducts();
        $data_imgs = $this->db_turn->where(array('page'=>'pinpaidianping','status'=>1))->field('src,id')->select();
        $data_hangye =  $this->db_hangye->select();
        $res = array(
            'pinpailist' => $list,
            'turns' => $data_imgs,
            'hangye' => $data_hangye,
            'code' => 200
        );
        return $this->jsonp($res);
    }
    public function search(){
        $hangye = input('hangye');
        $paixu = input('paixu');
        $text = input('text');
        $hp = input('hangping');
        $sxMin = input('min');
        $sxMax = input('msx');
        $where = ' where a.kind = 2';
        if ($text) $where .= " and a.name like '%".$text."%'";
        if ($hangye) $where .= " and a.hangyeid = '".$hangye."'";
        $order = '';
        if ($hp) $order .= " order by fen desc ";
        if ($sxMin && $sxMax) {
            $where .= "and a.jiamengfei<='".$sxMax."' and a.jiamengfei>='".$sxMin."'";
        }
        if ($paixu=='shijian') {
            $order .= " order by a.time";
        }elseif ($paixu=='redu') {
            $order =  ' order by pinglun_count desc';
        }
        $list =  $this->db_product->getProductSearch($where, $order);
        if (!$list) {
            $list =array();
        }
        $res = array(
            'pinpailist' => $list,
            'code' => 200
        );
        return $this->jsonp($res);
    }
    public function detail(){
        $id = input('id');
        $uid = input('uid');

        $wx = $this->db_product->field('weixinurl')->where(array('id'=>$id))->find();
        if (!$wx['weixinurl']) {
            //  生成小程序码
            $qr = new qr();
            $weixin = $qr->getXcxCode($id,'1');
            $weixinUrl['weixinurl'] = $weixin;
            $this->db_product->db_update($id,$weixinUrl);
        }

        $data_pinpai =  $this->db_product->getProductById($id);
        $shoucang =  $this->db_collection->where(array('mid'=>$id,'uid'=>$uid,'kind'=>1))->field('status')->find();
        if ($shoucang) {
            $shoucang = $shoucang['status'];
        }else{
            $shoucang = 0;
        }
        $data_pinpai = $this->db_product->where(array('id'=>$id))->find();
        $data_pinpai['ppimglist'] = json_decode($data_pinpai['ppimglist']);

        $res = array(
            'pinpaixiangqing' => $data_pinpai,
            'shoucang'=>$shoucang,
            'code' => 200
        );
        return $this->jsonp($res);
    }
    //获得访问详情页用户IP地址
    public function getRealIpAddr(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}