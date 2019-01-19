<?php
namespace app\index\validate;
use think\Validate;

class Login extends Validate
{
	protected $rule = [
		'username' =>'require|chsAlphaNum',
		'phone' => 'require|regex:/^1[34578]\d{9}$/',
		'password' => 'require|alphaDash|length:6,12',
		'repassword' =>'require|confirm:password'
	];

	protected $message = [
		'username.require' => '请填写用户名！',
		'username.chsAlphaNum' => '用户名只能是汉字、字母和数字！',
			//'username.length' => '用户名长度为4-10，请重新设置！',
		'phone.require' => '请填写手机号！',
		'phone.regex' => '手机号码格式不正确，请重新输入！',
		'password.require' => '请设置密码！',
		'password.alphaDash' => '密码须设置为字母和数字、下划线‘_’及破折号‘-’',
		'password.length' => '密码长度为6-12，请重新设置！',
		'repassword.require' => '请再次输入密码！',
		'repassword.confirm' => '两次密码输入不一致，请重新输入！',
		'__token__.token' =>'请不要重复提交！'
	];
}
?>