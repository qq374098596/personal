<?php
namespace app\admin\controller;
use think\Controller;

class Upload extends Controller
{
	public function uploads()
	{
		$server = explode('/',$_SERVER['PHP_SELF']);
		//var_dump($_FILES);die;
		$file = request()->file('file');
		if ($file->isValid()) {
			$appPath = 'static/Uploads/';
			$info = $file->move(ROOT_PATH . 'public' . DS . $appPath);
			if ($info) {
				if (!is_numeric(substr($_SERVER['HTTP_HOST'],0,1))) {
					$ishttp = $_SERVER['HTTP_HOST'] . '/';
				}else{
					$ishttp = $_SERVER['HTTP_HOST'] . '/' . $server[1] . '/public/';
				}
				$data = array(
					'errno' => 0,
					'data' => ['http://' . $ishttp . $appPath . $info->getSaveName()],
				);
				echo json_encode($data);
				//return ['status'=>'0','imgsrc'=>'http://' . $ishttp . $appPath . $info->getSaveName()];
			}
		}
	}

	public function videos()
	{
		$server = explode('/', $_SERVER['PHP_SELF']);
		$file = request()->file('file');
		if ($file->isValid()) {
			$appPath = 'static/Videos/';
			$info = $file->move(ROOT_PATH . 'public' . DS .$appPath);
			if ($info) {
				if (!is_numeric(substr($_SERVER['HTTP_HOST'],0,1))) {
					$ishttp = $_SERVER['HTTP_HOST'] . '/';
				}else{
					$ishttp = $_SERVER['HTTP_HOST'] . '/' .$server[1] . '/public/';
				}
				$data = array(
					'errno' => 0,
					'data' => ['http://' . $ishttp . $appPath . $info->getSaveName()],
				);
				echo json_encode($data);
			}
		}
	}
}
?>