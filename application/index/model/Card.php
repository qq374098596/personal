<?php
namespace app\index\model;
use think\Model;
use think\Validate;
use think\Db;

class Card extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_tiezi';

	/**
	 *	签到功能
	 */
	public function sign($uid)
	{
		$data['uid'] = $uid;
		$data['jifen'] = 10;
		$data['time'] =time();
		$sign = Db::name('jifen')->insert($data);
		if (!$sign) {
			return ['status'=>0,'error'=>'签到失败！'];
		}else{
			return ['msg'=>'签到成功！'];
		}
	}

	/**
	 * 帖子数量
	 * @return [type]
	 */
	public function cardNum($table)
	{
		$Nowadays = strtotime(date('Y-m-d'),time());
		$yestdayNum = strtotime(date('Y-m-d',strtotime("-1 day")));
		// 获取贴子总数
		$countNum = Db::name($table)->count();
		// 获取今天贴子
		$nowNum = Db::name($table)->where('time>'.$Nowadays)->count();
		// 获取昨天贴子
		$yestdayNum = Db::name($table)->where('time>'.$yestdayNum.' AND time<'.$Nowadays)->count();
		$count = array(
			'countNum' => $countNum,
			'nowNum' => $nowNum,
			'yestdayNum' => $yestdayNum,
		);
		//var_dump($count);die;
		return $count;
	}

	/**
	 * 帖子回复数排行
	 * @return [type] [description]
	 */
	public function cardOld()
	{
		//回复数量
		$comment = Db::name('pinglun')->alias('p')->where("p.mid=t.id AND p.mkind=3 AND p.gid=0")->fetchSql(true)->count();
		//点赞数量
		$laud = Db::name('dianzan')->alias('dz')->where("dz.mid=t.id AND dz.kind=2 AND dz.status=1")->fetchSql(true)->count();
		$cardOld = Db::name('tiezi')->alias('t')->field("t.id,t.title,t.read_num,(".$comment.") comment,(".$laud.") laud")->order("comment desc")->limit(9)->select();
		return $cardOld;
	}

	/**
	 * 获取数据
	 * @param  [type] $table  [description]
	 * @param  array  $select [description]
	 * @param  [type] $limit  [description]
	 * @param  string $id     [description]
	 * @return [type]         [description]
	 */
	public function getAll($table,$select=array(),$limit,$id='')
	{
		// id是否存在，如果存在说明是详情页
		if (!$id) {
			$getAll = Db::name($table)->field($select)->limit($limit)->select();
		}else{
			$ugetAll = Db::name($table)->field($select)->limit($limit)->where('id='.$id)->select();
			$uid = $ugetAll[0]['uid'];
			$user = Db::name('user')->field('headimg,nickname')->where('id='.$uid)->select();
			$commentCount = Db::name('pinglun')->where('mid='.$id.' AND uid2=0 AND mkind=3')->count();

			$getAll = array();
			foreach ($ugetAll as $k => $v) {
				$getAll[] = array_merge($v,$user[$k]);
				$getAll[$k]['commentCount'] = $commentCount;
			}
		}
		return $getAll;
	}

	public function userAll($table)
	{
		$cardNum = Db::name('tiezi')->alias('t')->field('count(1)')->where('t.uid=u.id')->select(false);
		$userAll = Db::name($table)->alias('u')->field("u.id,u.nickname,u.headimg,(".$cardNum.") commentNum")->order('commentNum desc')->limit(10)->select();
		return $userAll;
	}

	public function joinAll($table,$table1,$limit,$id='')
	{
		if ($id) {
			$where = array('t.uid=u.id AND t.ztid='.$id);
		}else{
			$where = array('t.uid=u.id');
		}
		
		$joinAll = Db::name($table)->alias('t')
		->join("fanxiang_".$table1." u",$where)
		->field('t.id,t.uid,t.title,t.time,t.read_num,u.headimg,u.nickname')->order('time desc')->limit($limit)->select();
		foreach ($joinAll as $k => $v) {
			$commentCount[]['count'] = Db::name('pinglun')->where('mid='.$v['id'].' AND uid2=0 AND mkind=3')->count();
			$joinAll[$k] = array_merge($v,$commentCount[$k]);
		}
		return $joinAll;
	}

	/**
	 *	用户评论信息和用户回复信息显示
	 *
	 */
	public function cardComment($id,$uid2=0,$options='')
	{
		// 获取第一层评论信息
		$uidSql = Db::name('user')->alias('u')->field('u.nickname')->where('u.id=p.uid1')->select(false);
		$uidImgSql = Db::name('user')->alias('u')->field('u.headimg')->where('u.id=p.uid1')->select(false);
		if($options){
            $comment = Db::name('pinglun')->alias('p')->field("p.id,p.content,p.time,p.uid1,(".$uidSql.") uidname,(".$uidImgSql.") uidimg")->where('mid='.$id.' AND mkind=3 AND uid2='.$uid2)->order('id desc')->paginate(5,false,$options);
        }else{
            $comment = Db::name('pinglun')->alias('p')->field("p.id,p.content,p.time,p.uid1,(".$uidSql.") uidname,(".$uidImgSql.") uidimg")->where('mid='.$id.' AND mkind=3 AND uid2='.$uid2)->order('id desc')->paginate(5);
        }
		if (empty($comment)) {
			return array();
		}

		foreach ($comment as $k => $v) {
			// 根据评论目标id获取用户信息以及评论信息
			$uid1Sql = Db::name('user')->alias('u')->field('u.nickname')->where('u.id=p.uid1')->select(false);
			$uid1idSql = Db::name('user')->alias('u')->field('u.id')->where('u.id=p.uid1')->select(false);
			$uid2Sql = Db::name('user')->alias('u')->field('u.nickname')->where('u.id=p.uid2')->select(false);
			$uid2idSql = Db::name('user')->alias('u')->field('u.id')->where('u.id=p.uid2')->select(false);
			$comments[$k]['child'] = Db::name('pinglun')->alias('p')->field("p.id,p.content,p.time,(".$uid1idSql.") uid1,(".$uid1Sql.") uid1name,(".$uid2idSql.") uid2,(".$uid2Sql.") uid2name")->where('gid='.$v['id'])->select();
			// 合并第一层和回复评论信息
			$comment[$k] = array_merge($v,$comments[$k]);
		}
		return $comment;	
	}

	/**
	 *	评论回复功能
	 */
	public function comment($data)
	{
		if ($data['uid1'] == $data['uid2']) {
			return ['status'=>0,'error'=>'不能评论自己！'];
		}else{
			if ($data['content'] == '') {
				return ['status'=>0,'error'=>'请填写评论内容！'];
			}
			$data['mkind'] = 3;
			$data['time'] = time();
			$comment = Db::name('pinglun')->insert($data);
			if($comment){
			    $user=Db::name('user')->where('id',$data['uid1'])->field('headimg')->find();
			    $data['headimg']=$user['headimg'];
            }
			return $data;
			//return ['status'=>1,'msg'=>'评论成功！'];
		}
		
	}

	/**
	 * 添加帖子
	 * @param [type] $data [description]
	 */
	public function addCard($data)
	{

		$data['ztid'] = $data['topic'];
		$data['time'] = time();
		$data['date']=date('Y-m-d',time());

		$valid = $this->Validate([
			'title' => 'require',
		],[
			'title.require' => '请填写标题内容！',
		])->allowfield(true)->save($data);
		if (!$valid) {
			return ['valid'=>0,'error'=>$this->getError()];
		}else{
			return ['valid'=>1,'msg'=>'发布成功！'];
		}
	}
}
?>