<?php
namespace app\store\controller;
//use think\Controller;

class Froms extends Base
{
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\store\model\Froms();
	}

	/**
	 * 品牌信息
	 * @return [type] [description]
	 */
	public function brand()
	{
		$pid = session('api.pid');
		if (!$pid) {
			return json(['s'=>200,'msg'=>'暂无数据']);
		}else{
			$brand = $this->db->brandInfo($pid);
			return json_encode($brand);
		}
	}

	/**
	 * 更新品牌信息
	 * @return [type] [description]
	 */
	public function upBrand()
	{
		$input = input('post.');
		if (!$input['pid']) {
			return json(['s'=>500,'error'=>'非法请求！']);
		}else{
			$banner = $this->db->upBrand($input);
			return json($banner);
		}
	}

	public function upImgList()
	{
		$input = input('post.');
		$upImgList = $this->db->upImgList($input);
		return json($upImgList);
	}

	/**
	 * 品牌简介
	 * @return [type] [description]
	 */
	public function summary()
	{
		$pid = session('api.pid');
		if (!$pid) {
			return json(['s'=>200,'msg'=>'暂无数据']);
		}else{
			$summary = $this->db->summary($pid);
			return json($summary);
		}
	}

	/**
	 * 更新品牌简介
	 * @return [type] [description]
	 */
	public function upSummary()
	{
		$input = input('post.');
		if (!$input['pid']) {
			return json(['s'=>500,'error'=>'非法请求！']);
		}else{
			$upSummary = $this->db->upSummary($input);
			return json($upSummary);
		}
	}
}
?>