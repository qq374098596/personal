<?php
namespace app\index\model;
use think\Model;
use think\Validate;
use think\Db;

class Ucenter extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_user';

	//	count查询数量
	public function getCount($table,$where)
	{
		$count = Db::name($table)->where($where)->count();
		return $count;
	}

	//	求和
	public function getSum($table,$sum,$where)
	{
		$sum = Db::name($table)->field("sum(".$sum.") sum")->where($where)->select();
		return $sum;
	}

	//	根据条件查询全部数据
	public function getAll($table,$field,$where)
	{
		$getAll = Db::name($table)->field($field)->where($where)->select();
		return $getAll;
	}

	/**
	 * 修改密码
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function changePwd($data)
	{
		$rule = [
			'password' => 'require',
			'newpwd' => 'require|alphaDash|length:6,12|different:password',
			'repwd' => 'require|confirm:newpwd',
		];
		$message = [
			'password.require' => '请填写原密码！',
			'newpwd.require' => '请填写新密码！',
			'newpwd.alphaDash' => '密码须设置为字母和数字、下划线‘_’及破折号‘-’',
			'newpwd.length' => '密码长度为6-12，请重新设置！',
			'newpwd.different' => '新密码与原密码不能相同，请重新输入！',
			'repwd.require' => '请确认新密码！',
			'repwd.confirm' => '两次密码输入不一致，请重新输入！',
		];
		$valid = new Validate($rule,$message);
		$resule = $valid->check($data);
		if (!$resule) {
			return ['status'=>0,'error'=>$valid->getError()];
		}else{
			$userInfo = $this->where('id',$data['id'])->where('pwd',md5($data['password']))->find();
			if (!$userInfo) {
				return ['status'=>0,'error'=>'原密码不正确！请重新输入！'];
			}else{
				$changePwd = $this->save([
					'pwd' => md5($data['newpwd']),
				],[
					$this->pk => $data['id'],
				]);
				if ($changePwd) {
					return ['status'=>1,'msg'=>'密码修改成功！'];
				}else{
					return ['status'=>0,'error'=>'密码修改失败！'];
				}
			}
		}
	}

	/**
	 * 更换头像
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function avatar($data)
	{
		$headimg = Db::name('user')->field('headimg')->where('id',$data['id'])->find();
		if ($headimg['headimg'] != "") {
			$img = explode('static',$headimg['headimg']);
			$imgUrl = "../public/static".$img[1];
			unlink($imgUrl);
		}
		$avatar = Db::name('user')->where('id',$data['id'])->update(['headimg'=>$data['headimg']]);
		$sessions = session('user');
		$sessions['headimg'] = $data['headimg'];
		session('user',$sessions);
	}

	public function userInfo($data)
	{
		$rule = [
			'name' => 'chs',
			'email' => 'email',
			'tel' => 'regex:/^1[34578]\d{9}$/',
		];
		$message = [
			'name.chs' => '真实姓名只能是汉字！',
			'email.email' => '邮箱格式不正确！',
			'tel.regex' =>'手机格式不正确！'
		];
		$valid = new Validate($rule,$message);
		if (!$valid->check($data)) {
			return ['status'=>0,'error'=>$valid->getError()];
		}else{
			$userInfo = $this->save([
				'sex' => $data['sex'],
				'name' => $data['name'],
				'email' => $data['email'],
				'tel' => $data['tel'],
				'addr' => $data['addr'],
			],[
				$this->pk => $data['id'],
			]);
			if (!$userInfo) {
				return ['status'=>0,'error'=>'资料更新失败！'];
			}else{
				$sessions = session('user');
				$sessions['sex'] = $data['sex'];
				$sessions['name'] = $data['name'];
				$sessions['email'] = $data['email'];
				$sessions['tel'] = $data['tel'];
				$sessions['addr'] = $data['addr'];
				session('user',$sessions);
				return ['status'=>1,'msg'=>'资料更新成功！'];
			}
		}
	}
}
?>