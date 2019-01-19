<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

//use think\Request;

class Card extends Controller
{
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\index\model\Card();
	}

	/**
	 *	首页显示
	 */
	public function index(){
		$this->assign('title','创业圈');
		return $this->fetch();
	}

	/**
	 *	签到
	 */
	public function sign()
	{
		$uid = input('uid');
		if (!$uid) {
			return ['status'=>0,'error'=>'请登录后签到！'];
		}else{
			$sign = $this->db->sign($uid);
			return $sign;
		}

	}

	/**
	 * 贴子公共部分数据
	 * @return [type] [description]
	 */
	public function common($id='')
	{
		// 获取主题
		$topicAll = $this->db->getAll('zhuti',[],8);
		// 获取用户
		$userAll = $this->db->userAll('user');
		// 获取贴子
		$cardAll = $this->db->joinAll('tiezi','user','',$id);
		foreach ($cardAll as $k => $v) {
			$cardAll[$k]['time'] = date('Y/m/d H:i:s',$v['time']);
		}
		//	获取帖子数量
		$cardNum = $this->db->cardNum('tiezi');
		$common = array(
			'topicAll' => $topicAll,
			'userAll' => $userAll,
			'cardAll' => $cardAll,
			'cardNum' => $cardNum,
		);
		
		return $common;
	}

	/**
	 *	初始化数据
	 */
	public function jsonAll()
	{
		//判断用户是否签到
		if (session('user')) {
			$time = strtotime(date('Y-m-d',time()));
			$uid = session('user.id');
			$sign = db('jifen')->where('uid='.$uid.' AND time>'.$time)->count();
		}else{
			$sign = '0';
		}

		$common = $this->common();
		// 获取轮播图
		$bannr = $this->db->getAll('turnimgs',['src'],2);
		// 贴子排行
		$cardOld = $this->db->cardOld();
		// 推荐
		$recard = $this->db->joinAll('tiezi','user',4);
		foreach ($recard as $k => $v) {
			$recard[$k]['time'] = date('Y/m/d H:i:s',$v['time']);
		}
        //帖子列表
        $list = db('user user')->join('tiezi t','user.id = t.uid')->order('uid desc')->paginate('10');
        foreach($list as $k=>$v)
        {
            $count[]['count'] = db('pinglun')->where(['mid'=>$v['id'],'mkind'=>3,'gid'=>0])->count();
            $list[$k]=array_merge($v,$count[$k]);
        }
		$data = array(
			'cardNum' => $common['cardNum'],
			'bannr' => $bannr,
			'sign' => $sign,
			'cardAll' => $common['cardAll'],
			'recard' => $recard,
			'oldCard' => $cardOld,
			'userAll' => $common['userAll'],
			'topicAll' => $common['topicAll'],
            'list' => $list,
		);
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
	}


	/** 
	 *	帖子详情页
	 */
	public function detail()
	{
		$id = input('param.id');
        $myid=session('user.id');
		$common = $this->common();
		//	阅读数量
		$readNum = db('tiezi')->where("id=".$id)->setInc('read_num');
		//	获取文章详情
		$detail = $this->db->getAll('tiezi',['uid','id','title','content','time','read_num'],1,$id);
		$detailall = array();
		foreach ($detail as $k => $v) {
			$v['time'] = date('Y/m/d H:i:s',$v['time']);
			foreach ($v as $ka => $va) {
				$detailall[$ka] = $va;
			}
		}
		// 获取评论信息
		$comment = $this->db->cardComment($id);
		//我评论是否点赞
        foreach ($comment as $ke=>$ve) {
		if($ve) {
            foreach ($comment as $k => $v) {
                $re[] = db('commentary')->field('status')->where(['pid' => $v['id'], 'kind' => 2, 'mid' => $id, 'myid' => $myid])->find();
            }
            foreach ($re as $k=>$v){
                if($v==null){
                    $re[$k]=array('status'=>0);
                }
            }
            foreach ($comment as $k => $v) {
                $comment[$k] = array_merge($v, $re[$k]);
            }
        }
        }
        //评论总点赞数
        foreach ($comment as $ke=>$ve) {
            if ($ve) {
                foreach ($comment as $ke => $ve) {
                    $rea[] = array();
                    $rea[]['cou'] = db('commentary')->field('mid')->where(['pid' => $ve['id'], 'kind' => 2, 'mid' => $id, 'status' => 1])->count();
                }
                foreach ($rea as $ki => $vi) {
                    if (!$vi) {
                        unset($rea[$ki]);
                    }
                }
                $rea = array_values($rea);
                foreach ($comment as $ke => $ve) {
                    $comment[$ke] = array_merge($ve, $rea[$ke]);
                }
            }
        }
		//文章点赞数
        $chick=db('dianzan')->field('mid')->where('mid',$id)->where('status',1)->where('kind',2)->select();
        $chick=count($chick);
        //文章收藏数
        $shoucang=db('shoucang')->field('mid')->where('mid',$id)->where('status',1)->where('kind',2)->select();
        $shoucang=count($shoucang);

        //自己是否点过赞
        $getchick=db('dianzan')->field('status')->where(['uid'=>session("user.id"),'mid'=>$id,'kind'=>2])->find();
        if ($getchick['status'] == 1) {
        	$chickStatus = "";
        }else{
        	$chickStatus = 1;
        }

        //是否已收藏
        $getshoucang=db('shoucang')->field('status')->where(['uid'=>session("user.id"),'mid'=>$id,'kind'=>2])->find();
        if ($getshoucang['status'] == 1) {
        	$collectStatus = "";
        }else{
        	$collectStatus = 1;
        }
		$data = array(
			'comment' => $comment,
			'detail' => $detailall,
			'userAll' => $common['userAll'],
			'topicAll' => $common['topicAll'],
            'chick' => $chick,
            'chickStatus' => $chickStatus,
            'shoucang' => $shoucang,
            'collectStatus' => $collectStatus,
		);

		$json = json_encode($data,JSON_UNESCAPED_UNICODE);
        // //自己是否点过赞
        // $getchick=db('dianzan')->where(['uid'=>session("user.id"),'mid'=>$id])->find();
        // $this->assign('getchick',$getchick);
        //是否已收藏
        // $getshoucang=db('shoucang')->where(['uid'=>session("user.id"),'mid'=>$id])->find();
        // $this->assign('getshoucang',$getshoucang);
		$this->assign('data',$json);
        $this->assign('id',$id);
		$this->assign('title','创业圈');
		return $this->fetch();
	}
	/**
	 *	帖子详情以及评论回复
	 */
	// public function detailJson()
	// {
	// 	$id = input('param.id');
	// 	$common = $this->common();
	// 	//	阅读数量
	// 	$readNum = db('tiezi')->where("id=".$id)->setInc('read_num');
	// 	//	获取文章详情
	// 	$detail = $this->db->getAll('tiezi',['uid','title','content','time','read_num'],1,$id);
	// 	$detailall = array();
	// 	foreach ($detail as $k => $v) {
	// 		$v['time'] = date('Y/m/d H:i:s',$v['time']);
	// 		foreach ($v as $ka => $va) {
	// 			$detailall[$ka] = $va;
	// 		}
	// 	}
	// 	// 获取评论信息
	// 	$comment = $this->db->cardComment($id);
	// 	//文章点赞数
 //        $chick=db('dianzan')->field('mid')->where('mid',$id)->where('status',1)->select();
 //        $chick=count($chick);
 //        //文章收藏数
 //        $shoucang=db('shoucang')->field('mid')->where('mid',$id)->select();
 //        $shoucang=count($shoucang);
 //      //评论分页
 //        $list = db('user user')->where(['mid'=>$id,'gid'=>0])->join('pinglun p','user.id = p.uid1')->paginate('5');

 //        //自己是否点过赞
 //        $getchick=db('dianzan')->field('status')->where(['uid'=>session("user.id"),'mid'=>$id])->find();
 //        if ($getchick['status'] == 1) {
 //        	$status = "";
 //        }else{
 //        	$status = 1;
 //        }
	// 	$data = array(
	// 		'comment' => $comment,
	// 		'detail' => $detailall,
	// 		'userAll' => $common['userAll'],
	// 		'topicAll' => $common['topicAll'],
 //            'chick' => $chick,
 //            'status' => $status,
 //            'shoucang' => $shoucang,
 //            'list' => $list
	// 	);
	// 	echo json_encode($data,JSON_UNESCAPED_UNICODE);
	// }

	/**
	 *	评论回复功能
	 */
	public function comment()
	{
//		var_dump($_POST);die;
		$comment = $this->db->comment($_POST);
		return $comment;
	}

	/**
	 *	话题详情页
	 */
	public function topic()
	{
		$id = input('param.id');
		$topicName = db('zhuti')->field('name')->where('id='.$id)->select();

		$this->assign('id',$id);
		$this->assign('topicName',$topicName[0]['name']);
		$this->assign('title','创业圈');
		return $this->fetch();
	}
	public function topicJson()
	{
		$id = input('param.id');
		$common = $this->common($id);
		//主题分页
        $list = db('user user')->where(['ztid'=>$id])->join('tiezi t','user.id = t.uid')->order('uid desc')->paginate('1');
        foreach($list as $k=>$v)
        {
            $count[]['count'] = db('pinglun')->where('mid',$v['id'])->where('mkind',3)->count();
            $list[$k]=array_merge($v,$count[$k]);
        }
        //帖子数
        $allNum=db('tiezi')->where('ztid',$id)->count();
		$data = array(
			'cardNum' => $common['cardNum'],
			'cardAll' => $common['cardAll'],
			'userAll' => $common['userAll'],
			'topicAll' => $common['topicAll'],
            'list' => $list,
            'allNum' => $allNum
		);
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
	}

	/**
	 *	发布帖子页面
	 */
	public function editing()
	{
		if (!session('user')) {
            if (Request()->isAjax()) {
                $data['code']=500;
                return $data;
            }else{
                $this->redirect('/');
            } 
		}else{
            $this->assign('title','创业圈-帖子发布');
            return $this->fetch();
        }
	}
	public function editload()
	{
		// 获取主题
		$common = $this->common();

		$data = array(
			'topicAll' => $common['topicAll'],
			'tuijianhuati' => $common['topicAll'],
			'selecthuati' => $common['topicAll'],
		);
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
	}

	/**
	 * 发布帖子功能
	 */
	public function addCard()
	{
		//var_dump($_POST);die;
		$addCard = $this->db->addCard($_POST);
		return $addCard;
	}
	//帖子点赞功能
	public function getChick()
    {
        $mid=input('param.');
        $mid=$mid['id'];
        $uid=session('user.id');
        $reon = db('dianzan')->where('uid',$uid)->where('kind',2)->where('mid',$mid)->find();
        //var_dump($reon);die;
        if ($reon) {
        	if ($reon['status'] == 1) {
        		$re=db('dianzan')->where('uid',$uid)->where('kind',2)->where('mid',$mid)->update(['status'=>0]);
        		$data['status'] = 1;
        	}else{
        		$re=db('dianzan')->where('uid',$uid)->where('kind',2)->where('mid',$mid)->update(['status'=>1]);
        		$data['status'] = "";
        	}	
        }else{
        	$re=db('dianzan')->insert(['uid'=>$uid,'kind'=>2,'mid'=>$mid,'status'=>1,'time'=>time()]);
        	$data['status'] = "";
        }
        
        if($re){
            $data['code']=200;
        }else{
            $data['code']=500;
        }
        return $data;
    }
    //收藏功能
    public function getShoucang()
    {
        $mid=input('param.');
        $mid=$mid['id'];
        $uid=session('user.id');
        $reon = db('shoucang')->where('uid',$uid)->where('kind',2)->where('mid',$mid)->find();
        if ($reon) {
        	if ($reon['status'] == 1) {
        		$re = db('shoucang')->where('uid',$uid)->where('kind',2)->where('mid',$mid)->update(['status'=>0]);
        		$data['status'] = 1;
        	}else{
        		$re = db('shoucang')->where('uid',$uid)->where('kind',2)->where('mid',$mid)->update(['status'=>1]);
        		$data['status'] = "";
        	}
        }else{
        	$re=db('shoucang')->insert(['uid'=>$uid,'kind'=>2,'mid'=>$mid,'status'=>1,'time'=>time()]);
        	$data['status'] = "";
        }

        if($re){
            $data['code']=200;
        }else{
            $data['code']=500;
        }
        return $data;
    }
    //帖子列表分页
    public function page(){
        $nowpage=input('param.');
        $options=[
            'page'=>$nowpage['page']
        ];
        $where='';
        if(isset($nowpage['num'])){
            $num=$nowpage['num'];
            if($num==1){
                $where='';
            }
            if($num==2){
                $where=['kind'=>3];
            }
            if($num==3){
                $where=['kind'=>2];
            }
        }
        $list = db('user user')
            ->join('tiezi t','user.id = t.uid')
            ->where($where)
            ->order('uid desc')
            ->paginate('10',false,$options);
        foreach($list as $k=>$v)
        {
            $count[]['count'] = db('pinglun')->where('mkind',3)->where('gid',0)->where('mid',$v['id'])->count();
            $list[$k]=array_merge($v,$count[$k]);
        }
        return $list;
    }
    /*帖子筛选
     * */
    public function tieziall(){
        $num=request()->post('num');
        $nu='';
        if(request()->post('id')){
            $ztid=request()->post('id');
            $nu=['ztid'=>$ztid];
        }
        if($num==1){
            $where='';
        }
        if($num==2){
            $where=['kind'=>3];
        }
        if($num==3){
            $where=['kind'=>2];
        }
        $list = db('user user')
            ->join('tiezi t','user.id = t.uid')->where($where)->where($nu)->order('uid desc')
            ->field('user.nickname,user.headimg,t.id,t.title,t.time,t.read_num')->paginate('10');
        foreach($list as $k=>$v)
        {
            $count[]['count'] = db('pinglun')->where(['mid'=>$v['id'],'mkind'=>3,'gid'=>0])->count();
            $list[$k]=array_merge($v,$count[$k]);
        }
        return $list;
    }
    //主题分页
    public function zhutiPage(){

        $nowpage=input('param.');
        $id=input('param.id');
        $options=[
            'page'=>$nowpage['page']
        ];
        $where='';
        if(isset($nowpage['num'])){
            $num=$nowpage['num'];
            if($num==1){
                $where='';
            }
            if($num==2){
                $where=['kind'=>3];
            }
            if($num==3){
                $where=['kind'=>2];
            }
        }
        $list = db('user user')->where('ztid',$id)->where($where)->join('tiezi t','user.id = t.uid')->order('uid desc')->paginate('1',false,$options);
        if($list){
            foreach($list as $k=>$v)
            {
                $count[]['count'] = db('pinglun')->where('mkind',3)->where('mid',$v['id'])->count();
                $list[$k]=array_merge($v,$count[$k]);
            }
        }

        return $list;
    }
    //评论分页
    public function detailPage(){
        $nowpage=input('param.page');
        $id=input('param.id');
        $myid=session('user.id');
        $options=[
            'page'=>$nowpage
        ];
        $uid2=0;
        $comment=$this->db->cardComment($id,$uid2,$options);
        //我是否点过赞
        foreach ($comment as $ke=>$ve) {
            if($ve) {
                foreach ($comment as $k => $v) {
                    $re[] = db('commentary')->field('status')->where(['pid' => $v['id'], 'kind' => 2, 'mid' => $id, 'myid' => $myid])->find();
                }
                foreach ($re as $k=>$v){
                    if($v==null){
                        $re[$k]=array('status'=>0);
                    }
                }
                foreach ($comment as $k => $v) {
                    $comment[$k] = array_merge($v, $re[$k]);
                }
            }
        }
        //评论总点赞数
        foreach ($comment as $ke=>$ve) {
            if ($ve) {
                foreach ($comment as $ke => $ve) {
                    $rea[] = array();
                    $rea[]['cou'] = db('commentary')->field('mid')->where(['pid' => $ve['id'], 'kind' => 2, 'mid' => $id, 'status' => 1])->count();
                }
                foreach ($rea as $ki => $vi) {
                    if (!$vi) {
                        unset($rea[$ki]);
                    }
                }
                $rea = array_values($rea);
                foreach ($comment as $ke => $ve) {
                    $comment[$ke] = array_merge($ve, $rea[$ke]);
                }
            }
        }
        return $comment;
    }
    //评论点赞
    public function changedianzan(){
        $nowpage=request()->post('page');
        $options=[
            'page'=>$nowpage
        ];
        $uid2=0;
        $id=request()->post('mid');
        $pid=request()->post('pid');
        $myid=session('user.id');
        $res=db('commentary')->where(['pid'=>$pid,'kind'=>2,'mid'=>$id,'myid'=>$myid])->find();

        if($res){
            if($res['status']==1){
                $res=db('commentary')->where(['pid'=>$pid,'kind'=>2,'mid'=>$id,'myid'=>$myid])->update(['status'=>0]);
                $comment = $this->db->cardComment($id,$uid2,$options);

                if($comment){
                    foreach ($comment as $k => $v) {
                        $re[] = db('commentary')->field('status')->where(['pid' => $v['id'], 'kind' => 2, 'mid' => $id, 'myid' => $myid])->find();
                    }
                    foreach ($re as $k=>$v){
                        if($v==null){
                            $re[$k]=array('status'=>0);
                        }
                    }
                    foreach ($comment as $k => $v) {
                        $comment[$k] = array_merge($v, $re[$k]);
                    }
                }
                if($comment){
                    foreach ($comment as $ke=>$ve){
                        $rea[]=array();
                        $rea[]['cou']=db('commentary')->field('mid')->where(['pid'=>$ve['id'],'kind'=>2,'mid'=>$id,'status'=>1])->count();
                    }
                    foreach ($rea as $ki=>$vi){
                        if (!$vi){
                            unset($rea[$ki]);
                        }
                    }
                    $rea=array_values($rea);
                    foreach ($comment as $ke=>$ve){
                        $comment[$ke]=array_merge($ve,$rea[$ke]);
                    }
                }
            }else{
                $res=db('commentary')->where(['pid'=>$pid,'kind'=>2,'mid'=>$id,'myid'=>$myid])->update(['status'=>1]);
                $comment = $this->db->cardComment($id,$uid2,$options);
                if($comment){
                    foreach ($comment as $k=>$v){
                        $re[]=db('commentary')->field('status')->where(['pid'=>$v['id'],'kind'=>2,'mid'=>$id,'myid'=>$myid])->find();
                    }
                    foreach ($re as $k=>$v){
                        if($v==null){
                            $re[$k]=array('status'=>0);
                        }
                    }
                    foreach ($comment as $k=>$v){
                        $comment[$k]=array_merge($v,$re[$k]);
                    }
                }
                if($comment){
                    foreach ($comment as $ke=>$ve){
                        $rea[]=array();
                        $rea[]['cou']=db('commentary')->field('mid')->where(['pid'=>$ve['id'],'kind'=>2,'mid'=>$id,'status'=>1])->count();
                    }
                    foreach ($rea as $ki=>$vi){
                        if (!$vi){
                            unset($rea[$ki]);
                        }
                    }
                    $rea=array_values($rea);
                    foreach ($comment as $ke=>$ve){
                        $comment[$ke]=array_merge($ve,$rea[$ke]);
                    }
                }
            }
        }else{
            $res=db('commentary')->insert(['pid'=>$pid,'kind'=>2,'mid'=>$id,'myid'=>$myid,'status'=>1]);
            $comment = $this->db->cardComment($id,$uid2,$options);
            if($comment){
                foreach ($comment as $k => $v) {
                    $re[] = db('commentary')->field('status')->where(['pid' => $v['id'], 'kind' => 2, 'mid' => $id, 'myid' => $myid])->find();
                }
                foreach ($re as $k=>$v){
                    if($v==null){
                        $re[$k]=array('status'=>0);
                    }
                }
                foreach ($comment as $k => $v) {
                    $comment[$k] = array_merge($v, $re[$k]);
                }
            }
            if($comment){
                foreach ($comment as $ke=>$ve){
                    $rea[]=array();
                    $rea[]['cou']=db('commentary')->field('mid')->where(['pid'=>$ve['id'],'kind'=>2,'mid'=>$id,'status'=>1])->count();
                }
                foreach ($rea as $ki=>$vi){
                    if (!$vi){
                        unset($rea[$ki]);
                    }
                }
                $rea=array_values($rea);
                foreach ($comment as $ke=>$ve){
                    $comment[$ke]=array_merge($ve,$rea[$ke]);
                }
            }
        }
        return $comment;
    }
    //主题随机
    public function chage(){
        $re=Db::name('zhuti')->field('id')->count();
        $re=$this->number_rand(1,$re,4);
        $where='';
        if($re){
            $where['id']=array('in',$re);
        }
        $topicAll=Db::name('zhuti')->where($where)->select();
        return $topicAll;
    }
    public function number_rand($begin,$end,$limit){
        $rand_array = range($begin, $end);
        shuffle($rand_array);
        return array_slice($rand_array, 0, $limit);
    }
}
?>