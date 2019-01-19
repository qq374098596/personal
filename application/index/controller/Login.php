<?php
namespace App\index\Controller;
use think\Controller;
use think\Loader;

class Login extends Controller
{
	protected $db;
	protected function _initialize(){
		parent::_initialize();
		$this->db = new \app\index\model\Login();
	}
	/**
	 * 用户登录
	 */
	public function login()
	{
		if (session('user')) {
			$this->redirect(url('/'));
		}
		if (request()->isAjax()) {

			$code = $this->db->code($_POST);
			if (!$code) {
				$login = $this->db->login($_POST);
				return $login;
			}else{
				return $code;
			}
		}else{
			return $this->fetch();
		}
		
	}

	// 失去焦点
	public function decideval()
	{
		$keyname = key($_POST);
		$value = array_values($_POST)[0];
		
		if ($value != '') {
			$info = db('user')->where($keyname,$value)->count();
			if (!$info) {
				return ['status'=>1];
			}else{
				if ($keyname == 'nickname') {
					return ['status'=>0,'error'=>'用户名已存在！'];
				}elseif($keyname == 'tel'){
					return ['status'=>0,'error'=>'手机号已存在！'];
				}
			}
		}
		
		
	}

	/**
	 * 用户注册
	 */
	public function register()
	{
		if (session('user')) {
			$this->redirect(url('/'));
		}
		if (request()->isAjax()) {
			$code = $this->db->code($_POST);
			if (!$code) {
				$validate = Loader::validate('Login');
				
				if (!$validate->check($_POST)) {
					return ['status'=>0,'error'=>$validate->getError()];
				}else{
					$register = $this->db->register($_POST);
					return $register;
				}
				
			}else{
				return $code;
			}
		}
		
		return $this->fetch();
	}

	/**
	 * 用户退出
	 */
	public function quit()
	{
		session('user',null);
		$this->redirect(url('/'));
	}
	
}
?>