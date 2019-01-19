<?php
namespace app\store\model;
use think\Model;

class Login extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_admin';

	public function login($data)
	{
		$adminuser = $this->where('member',$data['username'])->where('password',md5($data['password']))->find();
		if (!$adminuser) {
			return ['s'=>500,'error'=>'管理员账号/密码有误！'];
		}else{
			if ($adminuser['pid'] == 0) {
				return ['s'=>500,'error'=>'不是品牌管理员！请联系后台！'];
			}else{
				$time = time();
				$this->where('id',$adminuser['id'])->update(['lastTime'=>$time]);
				$adminuser['time'] = date('Y-m-d H:i:s',$time);
				if ($adminuser['lastTime']) {
					$adminuser['lastTime'] = date('Y-m-d H:i:s',$adminuser['lastTime']);
				}
				$token = request()->token('__token__', 'sha1');
				session('api',array(
						'token' => $token,
						'id' => $adminuser['id'],
						'name' => $adminuser['member'],
						'pid' => $adminuser['pid'],
						'time' => $adminuser['time'],
						'lastTime' => $adminuser['lastTime'],
				));
				cookie('api',array(
						'token' => $token,
						'id' => $adminuser['id'],
						'name' => $adminuser['member'],
						'pid' => $adminuser['pid'],
						'time' => $adminuser['time'],
						'lastTime' => $adminuser['lastTime'],
				));
				action_log('user_login','api',session('api.id'),session('api.id'));
				return ['s'=>200,'msg'=>'登录成功！'];
			}
		}
	} 
}
?>