<?php
namespace app\index\controller;
use think\Controller;
class Common extends Controller
{
	/**
	 * 获取JSON数据
	 */
	public function json($data)
	{
		return json_encode($data,JSON_UNESCAPED_UNICODE); 
	}

	/**
	 * 删除图片文件
	 * @return [type] [description]
	 */
	public function delImg()
	{
		$img = explode('static',$_POST['imgUrl']);
		$imgUrl = "../public/static".$img[1];
		unlink($imgUrl);
	}

}
?>