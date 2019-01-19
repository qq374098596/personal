<?php
namespace app\index\model;
use think\Model;
use think\Validate;
use think\Db;

class Index extends Model
{
	protected $pk = "id";
	protected $table = "fanxiang_zhenduan";

	/**
	 * 首页品牌点评
	 * @param  [type]  $tablep [description]
	 * @param  [type]  $tabled [description]
	 * @param  [type]  $tableu [description]
	 * @param  boolean $bool   [description]
	 * @return [type]          [description]
	 */
	public function reviews($tablep,$tabled,$tableu,$bool=false)
	{
		// 获取品牌评分平均值
		$score = $this->aliasSql($tabled,'d','p','id','pinpaiid','','pingfen','sum');
		// 获取品牌的评论条数
		$count = $this->aliasSql($tabled,'d','p','id','pinpaiid','1');
		// 获取品牌的最新评论
		$content = $this->aliasSql($tabled,'d','p','id','pinpaiid','','content');
		// 获取品牌的最新评论图片
		$scoreimg = $this->aliasSql($tabled,'d','p','id','pinpaiid','','imglist');
		// 获取品牌最新评论用户的头像
		$headimg = $this->aliasSql($tableu,'u','d','uid','id','','headimg');
		$uimg = Db::name('dianping')->alias('d')->field("(".$headimg.") headimg")->where('p.id=d.pinpaiid')->limit(1)->select(false);
		// 获取品牌最新评论用户名
		$nickname = $this->aliasSql($tableu,'u','d','uid','id','','nickname');
		$uname = Db::name('dianping')->alias('d')->field("(".$nickname.") nickname")->where('p.id=d.pinpaiid')->limit(1)->select(false);

		$fields = "p.id,p.name,(".$score.") score,(".$count.") count,(".$content.") content,(".$scoreimg.") scoreimg,(".$uimg.") headimg,(".$uname.") nickname";

		// 首页品牌信息
		$reviews = Db::name($tablep)->alias('p')->field($fields)->where("(".$count.")>0")->limit(4)->select();
		foreach ($reviews as $k => $v) {
			$reviews[$k]['scoreimg'] = json_decode($v['scoreimg'])[0];
			$reviews[$k]['roundscore'] = round($v['score']/$v['count']);
		}
		
		return $reviews;
	}

	/**
	 * 数据库单表操作
	 * @param  [type] $table  [description]
	 * @param  [type] $select [description]
	 * @return [type]         [description]
	 */
	public function getAll($table,$select)
	{
		$getAll = Db::name($table)->field($select)->select();

		return $getAll;
	}

	/**
	 * 严选品牌
	 * @param  [type] $table  [description]
	 * @param  [type] $select [description]
	 * @param  [type] $where  [description]
	 * @return [type]         [description]
	 */
	public function strictAll($table,$select,$where)
	{
		$whereAll = Db::name($table)->alias('pp')->join('fanxiang_hangye hy','pp.hangyeid=hy.id')->field($select)->where($where)->select();
		return $whereAll;
	}

	/**
	 * 多表操作获取贴子
	 * @param  [type] $table [description]
	 * @return [type]        [description]
	 */
	public function getCard($table)
	{
		$joinAll = Db::name($table)->alias('t')->join("__USER__ u","t.uid=u.id")->field('t.id,t.title,t.content,t.time,u.headimg,u.nickname')->limit(4)->select();
		return $joinAll;
	}

	/**
	 * [返回SQL语句]
	 * @param  [type]  $tables   [表名]
	 * @param  string  $secali   [从表别名]
	 * @param  string  $priali   [主表别名]
	 * @param  [type]  $pri      [主表主键]
	 * @param  [type]  $sec      [从表索引]
	 * @param  [type]  $variable [字段]
	 * @param  [type]  $order 	 [聚合查询]
	 * @param  boolean $bool     [bool]
	 * @return [type]            [description]
	 */
	public function aliasSql($tables,$secali='',$priali='',$pri,$sec,$count,$variable='',$order='',$bool=false)
	{

		if ($order && $variable) {
			$field = "".$order."(".$secali.".".$variable.")";
		}else if($order=='' && $variable){
			$field = "(".$secali.".".$variable.")";
		}else{
			$field = "count(".$count.")";
		}
		
		$where = "".$priali.".".$pri."=".$secali.".".$sec."";
		$sql = Db::name($tables)->alias($secali)->field($field)->where($where)->limit(1)->select($bool);
		return $sql;
	}

	/**
	 * 用户诊断
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function diagnosis($data)
	{
		if ($data) {
			$diagnosis['pinpai'] = $data['brand'];
			$diagnosis['addr'] = $data['address'];
			$diagnosis['jsfs'] = $data['contact'];
			$diagnosis['time'] = time();
			$diagnosis['uid'] = $data['uid'];
		
			if(!isset($data['pid'])){
			    $diagnosis['pid']=0;
       		}else{
       		    $diagnosis['pid']=$data['pid'];
       		}		
			$rule = [
				'token' => 'token',
			];
			$message = [
				'token.token' => '请不要重复提交！',
			];		
			$valid = new Validate($rule,$message);
			$result = $valid->check($diagnosis);
			
			if (!$valid) {
				return ['valid'=>0,'msg'=>$this->getError()];
			}else{
				$addDiagnosis = $this->insert($diagnosis);
				return ['valid'=>1,'msg'=>'提交成功！'];
			}
		}else{
			return ['valid'=>0,'msg'=>'非法数据请求！'];
		}
	}
}
?>