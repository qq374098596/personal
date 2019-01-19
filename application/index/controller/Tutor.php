<?php
namespace App\index\Controller;
use think\Controller;

class Tutor extends Controller{
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\index\model\Tutor();
	}

	// 导师首页
	public function index()
	{
		// 行业
		$trade = $this->db->getAll('','hangye');
		$resule = array();
		foreach ($trade as $k => $v) {
			$resule[$v['id']] = $v['hyname'];
		}
		// 导师
		$tutor = $this->db->getAll('daoshi','hangye');
		$data = array(
			'tutor'	=>$tutor,
			'resule'	=>$resule,
		);
		$json = json_encode($data,JSON_UNESCAPED_UNICODE);

		if (input('param.type') == 'json') {
			echo $json;
		}else{
			$this->assign('data',$json);
			$this->assign('title','创业导师');
			return $this->fetch();
		}
	}

	/**
	 * 详情页
	 * @return [type] [description]
	 */
	public function detail()
	{
		$id = input('param.id');

		$detail = $this->db->detailJson($id);
		$details = array();
		foreach ($detail as $k => $v) {
			$details = $v;
			$details['imglist'] = json_decode($v['imglist']);
		}
		//获取相似导师
		$similar = $this->db->similar($id);
		//判断是否收藏
		$getchick=db('shoucang')->field('status,time')->where(['uid'=>session("user.id"),'kind'=>4,'mid'=>$id])->find();
        if ($getchick['status'] == 1) {
        	$chickStatus = "";
        }else{
        	$chickStatus = 1;
        }
		$data = array(
			'detail' => $details,
			'similar' => $similar,
			'getchick' => $chickStatus,
		);

		$json = json_encode($data,JSON_UNESCAPED_UNICODE);

		if (input('param.type') == 'json') {
			echo $json;
		}else{
			$this->assign('data',$json);
			$this->assign('title','创业导师');
			return $this->fetch();
		}
	}

	// 收藏
	public function collect()
	{
		$mid = input('param.id');
		$uid = session('user.id');
		if ($uid) {
			$reon = db('shoucang')->where(['uid'=>$uid,'kind'=>4,'mid'=>$mid])->find();
			if (!$reon) {
				$re=db('shoucang')->insert(['uid'=>$uid,'kind'=>4,'mid'=>$mid,'status'=>1,'time'=>time()]);
        		$data['status'] = "";
			}else{
				if ($reon['status'] == 1) {
        			$re=db('shoucang')->where('uid',$uid)->where('mid',$mid)->update(['status'=>0]);
        			$data['status'] = 1;
        		}else{
        			$re=db('shoucang')->where('uid',$uid)->where('mid',$mid)->update(['status'=>1]);
        			$data['status'] = "";
        		}	
			}

			if($re){
        	    $data['code']=200;
        	}else{
        	    $data['code']=500;
        	}
        	return $data;
		}
	}
	//搜索
    public function Search(){
        $search = request()->post('search');
        $tutor = $this->db->getAll('daoshi','hangye',$search);
        return $tutor;
    }
}
?>