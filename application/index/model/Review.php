<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Review extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_dianping';

	// 公共数据部分
	public function common($table='',$id='',$hyid='')
	{
		$soure = $this->aliasSql('dianping','d','pinpaiid','p','id','pingfen','sum');
		$count = $this->aliasSql('dianping','d','pinpaiid','p','id','1','count');
		$reviews = array();
		if ($table) {
			$fields = "p.id,p.order_id,p.name,p.imgsrc,p.bgcolor,(".$count.") count,(".$soure.") soure";
			if ($id) {
				$where = "p.hangyeid='".$hyid."' AND id<>'".$id."'";
			}else{

				$where = "p.hangyeid='".$hyid."'";
			}
			$reviews = Db::name($table)->alias('p')->field($fields)->where($where)->where('kind',1)->select();
		}
		$common = array(
			'soure' => $soure,
			'count' => $count,
			'reviews' => $reviews,
		);
		return $common;
	}
	/**
	 * 	Sql语句
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
	 * 加盟点评首页数据
	 * @param  [type] $table [description]
	 * @return [type]        [description]
	 */
	public function jsonAll($table)
	{
		$data = Db::name($table)->field('id,hyname,bgcolor')->select();
		return $data;
	}
	public function reviewJson($table,$where='')
	{
	    if($where!='')
        {
            $where=['id'=>$where];
        }
		$industry = Db::name('hangye')->where($where)->field('id,hyname,bgcolor')->select();
		foreach ($industry as $k => $v) {
			$common = $this->common($table,'',$v['id']);
			$reviews[$k]['review'] = $common['reviews'];
			$industry[$k] = array_merge($v,$reviews[$k]);
            if(!$reviews[$k]['review']){
                unset($industry[$k]);
            }
		}
		return $industry;
	}

    public function reviewHot($table='')
    {
        $soure = $this->aliasSql('dianping','d','pinpaiid','p','id','pingfen','sum');
        $count = $this->aliasSql('dianping','d','pinpaiid','p','id','1','count');
        $reviews = array();
        if ($table) {
            $fields = "p.id,p.order_id,p.name,p.imgsrc,(".$count.") count,(".$soure.") soure";
            $reviews = Db::name($table)->alias('p')->field($fields)->where('order_id',1)->select();
        }
        return $reviews;
    }

	/**
	 * 	详情页数据
	 * @param  [type]
	 * @param  string
	 * @return [type]
	 */
	public function detail($table,$id='')
	{
		$fields = "p.id,p.name,p.imgsrc,p.ppimglist,p.hangyeid,p.jiamengfei,h.hyname";
		$detail = Db::name($table)->alias('p')->join('fanxiang_hangye h','p.hangyeid=h.id')->field($fields)->where("p.id",$id)->find();
		// 用户回复数
		$reply = Db::name('pinglun')->field('count(1) replyCount')->where('mkind',1)->where('mid','IN',function($query) use ($id){
			$query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
				$query->table('fanxiang_pinpai')->field('id')->where('id',$id);
			});
		})->find();
		// 点评评分平均值
		$score = Db::name('dianping')->field('sum(pingfen) score')->where('pinpaiid','IN',function($query) use ($id){
			$query->table('fanxiang_pinpai')->field('id')->where('id',$id);
		})->find();
		// 点评数
		$review = Db::name('dianping')->field('count(1) reviewCount')->where('pinpaiid','IN',function($query) use ($id){
			$query->table('fanxiang_pinpai')->field('id')->where('id',$id);
		})->find();
		
		$detail['replyCount'] = $reply['replyCount'];
		$detail['score'] = $score['score'];
		$detail['reviewCount'] = $review['reviewCount'];
		return $detail;
	}

	/**
	 * 	最新点评
	 */
	public function newReview($table,$id)
	{
		$newReview = Db::query("select id,pinpaiid,jiamengfei,zongchengben,yueyingshou,date,pingfen from (select * from fanxiang_dianping WHERE pinpaiid IN (SELECT id FROM fanxiang_pinpai WHERE kind=1) order by time desc) dptime group by pinpaiid order by time desc limit 4");
		foreach ($newReview as $k => $v) {
			$brand[] = Db::name('pinpai')->field('id,name,imgsrc')->where('id',$v['pinpaiid'])->find();
			$newReview[$k] = array_merge($v,$brand[$k]);
		}
		return $newReview;
	}

	/**
	 * 推荐品牌
	 * @return [type] [description]
	 */
	public function recReview($table,$id,$hyid)
	{
		$recBrand = Db::name($table)->field('id,name,imgsrc')->where('order_id',1)->where('hangyeid',$hyid)->where('id','<>',$id)->select();
		foreach ($recBrand as $k => $v) {
			// 点评评分平均值
			$score[] = Db::name('dianping')->field('sum(pingfen) score')->where('pinpaiid','IN',function($query) use ($v){
				$query->table('fanxiang_pinpai')->field('id')->where('id',$v['id']);
			})->find();
			// 用户回复数
			$reply[] = Db::name('pinglun')->field('count(1) replyCount')->where('mkind',1)->where('mid','IN',function($query) use ($v){
				$query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($v){
					$query->table('fanxiang_pinpai')->field('id')->where('id',$v['id']);
				});
			})->find();
			// 点评数
			$review[] = Db::name('dianping')->field('count(1) reviewCount')->where('pinpaiid','IN',function($query) use ($v){
				$query->table('fanxiang_pinpai')->field('id')->where('id',$v['id']);
			})->find();
			$recBrand[$k] = array_merge($v,$score[$k],$reply[$k],$review[$k]);
		}
		return $recBrand;
	}

	/**
	 * 用户点评信息
	 * @param  [type] $table [description]
	 * @param  [type] $id    [description]
	 * @return [type]        [description]
	 */
	public function review($table,$id,$where='')
	{
		// 点评信息 第一层
		$review = Db::name('dianping')->alias('dp')->join('fanxiang_user u','dp.uid=u.id')
		->join("fanxiang_pinpai pp","dp.pinpaiid=pp.id")
		->field('u.nickname,u.headimg,pp.company,dp.id,dp.jiamengfei,dp.zongchengben,dp.date,dp.city,dp.yueyingshou,dp.imglist,dp.pingfen,dp.content,dp.time')
		->where('dp.pinpaiid',$id)->where($where)->paginate(5);
		
		//网友回复
		//获取第二层回复
		foreach ($review as $k => $v) {
			$reply[$k]['reply'] = Db::name('pinglun')->alias('pl')->join('fanxiang_user u','pl.uid1=u.id')
			->field('u.id,u.headimg,u.nickname,pl.id,pl.uid1,pl.time,pl.content')
			->where("mid=".$review[$k]['id']." AND mkind=1")->where("uid2=0")->select();
			$review[$k] = array_merge($v,$reply[$k]);
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
			$data['mkind'] = 1;
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
	 * 写点评
	 * @param [type] $review [description]
	 */
	public function addReview($review)
	{
		if ($review == '') {
			$brandId = Db::name('pinpai')->where('name',$review['reviewname'])->field('id')->find();
			if (!$brandId) {
				$brand = array(
					'name' => $review['reviewname'],
					'hangyeid' => $review['trade'],
					'imgsrc' => $review['logo'],
					'kind' => 1,
                    'company'=>$review['company'],
                    'jiamengfei'=>$review['number']
				);

				$addBrand = Db::name('pinpai')->insert($brand);

				if ($addBrand) {
					$brandId = Db::name('pinpai')->where('name',$review['reviewname'])->field('id')->find();
				}	
			}
			$review['id'] = $brandId['id'];
		}
		if (!empty($review['id'])) {
			$data = array(
				'jiamengfei' => $review['number'],
				'zongchengben' => $review['cost'],
				'date' => $review['date'],
				'city' => $review['target'],
				'yueyingshou' => $review['revenue'],
				'imglist' => json_encode(explode(',',$review['material'])),
				'pingfen' => $review['starlevel'],
				'content' => $review['content'],
				'pinpaiid' => $review['id'],
				'pinpainame' => $review['reviewname'],
				'uid' => $review['uid'],
				'time' => time(),
				'status' =>$review['radio'],
			);
		}else{
			return ['status'=>0,'error'=>'非法数据请求！'];
		}
		if(strlen($data['imglist'])<=5){
            $data['imglist']=null;
        }
		$addReview = Db::name('dianping')->insert($data);
		if (!$addReview) {
			return ['status'=>0,'error'=>'添加失败！'];
		}else{
			return ['status'=>1,'msg'=>'添加成功！'];
		}
	}
}
?>