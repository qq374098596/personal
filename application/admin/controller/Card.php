<?php

namespace app\admin\controller;
use app\common\Upload;

class Card extends Base{
	protected $db;
	protected function _initialize(){
		parent::_initialize();
		$this->db = new \app\model\Card();
	}

	/**
	 * 帖子列表
	 * @return [type] [description]
	 */
	public function index(){
		$find = input('get.q');
		if ($find) {
			$card = $this->db->card($find);
		}else{
			$card = $this->db->card();
		}
		$count = count($card);
		$this->assign('q',$find);
		$this->assign('count',$count);
		$this->assign('card',$card);
		$this->assign('title','帖子管理');
		return $this->fetch();
	}
	public function card_set(){
		$id = input('id',0);
		if ($id) {
			$this->assign('title','修改帖子');
		}else{
			$this->assign('title','添加帖子');
		}
		$card = $this->db->find($id);
		$topic = $this->db->topic();
		$this->assign('topic',$topic);
		$this->assign('card',$card);
		$this->assign('id',$id);
		return $this->fetch();
	}
	public function card_post(){
		$id = input('id',0);
		$data['title'] = input('post.title');
		$data['content'] = input('post.content');
		$data['uid'] = input('post.uid');
		$data['ztid'] = input('post.option');
		$data['time'] = time();
		$data['date'] = date('Y-m-d',time());
		if ($id>0) {
			$card = $this->db->card_up($id,$data);
			if ($card) {
				return $this->success('修改成功','card/index');
			}else{
				return $this->success('修改失败');
			}
		}else{
			$card = $this->db->card_insert($data);
			if ($card) {
				return $this->success('新增成功','card/index');
			}else{
				return $this->success('新增失败');
			}
		}
	}
	public function delCard(){
		$id = input('id',0);
		if ($id>0) {
			$del = $this->db->card_del($id);
			if ($del) {
				return $this->success('删除成功','card/index');
			}else{
				return $this->success('删除失败');
			}
		}
	}

	/**
	 * 主题列表
	 * @return [type] [description]
	 */
	public function topic(){
		$topic = $this->db->topic();
		$count = count($topic);
		$this->assign('count',$count);
		$this->assign('topic',$topic);
		$this->assign('title','主题管理');
		return $this->fetch();
	}
	public function topic_set(){
		$id = input('id',0);
		if ($id) {
			$this->assign('title','修改主题');
		}else{
			$this->assign('title','添加主题');
		}
		$topic = $this->db->topic_find($id);
		$this->assign('topic',$topic);
		$this->assign('id',$id);
		return $this->fetch();
	}
	public function topic_post(){
		$id = input('id',0);
		$data['name'] = input('post.name');
		if ($id>0) {
			$topic = $this->db->topic_up($id,$data);
			if ($topic) {
				return $this->success('修改成功','card/topic');
			}else{
				return $this->success('修改失败');
			}
		}else{
			$topic = $this->db->topic_insert($data);
			if ($topic) {
				return $this->success('新增成功','card/topic');
			}else{
				return $this->success('新增失败');
			}
		}
	}
	public function delTopic(){
		$id = input('id',0);
		if ($id>0) {
			$del = $this->db->topic_del($id);
			if ($del) {
				return $this->success('删除成功','card/topic');
			}else{
				return $this->success('删除失败');
			}
		}
	}

	/**
	 * 排序
	 * @return [type] [description]
	 */
	public function sort(){
		$id = input('id',0);
		$sort = input('post.sort');
		$valid = new \think\Validate([
				'sort' => 'require|number',
			],[
				'sort.require' => '排序不能为空！',
				'sort.number' => '排序必须为数字！',
			]);
		$data['sort'] = $sort;
		if (!$valid->check($data)) {
			return json(['s'=>0,'error'=>$valid->getError()]);
		}else{
			if ($this->db->card_up($id,$data)) {
				return json(['s'=>1,'msg'=>'修改成功！']);
			}else{
				return json(['s'=>0,'error'=>'修改失败！']);
			}
		}
	}

	/**
	 * 点击置顶
	 * @return [type] [description]
	 */
	public function statusClick(){
		$id = input('id',0);
		$zhiding = input('zhiding',0);
		if ($zhiding>0) {
			$data['zhiding'] = 0;
			$this->db->card_up($id,$data);
		}else{
			$data['zhiding'] = 1;
			$this->db->card_up($id,$data);
		}
	}

	/**
	 * 上传文件
	 * @return [type] [description]
	 */
	public function uploads(){
		
		$type = explode('/',$_FILES['file']['type'])[0];
		$file = Request()->file('file');
		$uploads = new Upload();
		if ($type == 'image') {
			$files = $uploads->uploads($file);
		}elseif ($type == 'video') {
			$files = $uploads->videos($file);
		}{

		}
		return $files;
	}
}
?>