<?php
namespace App\index\controller;
use think\Controller;
use think\Db;

class Review extends Controller
{
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\index\model\Review();
	}

	public function index()
	{
		$this->assign('title','加盟点评');
		return $this->fetch();
	}

	/**
	 * 加盟点评公共数据
	 * @return [type] [description]
	 */
	public function common()
	{
		$reviewAll = $this->db->reviewJson('pinpai');
		return $reviewAll;
	}

	/**
	 * 初始化数据
	 * @return [type] [description]
	 */
	public function reviewJson()
	{
		$common = $this->common();
		$industry = $this->db->jsonAll('hangye');
		foreach ($common as $k=>$v){
		    foreach ($v['review'] as $vi=>$ei){
                $id=$ei['id'];
                $reply = [];
                $reply = Db::name('pinglun')->field('count(1) replyCount')->where('mkind',1)->where('mid','IN',function($query) use ($id){
                    $query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
                        $query->table('fanxiang_pinpai')->field('id')->where('id',$id);
                    });
                })->find();
                if(!empty($reply) && isset($reply['replyCount'])) $ei['replyCount'] = $reply['replyCount'];
                else $ei['replyCount'] = 0;
               $common[$k]['review'][$vi]=$ei;
            }
        }
        //热门品牌
        $hot=$this->db->reviewHot('pinpai');
        foreach ($hot as $i=>$j){
            $id=$j['id'];
            $replys = [];
            $replys = Db::name('pinglun')->field('count(1) replyCount')->where('mkind',1)->where('mid','IN',function($query) use ($id){
                $query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
                    $query->table('fanxiang_pinpai')->field('id')->where('id',$id);
                });
            })->find();
            if(!empty($replys) && isset($replys['replyCount'])) $j['replyCount'] = $replys['replyCount'];
            else $j['replyCount'] = 0;
            $hot[$i]=$j;
        }
		$data = array(
			'nav'=>$industry,
			'reviewAll'=>$common,
            'hot'=>$hot
		);
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
	}
    /*
     * 更多
     * */
    public function more()
    {
        $where=input('param.id');
        $reviewAll = $this->db->reviewJson('pinpai',$where);
        foreach ($reviewAll as $k=>$v){
            foreach ($v['review'] as $vi=>$ei){
                $id=$ei['id'];
                $reply[] = Db::name('pinglun')->field('count(1) replyCount')->where('mkind',1)->where('mid','IN',function($query) use ($id){
                    $query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
                        $query->table('fanxiang_pinpai')->field('id')->where('id',$id);
                    });
                })->find();
                $v['review'][$vi] = array_merge($ei, $reply[$vi]);
                $reviewAll[$k]=$v;

            }
        }
        $json=json_encode($reviewAll,JSON_UNESCAPED_UNICODE);
        if (input('param.type') == 'json') {
            return $json;
        }else{
            $this->assign('data',$json);
            $this->assign('title','加盟点评');
            return $this->fetch();
        }
    }

	/**
	 * 我要点评
	 * @return [type] [description]
	 */
	public function addreview()
	{
		if (!session('user')) {
            if (Request()->isAjax()) {
                $data['code']=500;
                return $data;
            }else{
                $this->redirect('/');
            }
		}else{
            if (request()->isAjax()) {
                $addReview = $this->db->addReview($_POST);
                return $addReview;
            }else{
                $id = input('param.id');
                //var_dump($id);die;
                $this->assign('id',$id);
                $this->assign('title','我要点评');
                return $this->fetch();
            }
        }
	}

	/**
	 * 点评详情页
	 * @return [type] [description]
	 */
	public function detail()
	{
		$id = input('param.id');
        $uid=session('user.id');
		if (!$id) {
			$industry = $this->db->jsonAll('hangye');
			$data = array(
				'industry' => $industry,
			);
			$json = json_encode($data,JSON_UNESCAPED_UNICODE);
			return $json;
		}else{
			//获取点评详情数据
			$detail = $this->db->detail('pinpai',$id);
			$detail["ppimglist"] = json_decode($detail["ppimglist"]);

			//获取最新点评
			$newReview = $this->db->newReview('pinpai',$id);

			//获取推荐品牌
			$recBrand = $this->db->recReview('pinpai',$id,$detail['hangyeid']);

			//获取行业
			$industry = $this->db->jsonAll('hangye');

			//获取评论
			$review = $this->db->review('dianping',$id);

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
			$items=$review->items();
			foreach ($items as $k => $v) {
                $items[$k]['imglist'] = json_decode($v['imglist']);
                $items[$k]['time'] = date('Y-m-d',$v['time']);
                //第三层评论
                foreach ($items[$k]['reply'] as $key => $value) {

                    $replyreply[$k]['replyreply'] = Db::name('pinglun')->field('id,uid1,uid2,gid,content,time')->where('mkind',1)->where("mid='".$v['id']."' AND gid='".$value['id']."'")->where('uid2','<>',0)->select();

                        $items[$k]['reply'][$key] = array_merge($value, $replyreply[$k]);
                        $items[$k]['reply'][$key]['time'] = date('Y-m-d', $value['time']);
                        foreach ($items[$k]['reply'][$key]['replyreply'] as $key1 => $value1) {
                            $uid1[] = $this->user($value1['uid1']);
                            $uid2[] = $this->user($value1['uid2']);
                            $items[$k]['reply'][$key]['replyreply'][$key1]['uid1name'] = $uid1[$key1]['nickname'];
                            $items[$k]['reply'][$key]['replyreply'][$key1]['uid2name'] = $uid2[$key1]['nickname'];
                        }

                }
			}
			$dianpingnum=Db::name('dianping')->where('pinpaiid',$id)->count();
			//获取点击状态（好评、中评、差评）
			$reviewStatus = $this->db->status($id);

			//是否已收藏
        	$getshoucang=Db::name('shoucang')->where(['uid'=>session("user.id"),'mid'=>$id,'kind'=>1,'status'=>1])->find();
        	//是否举报
        	$getReport=Db::name('jubao')->where(['uid'=>session("user.id"),'mid'=>$id,'status'=>1])->find();
        	//收藏人数
        	$shoucangnum=Db::name('shoucang')->where(['mid'=>$id,'kind'=>1,'status'=>1])->count();
			$data = array(
				'detail' => $detail,
				'newReview' => $newReview,
				'review' =>$items,
				'page' => $review,
				'recReview' => $recBrand,
				'reviewStatus' => $reviewStatus,
				'industry' => $industry,
        	    'getshoucang' => $getshoucang,
        	    'getReport' => $getReport,
        	    'shoucangnum' => $shoucangnum,
                'dianpingnum' => $dianpingnum
			);
			$json = json_encode($data,JSON_UNESCAPED_UNICODE);
			if (input('param.type') == 'json') {
				return $json;
			}else{
				$this->assign('data',$json);
				$this->assign('title','加盟点评');
				return $this->fetch();
			}
		}
	}


	public function comment()
	{
		//var_dump($_POST);die;
		$comment = $this->db->comment($_POST);
		return $comment;
	}
	//品牌收藏
	public function getShoucang(){
        $mid=input('param.id');
        $uid=session('user.id');
        if(!$uid){
            return;
        }
        $status=Db::name('shoucang')->where(['uid'=>$uid,'kind'=>1,'mid'=>$mid])->field('status')->find();
        if(!$status){
            $re=Db::name('shoucang')->insert(['uid'=>$uid,'kind'=>1,'mid'=>$mid,'status'=>1,'time'=>time()]);
            $data['getshoucang']=1;
        }
        if ($status['status']==1){
            $re=Db::name('shoucang')->where(['uid'=>$uid,'kind'=>1,'mid'=>$mid])->update(['status'=>0]);
            $data['getshoucang']=null;
        }
        if($status['status']==0){
            $re=Db::name('shoucang')->where(['uid'=>$uid,'kind'=>1,'mid'=>$mid])->update(['status'=>1]);
            $data['getshoucang']=1;
        }
        $num=Db::name('shoucang')->where(['mid'=>$mid,'kind'=>1,'status'=>1])->count();
        $data['shoucangnum']=$num;
        return $data;
    }

    /**
     * 举报
     * @return [type] [description]
     */
    public function report()
    {
    	$mid = input('param.id');
    	$uid = session('user.id');
        if(!$uid){
            return;
        }
    	$status = Db::name('jubao')->where(['uid'=>$uid,'mid'=>$mid])->field('status')->find();
    	if (!$status) {
    		$re = Db::name('jubao')->insert(['uid'=>$uid,'mid'=>$mid,'status'=>1,'time'=>time()]);
    		$data['getReport'] = 1;
    	}
    	if ($status['status'] == 1) {
    		$re=Db::name('jubao')->where(['uid'=>$uid,'mid'=>$mid])->update(['status'=>0]);
            $data['getReport']=null;
    	}
    	if ($status['status'] == 0) {
    		$re=Db::name('jubao')->where(['uid'=>$uid,'mid'=>$mid])->update(['status'=>1]);
            $data['getReport']=1;
    	}
    	return $data;

    }
    //搜索
    public function Search(){
        $search = request()->post('search');
        if(request()->post('id')){
            $hid=request()->post('id');
        }else{
            $hid='';
        }

        $reviewAll = $this->searchJson('pinpai',$search,$hid);
        foreach ($reviewAll as $k=>$v){
            foreach ($v['review'] as $vi=>$ei){
                $id=$ei['id'];
                $reply[] = Db::name('pinglun')->field('count(1) replyCount')->where('mkind',1)->where('mid','IN',function($query) use ($id){
                    $query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
                        $query->table('fanxiang_pinpai')->field('id')->where('id',$id);
                    });
                })->find();
                $v['review'][$vi] = array_merge($ei, $reply[$vi]);
                $reviewAll[$k]=$v;

            }
        }
        if($reviewAll==[]){
            $reviewAll['code']=500;
        }
        return $reviewAll;
    }
    public function searchJson($table,$search,$hid='')
    {
        $where='';
        if($hid!=''){
            $where=$where=['id'=>$hid];
        }
        $industry = Db::name('hangye')->where($where)->field('id,hyname,bgcolor')->select();
        foreach ($industry as $k => $v) {
            $common = $this->commo($table,'',$v['id'],$search);
            $reviews[$k]['review'] = $common['reviews'];
            $industry[$k] = array_merge($reviews[$k],$v);
            if(!$reviews[$k]['review']){
                unset($industry[$k]);
            }
        }
        return $industry;
    }
    public function commo($table='',$id='',$hyid='',$search)
    {

        $soure = $this->db->aliasSql('dianping','d','pinpaiid','p','id','pingfen','sum');
        $count = $this->db->aliasSql('dianping','d','pinpaiid','p','id','1','count');
        $reviews = array();
        if ($table) {
            $fields = "p.id,p.name,p.imgsrc,(".$count.") count,(".$soure.") soure";
            if ($id) {
                $where = "p.hangyeid=>".$hyid." AND id<>'".$id."'";
            }else{
                $where = "p.hangyeid=>".$hyid."";
            }
            $reviews = Db::name($table)->alias('p')->field($fields)->where('name', 'like','%'.$search.'%')->where('p.hangyeid',$hyid)->select();
        }
        $common = array(
            'soure' => $soure,
            'count' => $count,
            'reviews' => $reviews,
        );
        return $common;
    }

    //评论分页
    public function page()
    {
        $id=request()->post('id');
        $nowpage=request()->post('dj');
        $uid=session('user.id');
        $options = [
            'page' => $nowpage
        ];
        $dd=request()->post();
        $where='';
        if(isset($dd['good'])){
            $fen=$dd['good'];
            if($fen==1){
                $where['pingfen']=array('gt',5);
            }
            if($fen==2){
                $where['pingfen']=array('in','4,5');
            }
            if($fen==3){
                $where['pingfen']=array('lt',4);
            }
            if($fen==4){
                $where['pingfen']=array('lt',8);
            }
            if($fen==5){
                $where['imglist']= array('neq','null');
            }
        }
        $review = $this->review('dianping',$id,$options,$where);
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
        $items=$review->items();
        foreach ($items as $k => $v) {
            $items[$k]['imglist'] = json_decode($v['imglist']);
            $items[$k]['time'] = date('Y-m-d',$v['time']);
            //第三层评论
            foreach ($items[$k]['reply'] as $key => $value) {

                $replyreply[$k]['replyreply'] = Db::name('pinglun')->field('id,uid1,uid2,gid,content,time')->where('mkind',1)->where("mid='".$v['id']."' AND gid='".$value['id']."'")->where('uid2','<>',0)->select();

                    $items[$k]['reply'][$key] = array_merge($value, $replyreply[$k]);
                    $items[$k]['reply'][$key]['time'] = date('Y-m-d', $value['time']);
                    foreach ($items[$k]['reply'][$key]['replyreply'] as $key1 => $value1) {
                        $uid1[] = $this->user($value1['uid1']);
                        $uid2[] = $this->user($value1['uid2']);
                        $items[$k]['reply'][$key]['replyreply'][$key1]['uid1name'] = $uid1[$key1]['nickname'];
                        $items[$k]['reply'][$key]['replyreply'][$key1]['uid2name'] = $uid2[$key1]['nickname'];
                    }

            }
        }
        $data=array(
            'review'=>$items,
            'page'=>$review
        );
        return $data;

    }
    public function review($table,$id,$options,$where)
    {
        // 点评信息 第一层
        $review = Db::name('dianping')->alias('dp')->join('fanxiang_user u','dp.uid=u.id')
            ->join("fanxiang_pinpai pp","dp.pinpaiid=pp.id")
            ->where($where)
            ->field('u.nickname,u.headimg,pp.company,dp.id,dp.jiamengfei,dp.zongchengben,dp.date,dp.city,dp.yueyingshou,dp.imglist,dp.pingfen,dp.content,dp.time')
            ->where('dp.pinpaiid',$id)->paginate(5,false,$options);
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
    //评论点赞
    public function pdianzan()
    {
        $nowpage = request()->post('page');
        $options = [
            'page' => $nowpage
        ];
        $id = request()->post('mid');
        $pid = request()->post('pid');
        $uid = session('user.id');
        if (!$uid) {
            return;
        }
        $dd=request()->post();
        $where='';
        if(isset($dd['good'])){
            $fen=$dd['good'];
            if($fen==1){
                $where['pingfen']=array('gt',5);
            }
            if($fen==2){
                $where['pingfen']=array('in','4,5');
            }
            if($fen==3){
                $where['pingfen']=array('lt',4);
            }
            if($fen==4){
                $where['pingfen']=array('lt',8);
            }
            if($fen==5){
                $where['imglist']= array('neq','null');
            }
        }
        $review = $this->review('dianping',$id,$options,$where);
        $res = Db::name('commentary')->where(['pid' => $pid, 'kind' => 1, 'mid' => $id, 'myid' => $uid])->find();
        if ($res) {
            if($res['status']==1){
                $res=Db::name('commentary')->where(['pid' => $pid, 'kind' => 1, 'mid' => $id, 'myid' => $uid])->update(['status'=>0]);
                //评论自己是否点赞
                foreach ($review as $k => $v) {
                    $req[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'myid' => $uid, 'status' => 1])->field('status')->find();
                    foreach ($req as $ki => $vi) {
                        if ($vi == null) {
                            $req[$ki] = array('status' => 0);
                        }
                    }
                    $review[$k] = array_merge($v, $req[$k]);
                }
                //评论总点赞
                foreach ($review as $k => $v) {
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
                //评论自己是否点赞
                foreach ($review as $k => $v) {
                    $req[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'myid' => $uid, 'status' => 1])->field('status')->find();
                    foreach ($req as $ki => $vi) {
                        if ($vi == null) {
                            $req[$ki] = array('status' => 0);
                        }
                    }
                    $review[$k] = array_merge($v, $req[$k]);
                }
                //评论总点赞
                foreach ($review as $k => $v) {
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
        } else {
            $re = Db::name('commentary')->insert(['pid' => $pid, 'kind' => 1, 'mid' => $id, 'myid' => $uid, 'status' => 1]);
            //评论自己是否点赞
            foreach ($review as $k => $v) {
                $req[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 1, 'myid' => $uid, 'status' => 1])->field('status')->find();
                foreach ($req as $ki => $vi) {
                    if ($vi == null) {
                        $req[$ki] = array('status' => 0);
                    }
                }
                $review[$k] = array_merge($v, $req[$k]);
            }
            //评论总点赞
            foreach ($review as $k => $v) {
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
        $items=$review->items();
        foreach ($items as $k => $v) {
            $items[$k]['imglist'] = json_decode($v['imglist']);
            $items[$k]['time'] = date('Y-m-d',$v['time']);
            //第三层评论
            foreach ($items[$k]['reply'] as $key => $value) {

                $replyreply[$k]['replyreply'] = Db::name('pinglun')->field('id,uid1,uid2,gid,content,time')->where('mkind',1)->where("mid='".$v['id']."' AND gid='".$value['id']."'")->where('uid2','<>',0)->select();
                    $items[$k]['reply'][$key] = array_merge($value, $replyreply[$k]);
                    $items[$k]['reply'][$key]['time'] = date('Y-m-d', $value['time']);
                    foreach ($items[$k]['reply'][$key]['replyreply'] as $key1 => $value1) {
                        $uid1[] = $this->user($value1['uid1']);
                        $uid2[] = $this->user($value1['uid2']);
                        $items[$k]['reply'][$key]['replyreply'][$key1]['uid1name'] = $uid1[$key1]['nickname'];
                        $items[$k]['reply'][$key]['replyreply'][$key1]['uid2name'] = $uid2[$key1]['nickname'];

                }
            }
        }

        $data=array(
            'review'=>$items,
            'page'=>$review
        );
        return $data;
    }
    public function fen(){
        $fen=request()->post('good');
        $id=request()->post('id');
        $uid=session('user.id');
        if($fen==1){
            $where['pingfen']=array('gt',5);
        }
        if($fen==2){
            $where['pingfen']=array('in','4,5');
        }
        if($fen==3){
            $where['pingfen']=array('lt',4);
        }
        if($fen==4){
            $where['pingfen']=array('lt',8);
        }
        if($fen==5){
            $where['imglist']= array('neq','null');
        }
        $review = $this->db->review('dianping',$id,$where);
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
        $items=$review->items();
        foreach ($items as $k => $v) {
            $items[$k]['imglist'] = json_decode($v['imglist']);
            $items[$k]['time'] = date('Y-m-d',$v['time']);
            //第三层评论
            foreach ($items[$k]['reply'] as $key => $value) {

                $replyreply[$k]['replyreply'] = Db::name('pinglun')->field('id,uid1,uid2,gid,content,time')->where('mkind',1)->where("mid='".$v['id']."' AND gid='".$value['id']."'")->where('uid2','<>',0)->select();
                    $items[$k]['reply'][$key] = array_merge($value, $replyreply[$k]);
                    $items[$k]['reply'][$key]['time'] = date('Y-m-d', $value['time']);
                    foreach ($items[$k]['reply'][$key]['replyreply'] as $key1 => $value1) {
                        $uid1[] = $this->user($value1['uid1']);
                        $uid2[] = $this->user($value1['uid2']);
                        $items[$k]['reply'][$key]['replyreply'][$key1]['uid1name'] = $uid1[$key1]['nickname'];
                        $items[$k]['reply'][$key]['replyreply'][$key1]['uid2name'] = $uid2[$key1]['nickname'];

                }
            }
        }
        $data=array(
            'review'=>$items,
            'page'=>$review
        );
        return $data;
    }

}
?>