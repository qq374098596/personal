<?php
namespace app\store\model;
use think\Model;

class Index extends Model
{
	public function index($id)
	{
		// 最近七天
		$chartData1 = array();
		for ($i=1; $i < 7; $i++) {
			$yestdayNum = strtotime(date('Y-m-d',strtotime("-".$i." day")));
			$yestdayNumTo = $yestdayNum+86400;
			$day = date('d/m',$yestdayNum);
			$where = "'".$yestdayNum."'<time AND time<'".$yestdayNumTo."'";
			$chartData1[$i-1]['day'] = $day;
			$chartData1[$i-1]['access'] = db('access')->where('pid',$id)->where($where)->count();
			$chartData1[$i-1]['advice'] = db('advice')->where('pid',$id)->where($where)->count();
		}

		$chartData2 = array();
		for ($i=1; $i < 30; $i++) {
			$yestdayNum = strtotime(date('Y-m-d',strtotime("-".$i." day")));
			$yestdayNumTo = $yestdayNum+86400;
			$day = date('d日',$yestdayNum);
			$where = "'".$yestdayNum."'<time AND time<'".$yestdayNumTo."'";
			$chartData2[$i-1]['day'] = $day;
			$chartData2[$i-1]['access'] = db('access')->where('pid',$id)->where($where)->count();
			$chartData2[$i-1]['advice'] = db('advice')->where('pid',$id)->where($where)->count();
		}
		$char = array(
			'chartData1' => $chartData1,
			'chartData2' => $chartData2,
		);
		//var_dump($chartData1);die;
		return $char;
	}
	
}
?>