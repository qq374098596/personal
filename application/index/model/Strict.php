<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Strict extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_pinpai';

	public function common()
	{
		$soure = $this->aliasSql('dianping','d','pinpaiid','p','id','pingfen','sum');
		$count = $this->aliasSql('dianping','d','pinpaiid','p','id','1','count');
		$common = array(
			'soure' => $soure,
			'count' => $count,
		);
		return $common;
	}
	/**
	 * 	Sql语句
	 * @param  [type]
	 * @param  [type]
	 * @param  [type]
	 * @param  [type]
	 * @param  [type]
	 * @param  [type]
	 * @param  string
	 * @return [type]
	 */
	public function aliasSql($table,$alias1,$id1,$alias2,$id2,$field,$order='')
	{
		$fields = "".$order."(".$field.")";
		$where = "".$alias1.".".$id1."=".$alias2.".".$id2."";
		$sql = Db::name($table)->alias($alias1)->field($fields)->where($where)->select(false);
		return $sql;
	}

	/**
	 * 行业数据
	 * @param  [type] $table [description]
	 * @param  [type] $limit [description]
	 * @return [type]        [description]
	 */
	public function trade($table,$limit)
	{
		$tradeAll = Db::name($table)->field('id,hyname')->limit(10)->select();
		return $tradeAll;
	}

	/**
	 * 严选项目数据
	 * @param  [type] $tablep [description]
	 * @param  [type] $tabled [description]
	 * @param  [type] $tableh [description]
	 * @param  string $id     [description]
	 * @return [type]         [description]
	 */
	public function strictAll($tablep,$tabled,$tableh,$id='',$pro='')
	{
		if ($id) {
			$where = "p.id='".$id."'";
		}else{
			$where = "";
		}
		$order='';
		if($pro!=''){
		    if($pro==2){
		        $order='p.time desc';
            }
            if($pro==1){
                $order='count desc';
            }
        }
		$common = $this->common();

		$fields = "p.id,p.name,p.imgsrc,p.tel,p.ppimglist,p.hangyeid,p.jianjie,p.shifang,p.prospect,p.company,p.jiamengfei,p.jiamengshop,p.zhiyingshop,p.bgcolor,h.hyname,(".$common['soure'].") soure,(".$common['count'].") count,round((".$common['soure'].")/(".$common['count'].")) fen";
		$strictall = Db::name($tablep)->alias('p')->join("fanxiang_".$tableh." h","p.kind=2 AND p.hangyeid=h.id")->field($fields)->where($where)->order($order)->select();
		return $strictall;
	}

	public function review($table,$id)
	{
		// 获取点评
		$review = Db::name($table)->alias('dp')->join('fanxiang_user u','dp.uid=u.id')
		->join('fanxiang_pinpai pp','dp.pinpaiid=pp.id')
		->field("u.nickname,u.headimg,pp.company,dp.id,dp.jiamengfei,dp.zongchengben,dp.date,dp.city,dp.yueyingshou,dp.imglist,dp.pingfen,dp.content,dp.time")->where("dp.pinpaiid",$id)
		->where("dp.pinpaiid",$id)->select();
		//获取第一层回复
		foreach ($review as $k => $v) {
			$reply[$k]['reply'] = Db::name('pinglun')->field('id,uid1,time,content')
			->where("mid=".$review[$k]['id']." AND mkind=4")->where("uid2=0")->select();
			$review[$k] = array_merge($v,$reply[$k]);

		}
		
		// 获取第二、三层回复
		foreach ($review as $k => $v) {
			foreach ($review[$k]['reply'] as $key => $value) {
				
				$replyreply[$k]['replyreply'] = Db::name('pinglun')->field('id,uid1,uid2,gid,content,time')->where('mkind',4)->where("mid='".$v['id']."' AND gid='".$value['id']."'")->where('uid2','<>',0)->select();
				$review[$k]['reply'][$key] = array_merge($value,$replyreply[$k]);
				$review[$k]['reply'][$key]['time'] = date('Y-m-d',$value['time']);
				foreach ($review[$k]['reply'][$key]['replyreply'] as $key1 => $value1) {
					$review[$k]['reply'][$key]['replyreply'][$key1]['uid1name'] = $this->user($value1['uid1'])['nickname'];
					if (substr($value1['uid2'],0,3) == '100') {
						$review[$k]['reply'][$key]['replyreply'][$key1]['uid2name'] = '商家';
					}else{
						$review[$k]['reply'][$key]['replyreply'][$key1]['uid2name'] = $this->user($value1['uid2'])['nickname'];
					}
				}
				if (substr($value['uid1'],0,3) == '100') {
					$review[$k]['reply'][$key]['nickname'] = '商家';
				}else{
					$review[$k]['reply'][$key]['nickname'] = $this->user($value['uid1'])['nickname'];
					$review[$k]['reply'][$key]['headimg'] = $this->user($value['uid1'])['headimg'];
				}
			}
		}
		return $review;
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
	 * 好评、中评、差评
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function status($id)
	{
		//差评
		$bad = Db::name('dianping')->where('pinpaiid',$id)->where('pingfen','<=',3)->count();
		//中评
		$average = Db::name('dianping')->where('pinpaiid',$id)->where('pingfen','>',3)->where('pingfen','<=',5)->count();
		//好评
		$good = Db::name('dianping')->where('pinpaiid',$id)->where('pingfen','>',5)->count();
		$isImg = Db::name('dianping')->where('pinpaiid',$id)->where('imglist','not null')->count();
		$status = array(
			'bad' => $bad,
			'average' => $average,
			'good' => $good,
			'isImg' => $isImg,
		);
		return $status;
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
			$data['mkind'] = 4;
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
	 * 问答数据
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function question($id)
	{
		// 查询问题
		$question = Db::name('wenda')->alias('w')->join('fanxiang_user u','w.uid=u.id')->field('w.status,w.id,w.pid,w.uid,u.nickname,w.content,w.gid,w.time')->where('w.pid',$id)->where('w.gid',0)->select();
		foreach ($question as $k => $v) {
			$reply[]['reply'] = Db::name('wenda')->where('pid',$id)->where('gid',$v['id'])->select();
			$question[$k] = array_merge($v,$reply[$k]);
		}
		foreach ($question as $k => $v) {
			foreach ($v['reply'] as $key => $value) {
				$uid[] = $this->user($value['uid']);
				$question[$k]['reply'][$key] = array_merge($value,$uid[$key]);
				$question[$k]['reply'][$key]['time'] = date('Y-m-d',$value['time']);
			}
			$question[$k]['time'] = date('Y-m-d',$v['time']);
		}
		//var_dump($question);die;
		return $question;
	}

	public function statusNum($id)
	{
		// $statusNum = [];
		// for ($i=0; $i <5 ; $i++) { 
		// 	$countNum = Db::name('wenda')->where('pid',$id)->where('gid',0)->where('status',$i+1)->count();
		// 	$statusNum[$i] = $countNum;
		// }
		$syNum = Db::name('wenda')->where('pid',$id)->where('gid',0)->where('status',1)->count();
		$cbNum = Db::name('wenda')->where('pid',$id)->where('gid',0)->where('status',2)->count();
		$kpNum = Db::name('wenda')->where('pid',$id)->where('gid',0)->where('status',3)->count();
		$zdNum = Db::name('wenda')->where('pid',$id)->where('gid',0)->where('status',4)->count();
		$scNum = Db::name('wenda')->where('pid',$id)->where('gid',0)->where('status',5)->count();
		$statusNum = array(
			'syNum' => $syNum,
			'cbNum' => $cbNum,
			'kpNum' => $kpNum,
			'zdNum' => $zdNum,
			'scNum' => $scNum,
		);
		//var_dump($statusNum);die;
		return $statusNum;
	}

	/**
	 * 用户提问
	 * @param [type] $data [description]
	 */
	public function addQuestion($data)
	{
		$question['pid'] = $data['pid'];
		$question['uid'] = $data['uid'];
		$question['gid'] = $data['gid'];
		$question['content'] = $data['content'];
		$question['time'] = time();
		$question['status'] = $data['questionClass'];
		$res = Db::name('wenda')->insert($question);
		if (!$res) {
			return ['status'=>0,'error'=>'提问失败！'];
		}else{
			return ['status'=>1,'msg'=>'提问成功！'];
		}
	}

	/**
	 * 回答提问
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function questionComment($data)
	{
		if ($data['uid'] == $data['uid2']) {
			return ['status'=>0,'error'=>'不能回答自己！'];
		}else{
			if ($data['content'] == '') {
				return ['status'=>0,'error'=>'请填写评论内容！'];
			}
			$question['pid'] = $data['pid'];
			$question['uid'] = $data['uid'];
			$question['gid'] = $data['gid'];
			$question['content'] = $data['content'];
			$question['time'] = time();
			$question['status'] = $data['questionClass'];
			$comment = Db::name('wenda')->insert($question);
			return ['status'=>1,'msg'=>'回答成功！'];
		}
	}
}
?>