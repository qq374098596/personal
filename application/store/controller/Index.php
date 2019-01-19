<?php
namespace app\store\controller;
//use think\Controller;

class Index extends Base
{
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\store\model\Index();
	}

	public function index()
	{

		$pid = session('api.pid');
		//var_dump(!$pid);die;
		if (!$pid) {
			return json(['s'=>200,'msg'=>'暂无数据']);
		}else{
			$res = $this->db->index($pid);
			return json($res);
		}
	}
}

?>