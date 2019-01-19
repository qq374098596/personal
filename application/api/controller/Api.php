<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/7
 * Time: 3:18 PM
 */

namespace app\api\controller;


use app\lib\Util;
use think\Controller;

class Api extends Controller{
    public  $uid;
    public $member;
    public $token;
    public function _initialize(){
        parent::_initialize();
        @define('TIMESTAMP', time());
        @define('DATETIME', date('Y-m-d H:i:s', TIMESTAMP));
        $this->token = intval('token');
        //$data = ['u'=> 1, 'p' => 'api', 't' => TIMESTAMP]; //加密格式
        if (!empty($this->token)){
            $this->initData();
        }
    }
    public function initData(){
        $data = $this->token = Util::up_decode($this->token);
        $this->uid = $data['uid'];
    }
    public function jsonp($data){
        return json($data);
    }
    public function err($msg){
        return $this->jsonp(['code'=>404, 'msg'=>$msg]);
    }
    public function is_login(){
        return $this->jsonp(['code'=>10001, 'msg'=>'用户没有登陆，请先登录']);
    }
    public function check_login(){
        if (empty($this->uid)) return $this->is_login();
    }
    public function getTree($arr)
    {
        $arr2 = array();
        foreach ($arr as $key => $value) {
            if (isset($value['id']))  $arr2[$value['id']] = $value;
        }
        $tree = array();
        foreach ($arr2 as $k => $item){
            if (isset($arr2[$item['gid']])){
                $arr2[$item['gid']]['son'][] = &$arr2[$item['id']];
            }else if($item['gid']==0){
                $tree[] = &$arr2[$item['id']];
            }
        }
        return $tree;
    }
}