<?php
namespace app\store\model;
use think\Model;

class Froms extends Model
{
	protected $pk = 'id';
	protected $table = "fanxiang_pinpai";

	/**
	 * 获取品牌资料
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function brandInfo($id)
	{
		// 品牌信息
		$brandInfo = $this->field('name,hangyeid,company,jiamengfei,tel,time,jiamengshop,zhiyingshop,ppimglist')
		->where('id',$id)->find();
		$industry = db('hangye')->field('id,hyname')->where('id',$brandInfo['hangyeid'])->find();
		$brandInfo['profession'] = $industry['hyname'];
		$brandInfo['time'] = date('Y-m-d',$brandInfo['time']);
		$brandInfo['jiamengfei'] = (int)$brandInfo['jiamengfei'];
		// 轮播图片
		$fileList = json_decode($brandInfo['ppimglist']);
		$data = array(
			'brandInfo' => $brandInfo,
			'fileList' => $fileList,
		);
		
		if ($brandInfo) {
			return ['s'=>200,'data'=>$data];
		}
	}

	/**
	 * 更新资料
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function upBrand($data)
	{
		$up['company'] = $data['company'];
		$up['jiamengfei'] = $data['joinMoney'];
		$up['tel'] = $data['tel'];
		$up['jiamengshop'] = $data['joinShop'];
		$up['zhiyingshop'] = $data['directShop'];
		$upBrand = $this->where('id',$data['pid'])->data($up)->update();
		if (!$upBrand) {
			return ['s'=>500,'error'=>'保存失败！'];
		}else{
			return ['s'=>200,'msg'=>'保存成功'];
		}
	}

	public function upImgList($data)
	{
		$up['ppimglist'] = json_encode($data['imglist']);
		$upImgList = $this->where('id',$data['pid'])->data($up)->update();
		if (!$upImgList) {
			return ['s'=>500,'error'=>'保存失败！'];
		}else{
			return ['s'=>200,'msg'=>'保存成功！'];
		}
	}

	/**
	 * 品牌简介
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function summary($id)
	{
		$summary = $this->field('jianjie')->where('id',$id)->find();
		return ['s'=>200,'summary'=>$summary];
	}

	public function upSummary($data)
	{
		$upS['jianjie'] = $data['content'];
		$upSummary = $this->where('id',$data['pid'])->data($upS)->update();
		if (!$upSummary) {
			return ['s'=>500,'error'=>'保存失败！'];
		}else{
			return ['s'=>200,'msg'=>'保存成功！'];
		}
	}
}
?>