<?php
namespace app\admin\controller;

class Turnimg extends Base{
	protected $db;
	protected function _initialize(){
		parent::_initialize();
		$this->db = new \app\model\Turnimg();
	}

	public function index(){
		$turnimgs = $this->db->db_select();
		foreach ($turnimgs as $key => $value) {
			if (substr($turnimgs[$key]['src'],0,1) == '[') {
				$turnimgs[$key]['src'] = json_decode($value['src']);
			}
		}
		$this->assign('turnimg',$turnimgs);
		$this->assign('title','轮播图');
		return $this->fetch();
	}
	public function turnimg_set(){
		$id = input('id',0);
		if ($id) {
			$this->assign('title','修改帖子');
		}else{
			$this->assign('title','添加帖子');
		}
		$turnimg = $this->db->db_find($id);
		//var_dump($turnimg['src']);die;
		if (substr($turnimg['src'],0,1) == '[') {
			$turnimg['src'] = json_decode($turnimg['src']);
		}
		
		$this->assign('turnimg',$turnimg);
		$this->assign('id',$id);
		return $this->fetch();
	}
	public function turnimg_post(){
		$id = input('id',0);
		$data['src'] = json_encode($_POST['imgs']);
		$data['page'] = input('post.page');
		$data['pagecode'] = input('post.pagecode');
		$data['status'] = input('post.status');
		if ($id>0) {
			$turnimg = $this->db->db_update($id,$data);
			if ($turnimg) {
				return $this->success('修改成功','turnimg/index');
			}else{
				return $this->success('修改失败');
			}
		}else{
			$turnimg = $this->db->db_insert($data);
			if ($turnimg) {
				return $this->success('新增成功','turnimg/index');
			}else{
				return $this->success('新增失败');
			}
		}
	}

	/**
	 * 删除
	 * @return [type] [description]
	 */
	public function delTurnimg(){
		$id = input('id',0);
		if ($id>0) {
			$del = $this->db->db_del($id);
			if ($del) {
				return $this->success('删除成功','turnimg/index');
			}else{
				return $this->success('删除失败');
			}
		}
	}
}
?>