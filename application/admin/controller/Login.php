<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 11:43
 */

namespace app\admin\controller;
use app\lib\IpLocation;
use app\lib\qr;
use app\lib\Util;
use think\Controller;
use think\Session;

class Login extends Controller{
    private $db_user;
    protected function _initialize(){
        parent::_initialize();
        $this->db_user = new \app\model\User();
    }
    public function index(){
        $this->assign('title','登录');
        return $this->fetch();
    }
    public function login_post(){
        $username = input('post.username');
        $password = input('post.password');
        //$captcha = input('post.captcha');
        $online = input('post.online', 0);
        $data = [
          'username' => $username,
          'password' => $password,
          //'captcha' => $captcha
        ];
        //验证
        $login = new \app\validate\login;
        if(! $login->check($data)){
            return $this->error($login->getError());
        }
        $is_check = $this->is_check($username,$password);
        if (!$is_check) return $this->error('用户名或密码错误!');
        return $this->redirect('/admin.php/index');

    }
    public function logout(){
        Session::delete('uid','admin');
        Session::delete('username','admin');
        return  $this->redirect('/admin.php/login/index');
    }
    private function is_check($username, $password){
        $user = $this->db_user->findByUserName($username);
        if (empty($user)) return false;
        $salt = $user->salt;
        $mdPassword = $user->password;
        if ($mdPassword !== Util::password($password, $salt)) return false;
        //修改登录状态
        $data = [
            'last_login_time' => time(),
            'last_login_ip' => IpLocation::getIP()
        ];
        $db_user = new \app\model\User();
        $db_user->db_update($user->id, $data);
        //保存登录状态
        Session::set('uid',$user->id,'admin');
        Session::set('username',$user->username,'admin');
        return true;
    }

}