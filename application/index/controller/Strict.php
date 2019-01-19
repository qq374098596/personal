<?php
namespace app\index\controller;
use think\Controller;
use think\Validate;
use think\Db;
use app\lib\Qr;

class Strict extends Controller{
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\index\model\Strict();
	}

	public function index()
	{
		//行业
		$trade = $this->db->trade('hangye',10);
		$resule = array();
		foreach ($trade as $k => $v) {
			$resule[$v['id']] = $v['hyname'];
		}
		// 严选项目
		$common = $this->common();
		$data = array(
			'strictAll'	=>$common['strictAll'],
			'resule'	=>$resule,
		);
		$json = json_encode($data,JSON_UNESCAPED_UNICODE);
		if (input('param.type') == 'json') {
			echo $json;
		}else{
			$this->assign('data',$json);
			$this->assign('title','严选项目');
			return $this->fetch();
		}	
	}

	/**
	 * 严选项目公共数据
	 * @return [type] [description]
	 */
	public function common($id='')
	{
	    $pro='';
	    if(request()->post()){
	        $pro=request()->post('pro');
	    }
		$strictAll = $this->db->strictAll('pinpai','dianping','hangye',$id,$pro);
		$common = array(
			'strictAll' => $strictAll,
		);
		return $common;
	}

	/**
	 * 严选详情页
	 * @return [type] [description]
	 */
	public function detail()
	{
	    //用户访问量
	    $ip=$this->getRealIpAddr();
	    $time=time();
		$id = input('param.id');

        // 微信小程序码
        $wx = db('pinpai')->field('weixinurl')->where('id',$id)->find();
        if (!$wx['weixinurl']) {
            $qr = new Qr();
            $weixin = $qr->getXcxCode($id,'2');
            $weixinurl['weixinurl'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$weixin;
            $upwxurl = db('pinpai')->where('id',$id)->data($weixinurl)->update();
        }        

		$times=strtotime(date('Y-m-d'));
		$re=Db::name('access')->field('time')->where(['pid'=>$id,'ip'=>$ip])
            ->order('id','desc')->find();
		if($re['time']&&$re['time']>$times){
        }else{
            $db=Db::name('access')->insert(['pid'=>$id,'ip'=>$ip,'time'=>$time]);
        }
        $uid=session('user.id');
		// 品牌详情
		$common = $this->common($id);
		$detail = array();
		foreach ($common['strictAll'] as $k => $v) {
			$detail = $v;
			$detail['ppimglist'] = json_decode($v['ppimglist']);
		}
		// 盟友点评
		$review = $this->db->review('dianping',$id);
		foreach ($review as $k=>$v)
		{
            $review[$k]['imglist'] = json_decode($v['imglist']);
            $review[$k]['time'] = date('Y-m-d',$v['time']);
        }
        //我的点赞
        foreach ($review as $k=>$v)
        {
            $re[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'myid' => $uid, 'status' => 1])->field('status')->find();
            foreach ($re as $ki => $vi) {
                if ($vi == null) {
                    $re[$ki] = array('status' => 0);
                }
            }
            $review[$k] = array_merge($v, $re[$k]);
        }
        //总点赞
        foreach ($review as $k=>$v)
        {
            $rea[] = array();
            $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'status' => 1])->field('mid')->count();
            foreach ($rea as $ki => $vi) {
                if (!$vi) {
                    unset($rea[$ki]);
                }
            }
            $rea = array_values($rea);
            $review[$k] = array_merge($v, $rea[$k]);
        }

		// 获取点击状态（好评、中评、差评）
		$reviewStatus = $this->db->status($id);

		// 品牌问答
		$question = $this->db->question($id);
        //是否已收藏
        $getshoucang=Db::name('shoucang')->where(['uid'=>session("user.id"),'mid'=>$id,'kind'=>1,'status'=>1])->find();
        //收藏人数
        $shoucangnum=Db::name('shoucang')->where(['mid'=>$id,'kind'=>1,'status'=>1])->count();

		// 问答数量
		$statusNum = $this->db->statusNum($id);

		$data = array(
			'detail' => $detail,
			'review' => $review,
			'reviewStatus' => $reviewStatus,
			'question' => $question,
            'getshoucang' => $getshoucang,
            'shoucangnum' => $shoucangnum,
			'statusNum' => $statusNum,
		);
		
		$json = json_encode($data,JSON_UNESCAPED_UNICODE);
		if (input('param.type') == 'json') {
			echo $json;
		}else{
			$this->assign('data',$json);
			$this->assign('title','严选项目');
			return $this->fetch();
		}
	}

	/**
	 * 点评评论
	 * @return [type] [description]
	 */
	public function comment()
	{
		$comment = $this->db->comment($_POST);
		return $comment;
	}

	/**
	 * 问答提问
	 */
	public function addQuestion()
	{
		$addQuestion = $this->db->addQuestion($_POST);
		return $addQuestion;
	}

	/**
	 * 问答回复
	 * @return [type] [description]
	 */
	public function questionComment()
	{
		//var_dump($_POST);die;
		$questionComment = $this->db->questionComment($_POST);
		return $questionComment;
	}
    //咨询提交
	public function advice(){
        $name = request()->post('name');
        $city=request()->post('city');
        $message=request()->post('message');
        $budget=request()->post('budget');
        $phone=request()->post('phone');
        $uid=request()->post('uid');
        $pid=request()->post('mid');
        if(!preg_match("/^1[3456789]\d{9}$/", $phone)){
            $data['code']=222;
            $data['msg']='请输入正确的电话号码';
            return $data;
        }
        $re=Db::name('advice')
            ->insert(['uid'=>$uid,'pid'=>$pid,'message'=>$message,'city'=>$city,'budget'=>$budget,'username'=>$name,'phone'=>$phone,'time'=>time()]);
        if($re){
            $data['code']=200;
            $data['msg']='提交成功';
            return $data;
        }else{
            $data['code']=222;
            $data['msg']='提交失败';
            return $data;
        }
    }
    public function Search(){
        $search = request()->post('search');
        $common = $this->db->common();
        $fields = "p.id,p.name,p.imgsrc,p.tel,p.ppimglist,p.hangyeid,p.company,p.jiamengfei,p.jiamengshop,p.zhiyingshop,h.hyname,(".$common['soure'].") soure,(".$common['count'].") count";
        $strictal = Db::name('pinpai')->alias('p')->join("fanxiang_hangye h","p.kind=2 AND p.hangyeid=h.id")->field($fields)->where('name', 'like','%'.$search.'%')->select();
        if (!$strictal){
            $data['code']=500;
            return $data;
        }
        return $strictal;
	}
	//获得访问详情页用户IP地址
    public function getRealIpAddr(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    public function pdianzan(){
        $id=request()->post('mid');
        $pid=request()->post('pid');
        $uid = session('user.id');
        if (!$uid) {
            return;
        }
        $review = $this->db->review('dianping',$id);
        foreach ($review as $k=>$v)
        {
            $review[$k]['imglist'] = json_decode($v['imglist']);
            $review[$k]['time'] = date('Y-m-d',$v['time']);
        }
        $res = Db::name('commentary')->where(['pid' => $pid, 'kind' => 1, 'mid' => $id, 'myid' => $uid])->find();
        if($res){
            if($res['status']==1){
                $res=Db::name('commentary')->where(['pid' => $pid, 'kind' => 1, 'mid' => $id, 'myid' => $uid])->update(['status'=>0]);
                foreach ($review as $k=>$v)
                {
                    $re[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'myid' => $uid, 'status' => 1])->field('status')->find();
                    foreach ($re as $ki => $vi) {
                        if ($vi == null) {
                            $re[$ki] = array('status' => 0);
                        }
                    }
                    $review[$k] = array_merge($v, $re[$k]);
                }
                //总点赞
                foreach ($review as $k=>$v)
                {
                    $rea[] = array();
                    $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'status' => 1])->field('mid')->count();
                    foreach ($rea as $ki => $vi) {
                        if (!$vi) {
                            unset($rea[$ki]);
                        }
                    }
                    $rea = array_values($rea);
                    $review[$k] = array_merge($v, $rea[$k]);
                }
            }else{
                $res=Db::name('commentary')->where(['pid' => $pid, 'kind' => 1, 'mid' => $id, 'myid' => $uid])->update(['status'=>1]);
                foreach ($review as $k=>$v)
                {
                    $re[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'myid' => $uid, 'status' => 1])->field('status')->find();
                    foreach ($re as $ki => $vi) {
                        if ($vi == null) {
                            $re[$ki] = array('status' => 0);
                        }
                    }
                    $review[$k] = array_merge($v, $re[$k]);
                }
                //总点赞
                foreach ($review as $k=>$v)
                {
                    $rea[] = array();
                    $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'status' => 1])->field('mid')->count();
                    foreach ($rea as $ki => $vi) {
                        if (!$vi) {
                            unset($rea[$ki]);
                        }
                    }
                    $rea = array_values($rea);
                    $review[$k] = array_merge($v, $rea[$k]);
                }
            }
        }else{
            $re = Db::name('commentary')->insert(['pid' => $pid, 'kind' => 1, 'mid' => $id, 'myid' => $uid, 'status' => 1]);
            //我的点赞
            foreach ($review as $k=>$v)
            {
                $rea[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'myid' => $uid, 'status' => 1])->field('status')->find();
                foreach ($rea as $ki => $vi) {
                    if ($vi == null) {
                        $rea[$ki] = array('status' => 0);
                    }
                }
                $review[$k] = array_merge($v, $rea[$k]);
            }
            //总点赞
            foreach ($review as $k=>$v)
            {
                $reo[] = array();
                $reo[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'status' => 1])->field('mid')->count();
                foreach ($reo as $ki => $vi) {
                    if (!$vi) {
                        unset($reo[$ki]);
                    }
                }
                $reo = array_values($reo);
                $review[$k] = array_merge($v, $reo[$k]);
            }
        }
        return $review;
    }
}
?>