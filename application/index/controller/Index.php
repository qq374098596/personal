<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;
use think\Db;

class Index extends Controller
{

	//	初始化
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\index\model\Index();
	}

    /**
     * 获取首页数据
     * @return [type] [description]
     */
    public function index()
    {
    	// 获取品牌点评
    	$review = $this->db->reviews('pinpai','dianping','user');
    	// 获取导师
    	$tutor = $this->db->getAll('daoshi',['id','headimg','name','old','are','zizhi','xiangmu','xiangmuxiangqing','imglist']);
    	foreach ($tutor as $k => $v) {
			$tutor[$k]['imglist'] = json_decode($v['imglist'])[0];
		}
		// 获取贴子
    	$card = $this->db->getCard('tiezi');
        foreach ($card as $k => $v) {
            $card[$k]['time'] = date('Y-m-d',$v['time']);
        }
    	$this->assign('_review',$review);
    	$this->assign('_tutor',$tutor);
    	$this->assign('_card',$card);
    	$this->assign('title','返乡创业网');
        return $this->fetch();
    }

    /**
     *  严选品牌
     * @return [type]
     */
    public function strict()
    {
    	$brand = $this->db->strictAll('pinpai',['pp.id','pp.imgsrc'],"pp.kind=2");
        // 小程序数据库->诊断表
        $xiaochengxu = Db::connect('mysql://fx_xiao:m6eHF6m3RBtaeGW6@39.104.167.90:3306/fx_xiao#utf8');
        $diagnosisAll = $xiaochengxu->table('fanxiang_zhenduan')->count();
        
    	$datajson = array(
    		'brand'=>$brand,
            'diagnosisAll' => $diagnosisAll,
    	);
    	echo json_encode($datajson,JSON_UNESCAPED_UNICODE);
    }

    /**
	 * 诊断提交
	 *
	 */
	public function diagnosis(){
		if (request()->isAjax()) {
            $validate = Loader::Validate('Diagnosis');
            if (!$validate->check($_POST)) {
                
                return ['status'=>0,'error'=>$validate->getError()];
            }else{
                //var_dump($_POST);die;
                $diagnosis = $this->db->diagnosis($_POST);
                return $diagnosis;
            }	
		}
	}

    /**
     * 模糊查询
     * @return [type] [description]
     */
    public function find()
    {
        var_dump($_POST);die;
    }
}
