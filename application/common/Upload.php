<?php
namespace app\common;
use think\Controller;

class Upload extends Controller
{
	public function uploads($file)
	{
		$server = explode('/',$_SERVER['PHP_SELF']);
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
				return json_encode($data);
			}
		}
	}

	public function videos($file)
	{
		$server = explode('/', $_SERVER['PHP_SELF']);
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
				return json_encode($data);
			}
		}
	}
}
?>