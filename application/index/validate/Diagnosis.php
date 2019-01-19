<?php
namespace app\index\validate;
use think\Validate;

class Diagnosis extends Validate
{
	protected $rule = [
		'brand' => 'require|chsAlphaNum',
		'address' => 'require|chsAlphaNum',
		//'contact' => 'require','email','regex:/^1[34578]\d{9}$/',
		// 'contact' => 'email',
		'contact' => 'require|regex:/^1[34578]\d{9}$/',
		
		
	];

	protected $message = [
		'brand.require' => '请填写品牌！',
		'brand.chsAlphaNum' => '品牌只能是汉字、字母和数字！',
		'address.require' => '请选择地址！',
		'contact.require' => '请填写联系方式！',
		//'contact.email' => '邮箱格式不正确！',
		'contact.regex' => '手机格式不正确！',
		'address.chsAlphaNum' => '地址只能是汉字、字母和数字！',
	];
}
?>