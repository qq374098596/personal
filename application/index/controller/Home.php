<?php
namespace Home\Controller;
//use Think\Controller;

class HomeController extends CommonController{

	/**
	 *	获取首页公共数据
	 */
	public function __construct(){
		parent::__construct();

		//获取加盟点评数据
		$review = M('dianping')->field('pinpainame,content,pingfen,imglist')->limit(4)->select();
		$this->assign('_review',$review);
		//获取导师数据
		$tutor = M('daoshi')->field('headimg,name,old,are,zizhi,xiangmu,xiangmuxiangqing,imglist')->select();
		$this->assign('_tutor',$tutor);
		//获取帖子数据
		$card = M('tiezi')
		->table('__TIEZI__ as t')->join('__USER__ as u')
		->field('t.title,t.content,t.time,u.headimg,u.nickname')->limit(4)->select();
		//var_dump($card);die;
		$this->assign('_card',$card);	

	}

	/**
	 * VUE json数据
	 * 获取严选项目数据
	 * @return [type] [description]
	 */
	public function index(){
		$strict = M('yanxuanpinpai')->field('imgsrc')->limit(10)->select();
		$data = ['yxxm'=>$strict];
		$json = $this->json($data);
		echo $json;
	}
}
?>