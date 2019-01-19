<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/12
 * Time: 14:59
 */

namespace app\api\controller;


use app\lib\WXBizDataCrypt;
use app\model\Advice;
use app\model\ShouCang;
use app\model\Tusu;

class Member extends Api{
    protected $db_member;
    protected $db_tusu;
    protected $db_shoucang;
    protected $db_advice;
    public function _initialize(){
        parent::_initialize();
        $this->db_member = new \app\model\Member();
        $this->db_tusu = new Tusu();
        $this->db_shoucang = new ShouCang();
        $this->db_advice = new Advice();
    }
    /***
     * 用户登录修改登录信息
     */
    public function login(){
        $data = [];
        $data['openid'] = input('openid');
        $data['nickname'] = input('nickname');
        $data['headimg'] = input('headimg');
        $user = $this->db_member->where(array('openid'=>$data['openid']))->find();
        if ($user) {
            if (!empty($data['nickname']) && !empty($data['headimg'])){
                $this->db_member->where(array('openid'=>$data['openid']))->data($data)->update();
            }
            $res = array(
                'status'=>1,
                'code' => 200
            );
            return $this->jsonp($res);
        }
        return $this->jsonp(['status'=>0,'code'=>400]);
    }
    public function getPhoneNumber()
    {
        $appid = Config::get('appid');
        $sessionKey = input('sessionKey');
        $encryptedData= input('encryptedData');
        $iv = input('iv');
        $openid = input('openid');
        $pc = new WXBizDataCrypt($appid, $sessionKey);//注意使用\进行转义
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode == 0) {
            $data = json_decode($data,true);
            $tel_data['tel'] = $data['phoneNumber'];
            $this->db_member->where(array('openid'=>$openid))->save($tel_data);
            $res = array(
                'status'=>1,
                'tel' => $tel_data['tel'],
                'code' => 200
            );
            return $this->jsonp($res);
        } else {
            print($errCode . "\n");
        }
    }
    /***
     * 用户投诉
     */
    public function complaint(){
        $data['uid'] = input('uid');
        $data['pname'] = input('pname');
        $data['content'] = input('content');
        $data['imglist'] = input('upload_picture_list');
        $addid = $this->db_tusu->db_insert($data);
        $res = array(
            'addid' => $addid,
            'code' => 200
        );
        return $this->jsonp($res);
    }
    /***
     * 用户收藏
     */
    //点评列表
    public function shoucang(){
        $mid = input('mid');
        $uid = input('uid');
        $kind = input('kind');

        $update_data['uid'] = $uid;
        $update_data['mid'] = $mid;
        $update_data['kind'] = $kind;
        $update_data['time'] = time();

        $shoucang = $this->db_shoucang->where(array('uid'=>$uid,'mid'=>$mid,'kind'=>$kind))->find();

        if ($shoucang) {
            $status = 1-$shoucang['status'];
            $update_data['status'] = $status;
            $res = $this->db_shoucang->db_update($shoucang['id'],$update_data);;
        } else {
            $status=$update_data['status'] = 1;
            $res = $this->db_shoucang->db_insert($update_data);
        }

        $res = array(
            'status' => $status,
            'code' => 200
        );
        print_r(json_encode($res));
    }
    /***
     * 用户项目咨询
     */
    public function advice(){
        $uid = input('uid');
        $pid = input('pid');
        $message = input('message');
        $city = input('city');
        $budget = input('budget');//投资预算
        $username = input('username');
        $phone = input('phone');
        if (empty($uid) || empty($message) || empty($pid) || empty($budget) || empty($username) || empty($phone)) return $this->err('参数错误');
        $data = [
            'uid' => $uid,
            'pid'=> $pid,
            'message' => $message,
            'city' => $city,
            'budget' => $budget,
            'username' => $username,
            'phone' => $phone,
            'time' => time()
        ];
        $this->db_advice->db_insert($data);
        $res = [
            'msg'=>'ok',
            'code'=>200
        ];
        return $this->jsonp($res);
    }
}