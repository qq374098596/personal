<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Story extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_gushi';

	/**
	 * 全部数据
	 * @param  [type] $table [description]
	 * @return [type]        [description]
	 */
	public function getAll($table)
	{
		$data = Db::name($table)->field('id,title,banner,time,gushi')->select();
		return $data;
	}

	/**
	 * 故事详情页
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function detail($id)
	{
		//故事
		$detail = $this->field('id,title,banner,time,gushi,read_num')->where('id',$id)->find();
		//评论
		$comment = $this->review($detail['id']);
		$detaCom = array(
			'detail' => $detail,
			'comment' => $comment,
		);
		return $detaCom;
	}

	/**
	 * 评论/回复
	 * @return [type] [description]
	 */
	public function review($mid)
	{
		// 第一层评论
		$comment = Db::name('pinglun')->alias('p')->join('fanxiang_user u','p.uid1=u.id')
		->field('u.nickname,u.headimg,p.id,p.uid1,p.gid,p.content,p.time')
		->where('mkind',2)->where("p.mid='".$mid."' AND p.uid2=0")->order('p.time desc')->paginate(2);
		// 循环合并第二层评论
		foreach ($comment as $k => $v) {

			$coments[]['reply'] = Db::name('pinglun')->field('id,uid1,uid2,gid,content,time')->where('mkind',2)->where("mid='".$mid."' AND gid='".$v['id']."'")->select();
			$comment[$k] = array_merge($v,$coments[$k]);

		}
        $items=$comment->items();
		foreach ($items as $k => $v) {
			foreach ($v['reply'] as $key => $value) {
				$uid1[] = $this->user($value['uid1']);
				$uid2[] = $this->user($value['uid2']);
                $items[$k]['reply'][$key]['uid1name'] = $uid1[$key]['nickname'];
                $items[$k]['reply'][$key]['uid2name'] = $uid2[$key]['nickname'];
                $items[$k]['reply'][$key]['time'] = date('Y-m-d',$value['time']);
			}
		}
        foreach ($comment as $k=>$v){
            $comment[$k] = array_merge($v, $items[$k]);
        }
		//var_dump($comment);die;
		return $comment;
	}

	/**
	 * 获取用户信息
	 * @param  [type] $uid [description]
	 * @return [type]      [description]
	 */
	public function user($uid)
	{
		$user = Db::name('user')->field('nickname,headimg')->where('id',$uid)->find();
		return $user;
	}

	/**
	 * 用户评论
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function comment($data)
	{
		
		if ($data['uid1'] == $data['uid2']) {
			return ['status'=>0,'error'=>'不能评论自己！'];
		}else{
			if ($data['content'] == '') {
				return ['status'=>0,'error'=>'请填写评论内容！'];
			}
			$data['mkind'] = 2;
			$data['time'] = time();
			$comment = Db::name('pinglun')->insert($data);
			if($comment){
			    $re=Db::name('user')->where('id',$data['uid1'])->field('headimg')->find();
			    $data['headimg']=$re['headimg'];
			    return $data;
            }			return ['status'=>1,'msg'=>'评论成功！'];
		}
	}
}
?>