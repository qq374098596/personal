<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Diagnosis extends Controller
{
	public function index(){
		$Nowadays = strtotime(date('Y-m-d'),time());
		$yestday = strtotime(date('Y-m-d',strtotime("-1 day")));
        $xiaochengxu = Db::connect('mysql://fx_xiao:m6eHF6m3RBtaeGW6@39.104.167.90:3306/fx_xiao#utf8');
        $diagnosisAll = $xiaochengxu->table('fanxiang_zhenduan')->count();
		$yestdayNum = db('zhenduan')->where('time>'.$yestday.' AND time<'.$Nowadays)->count();
		$data = array(
			'diagnosisAll' => $diagnosisAll,
			'yestdayNum' => $yestdayNum,
		);
		$json = json_encode($data);
		$this->assign('data',$json);
		$this->assign('title','加盟诊断');
		return $this->fetch();
	}
}
?>