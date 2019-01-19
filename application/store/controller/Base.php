<?php
namespace app\store\controller;
use think\Controller;

class Base extends Controller
{
	public function islogin()
	{
		$input = request()->post('');
		if (!$input) {
			return json(['s'=>500,'error'=>'请登录！']);
		}else{
			if ($input['token'] != session('api.token')) {
				return json(['s'=>500,'error'=>'非法请求，请重新登录！']);
			}else{
				return json(['s'=>200,'msg'=>'欢迎您！']);
			}
		}
	}
}
?>