<?php
namespace app\model;
use think\Model;
use think\Db;

class Card extends Model{
	protected $pk = 'id';
	protected $table = 'fanxiang_tiezi';

	public function card($find=''){
		$where = "";
		if ($find) {
			$where = "title like '%".$find."%'";
		}
		$card = $this->field('id,title,uid,time,ztid,zhiding,sort')->where($where)->order('time desc')->paginate(15);
		foreach ($card as $k => $v) {
			$card[$k]['zhuti'] = Db::table('fanxiang_zhuti')->field('name')->where('id',$v['ztid'])->find();
			$card[$k]['nickname'] = Db::table('fanxiang_user')->field('nickname')->where('id',$v['uid'])->find();
			$card[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
		}
		return $card;
	}
	public function find($id){
		$card = $this->field('title,content,uid,ztid,sort')->where('id',$id)->find();
		if ($card) {
			$nickname = Db::table('fanxiang_user')->field('nickname')->where('id',$card['uid'])->find();
			$card['nickname'] = $nickname['nickname'];
		}
		return $card;
	}
	public function card_up($id,$data){
		$card = $this->where('id',$id)->data($data)->update();
		return $card;
	}
	public function card_insert($data){

		$card = $this->save($data);
		return $card;
	}
	public function card_del($id){
		$card = $this->where('id',$id)->delete();
		return $card;
	}

	public function topic(){
		$topic = Db::table('fanxiang_zhuti')->select();
		return $topic;
	}

	public function topic_find($id){
		$topic = Db::table('fanxiang_zhuti')->where('id',$id)->find();
		return $topic;
	}
	public function topic_up($id,$data){
		$topic = Db::table('fanxiang_zhuti')->where('id',$id)->data($data)->update();
		return $topic;
	}
	public function topic_insert($data){
		$topic = Db::table('fanxiang_zhuti')->data($data)->insert();
		return $topic;
	}
	public function topic_del($id){
		$card = Db::name('zhuti')->where('id',$id)->delete();
		return $card;
	}
}
?>