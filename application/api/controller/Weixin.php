<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 10:17 AM
 */

namespace app\api\controller;


use app\lib\Util;
use app\model\Member;
use think\Config;

class Weixin extends Api{
    protected $db_member;
    public function _initialize(){
        parent::_initialize();
        $this->db_member = new Member();
    }
    public function getopenid(){
        $JSCODE = $_GET['code'];
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.Config::get('appid').'&secret='.Config::get('appsecret').'&js_code='.$JSCODE.'&grant_type=authorization_code';
        $rs = Util::curlGet($url);
        $rs = json_decode($rs,true);
        $openid = isset($rs['openid']) ? $rs['openid'] : '';

        if ($openid) {
            $userinfo = $this->db_member->where(array('openid'=>$openid))->find();

            if (!$userinfo) {
                $this->db_member->save(array('openid'=>$openid));
                $userinfo =$this->db_member->where(array('openid'=>$openid))->find();
            }

            $res = array(
                'status'=>1,
                'data'=>$userinfo,
                'openid'=>$openid,
                'session_key'=>$rs['session_key']
            );
           return $this->jsonp($res);
        }else{
            $res = array(
                'status'=>0,
                'code' => 400
            );
            return $this->jsonp($res);
        }
    }
}