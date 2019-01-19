<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Tutor extends Model
{
	protected $pk = 'id';
	protected $table = 'fanxiang_daoshi';

	public function getAll($tabled,$tableh,$search='')
	{
		if ($tabled) {
			$getAll = Db::name($tabled)->alias('d')
			->join("fanxiang_".$tableh." h","d.hangyeid=h.id")
            ->where('d.name', 'like','%'.$search.'%')
			->field('d.id,d.headimg,d.name,d.old,d.zizhi,d.are,d.xiangmu,d.jieshao,h.hyname')
			->select();
		}else{
			$getAll = Db::name($tableh)->field('id,hyname')->limit(8)->select();
		}
		
		return $getAll;
	}

	public function detailJson($id)
	{

		$detailJson = Db::name('daoshi')->alias('d')->join('fanxiang_hangye h',"d.hangyeid=h.id")
		->field('d.id,d.headimg,d.name,d.are,d.old,d.zizhi,d.xiangmu,d.imglist,d.jieshao,d.xiangmuxiangqing,d.licheng,d.qrcodeurl,d.hangyeid,h.hyname')
		->where('d.id='.$id)->select();
		return $detailJson;
	}

	public function similar($id)
	{
		$tradeid = $this->detailJson($id);
		$similars = Db::name('daoshi')->alias('d')->join('fanxiang_hangye h',"d.hangyeid=h.id")
		->field("d.id,d.headimg,d.name,h.hyname,d.are,d.old,d.zizhi,d.xiangmu")
		->where("d.hangyeid=".$tradeid[0]['hangyeid']." AND d.id<>".$id)->select();
		return $similars;
	}
}
?>