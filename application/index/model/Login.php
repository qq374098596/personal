<?php
namespace app\index\model;
use think\Model;
use think\Validate;
use think\Db;

class Login extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_user';

	public function code($data)
	{
		if(!captcha_check($data['code'])){
			return ['status'=>0,'error'=>'验证码错误！'];
		}
	}

	/**
	 * 登录
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function login($data)
	{
		$userinfo = Db::name('user')->where("nickname='".$data['username']."' OR tel='".$data['username']."'")->where('pwd',md5($data['password']))->find();
		//var_dump($userinfo);die;
		if (!$userinfo) {
			return ['status'=>0,'error'=>'用户名或密码不正确!'];
		}

		session('user',array(
			'id' => $userinfo['id'],
			'name' => $userinfo['name'],
			'nickname' => $userinfo['nickname'],
			'headimg'=>$userinfo['headimg'],
			'tel' => $userinfo['tel'],
			'email' => $userinfo['email'],
			'time' => date('Y-m-d H:i:s',$userinfo['time']),
			'sex' => $userinfo['sex'],
			'addr' => $userinfo['addr'],
		));
		return ['msg'=>'登录成功!'];
	}

	/**
	 * 注册
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function register($data)
	{
		$user["nickname"] = $data['username'];
		$user['tel'] = $data['phone'];
		$user['pwd'] = md5($data['password']);
		$user['time'] = time();
		
			
		$valid = $this->Validate([
			'__token__' => 'token',
		],[
			'__token__.token' =>'请不要重复提交！'
		])->allowfield(true)->save($user);
		//var_dump($valid);die;
		//$valid = Db::execute("insert into fanxiang_user (`nickname`,`tel`,`pwd`,`time`) VALUES ('".$user['nickname']."' , '".$user['tel']."' , '".$user['pwd']."' , ".$user['time'].")");
		
		if (!$valid) {
			return ['status'=>0,'error'=>$this->getError()];
		}else{
			$this->login($data);
			return ['msg'=>'注册成功！'];
			
		}
	}
}

?>