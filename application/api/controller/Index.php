<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/7
 * Time: 3:25 PM
 */

namespace app\api\controller;
//use think\Route;
use think\Db;
use app\index\model\Review;
//Route::rule('api.php/index/addReview','api.php/index/addReview');
class Index extends Api{
    protected $db_critique;
    public function _initialize(){
        parent::_initialize();
        $this->db_critique = new \app\model\Critique();
    }
    public function index(){
        /***
        $xiao_imgs = $this->turnimg('index');
        $xiao_gushi = Db::table('fanxiang_gushi')->field('id,title')->limit(10)->select();
        $res = array(
            'turns' => $xiao_imgs
        );
        return json($res);
        **/
        $yanxuan=Db::name('pinpai')->alias('p')->where(['p.kind'=>2,'p.is_index'=>1])
            ->join('hangye h','p.hangyeid=h.id')
            ->limit(5)->order('p.id desc')
            ->field('h.hyname,p.id,p.name,p.imgsrc,p.jiamengfei,p.jiamengshop,p.zhiyingshop,p.company')
            ->select();
        foreach ($yanxuan as $k=>$v)
        {
            $score[] = $this->sql($v['id'],"sum(pingfen) allpingfen");
            $yanxuan[$k]=array_merge($v,$score[$k]);
        }
        $putong=Db::name('pinpai')->where(['kind'=>1,'is_index'=>1])->field('id,imgsrc')
            ->limit(5)->order('id desc')->select();

        $daoshi=Db::name('daoshi')->where('tuijian',1)->limit(5)->order('id desc')
            ->field('headimg,name,id,jieshao,wx_index_img')->select();
        foreach ($daoshi as $k=>$v){
            $daoshi[$k]['wx_index_img']='https://test.fanxiang.top/static'.$v['wx_index_img'];
        }

        $xiao_review = $this->db_critique->getCritiquesLimit(5);
        foreach ($xiao_review as $k => $v) {
            $id = $v['id'];
            //$score[] = Db::name('dianping')->where('pinpaiid',$v['id'])->field("sum(pingfen) allpingfen")->find();
            $count[] = $this->sql($v['id'],"count(1) dianping_count");
            $reply[] = Db::name('pinglun')->field('count(1) pinglun_count')->where('mkind',1)->where('mid','IN',function($query) use ($id){
                $query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
                    $query->table('fanxiang_pinpai')->field('id')->where('id',$id);
                });
            })->find();
            $xiao_review[$k] = array_merge($v,$count[$k],$reply[$k]);
        }
        $data_gonggao = Db::query('select from_unixtime(a.time) as date,c.name,b.nickname from fanxiang_dianping as a left join fanxiang_user as b on a.uid=b.id left join fanxiang_pinpai as c on a.pinpaiid=c.id limit 10');
        $data=array(
            'yanxuan'=>$yanxuan,
            'putong'=>$putong,
            'daoshi'=>$daoshi,
            'dianping'=>$xiao_review,
            'gonggao'=>$data_gonggao,
            'code' => 200
        );
        return json($data);
    }
    /**
     * 故事
     * @return [type] [description]
     */
    public function story(){
    	$text = request()->get('text');
    	if ($text) {
    		$xiao_gushi = Db::table('fanxiang_gushi')->field("id,title,FROM_UNIXTIME(time,'%Y-%m-%d') as time,banner")->where('title','like','%'.$text.'%')->select();
    	}else{
    		$xiao_gushi = Db::table('fanxiang_gushi')->field("id,title,FROM_UNIXTIME(time,'%Y-%m-%d') as time,banner")->select();
    	}
    	$res = array(
            'gushi' => $xiao_gushi,
        );
        return json($res);
    }

    /**
     * 故事详情
     * @return [type] [description]
     */
    public function storyDetail(){
    	$id = request()->get('id');
    	$uid = request()->get('uid');
    	$xiao_gushi = Db::table('fanxiang_gushi')->where('id',$id)->find();
    	$xiao_comment = Db::table('fanxiang_pinglun')->alias('pl')->field('pl.*,u.nickname user1,u.headimg headimg1')->join('fanxiang_user u','pl.uid1=u.id','LEFT')
    	->where(['mid'=>$id,'mkind'=>2,'uid2'=>0])->select();
    	$xiao_likeCount = Db::table('fanxiang_dianzan')->where(['mid'=>$id,'kind'=>3])->count();
    	// $xiao_keep = Db::table('fanxiang_shoucang')->where([])
    	$res = array(
            'gushi' => $xiao_gushi,
            'pinglun' => $xiao_comment,
            //'dianzan' => $xiao_like,
            'dianzan_count' => $xiao_likeCount,
            //'shoucang' => $shoucang,
        );
        return json($res);
    	
    }

    /**
     * 点评
     * @return [type] [description]
     */
    public function review(){
    	$tradeId = request()->get('hangye');
    	$sort = request()->get('paixu');
    	$text = request()->get('text');

    	$xiao_imgs = $this->turnimg('pinpaidianping');
    	$xiao_trades = Db::table('fanxiang_hangye')->select();

    	$where = "kind=1";
    	$order = "";
    	if ($tradeId) {
    		$where .= " and hangyeid=".$tradeId;
    	}
    	if ($sort == "time") {
    		$order = "time desc";
    	}
    	if ($text) {
    		$where .= " and name like '%".$text."%'";
    	}
    	
    	$xiao_review = Db::name('pinpai')->field('id,name,imgsrc,tel,kind,bgcolor')->where($where)->order($order)->select();
    	foreach ($xiao_review as $k => $v) {
    		$id = $v['id'];
    		$score[] = $this->sql($v['id'],"sum(pingfen) allpingfen");
    		$count[] = $this->sql($v['id'],"count(1) dianping_count");
    		$reply[] = Db::name('pinglun')->field('count(1) pinglun_count')->where('mkind',1)->where('mid','IN',function($query) use ($id){
				$query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
					$query->table('fanxiang_pinpai')->field('id')->where('id',$id);
				});
			})->find();
    		$xiao_review[$k] = array_merge($v,$score[$k],$count[$k],$reply[$k]);
    	}
    	$res = array(
    		'turns' => $xiao_imgs,
    		'dianpinglist' => $xiao_review,
    		'hangye' => $xiao_trades
    	);
    	return json($res);
    }

    /**
     * 点评详情
     * @return [type] [description]
     */
    public function reviewDetail(){
    	$id = request()->get('id');
    	$uid = request()->get('uid');

        $dd=request()->get();
        $where = [];
        if(isset($dd['feilei'])){
            $fen=$dd['feilei'];
            if($fen=='haoping'){
                $where['dp.pingfen']=array('gt',4);
            }
            if($fen=='chaping'){
                $where['dp.pingfen']=array('lt',5);
            }
            if($fen=='all'){
                $where['dp.pingfen']=array('lt',8);
            }
            if($fen=='youtu'){
                $where['dp.imglist']= array('neq','null');
            }
        }
    	$xiao_review = Db::name('pinpai')->field('id,name,company,imgsrc,tel,kind')->where(['id'=>$id])->find();
    	if ($xiao_review) {
    		$score = $this->sql($id,"sum(pingfen) allpingfen");
    		$count = $this->sql($id,"count(1) dianping_count");
    		$reply = Db::name('pinglun')->field('count(1) pinglun_count')->where('mkind',4)->where('mid','IN',function($query) use ($id	){
				$query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
					$query->table('fanxiang_pinpai')->field('id')->where('id',$id);
				});
			})->find();
			$xiao_review['allpingfen'] = $score['allpingfen'];
			$xiao_review['dianping_count'] = $count['dianping_count'];
			$xiao_review['pinglun_count'] = $reply['pinglun_count'];

    		    $shoucang=Db::name('shoucang')->where(['uid'=>$uid,'kind'=>1,'mid'=>$xiao_review['id']])->field('status')->find();
                $xiao_review['shoucang'] = $shoucang['status'];
                if($xiao_review['shoucang']==null)
                {
                    $xiao_review['shoucang']=0;
                }



    		$review_review = Db::name('dianping')->alias('dp')->where($where)->field('dp.*,u.id as uid,u.nickname,u.headimg')
    			->join('fanxiang_user u','dp.uid=u.id','LEFT')->order('dp.id desc')->where('dp.pinpaiid',$id)->select();
    		foreach ($review_review as $k => $v) {

                $review_review[$k]['time'] = date('Y-m-d', $v['time']);
                $review_review[$k]['imglist'] = json_decode($v['imglist']);
    		}
    		foreach ($review_review as $k => $v){
                $stat['zannum']=Db::name('commentary')->where(['pid'=>$v['id'],'kind'=>1])->count();
                $review_review[$k]=array_merge($v,$stat);
            }
    		foreach ($review_review as $k => $v)
    		{
                $status['mystatus']=Db::name('commentary')->where(['pid'=>$v['id'],'kind'=>1,'myid'=>$uid])->field('status')->find();
                if(!isset($status['mystatus']['status']))
                {
                    $status['mystatus']['status']=0;
                }
                $review_review[$k]=array_merge($v,$status);
            }
            foreach ($review_review as $k => $v)
            {
    		    $counts['counts']=Db::name('pinglun')->where(['mkind'=>4,'mid'=>$v['id']])->count();
                $review_review[$k]=array_merge($v,$counts);
            }
    		
    		$res = array(
        	    'pinpai' => $xiao_review,
        	    'dianping' => $review_review,
        	    'code' => 200
        	);
        	return $this->jsonp($res);
    	}else{
    		$res = $this->err('暂无数据！');
    		return $res;
    	} 
    }

    /**
     * 写点评
     */
    public function addReview(){ 
    	$data = array(
    		'xiao' => 'xiao',
    		'id' =>''
    	);
    	$index_review = new \app\index\model\Review();
    	$addReview = $index_review->addReview($data);
    	if ($addReview['status'] == 0) {
    		$res = $this->err($addReview['error']);
    	}else{
    		$res = $this->err($addReview['msg']);
    	}
    	return $res;
    }

    /**
     * 严选
     * @return [type] [description]
     */
    public function strict(){
    	$tradeId = request()->get('hangye');
    	$sort = request()->get('paixu');
    	$text = request()->get('text');
    	//$fee = request()->get('')

    	$xiao_imgs = $this->turnimg('pinpaidianping');
    	$xiao_trades = Db::table('fanxiang_hangye')->select();
    	$where = "kind=2";
    	$order = "";
    	if ($tradeId) {
    		$where .= " and hangyeid=".$tradeId;
    	}
    	if ($sort == 'time') {
    		$order = "time desc";
    	}
    	if ($text) {
    		$where .= " and name like '%".$text."%'";
    	}
    	$xiao_strict = Db::name('pinpai')
    		->field('id,name,imgsrc,tel,kind,company,jiamengfei,jiamengshop,zhiyingshop,hangyeid,bgcolor')
    		->where($where)->order($order)->select();
    	foreach ($xiao_strict as $k => $v) {
    		$id = $v['id'];
    		$score[] = $this->sql($v['id'],"sum(pingfen) allpingfen");
    		$count[] = $this->sql($v['id'],"count(1) dianping_count");
    		$reply[] = Db::name('pinglun')->field('count(1) pinglun_count')->where('mkind',1)->where('mid','IN',function($query) use ($id){
				$query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
					$query->table('fanxiang_pinpai')->field('id')->where('id',$id);
				});
			})->find();
			$tradeName[] = Db::name('hangye')->field('id,hyname')->where('id',$v['hangyeid'])->find();
			$xiao_strict[$k] = array_merge($v,$score[$k],$count[$k],$reply[$k],$tradeName[$k]);
    	}
    	$res = array(
    		'turns' => $xiao_imgs,
    		'dianpinglist' => $xiao_strict,
    		'hangye' => $xiao_trades
    	);
    	return json($res);
    }

    /**
     * 严选详情
     * @return [type] [description]
     */
    public function strictDetail(){
    	$id = request()->get('id');
    	$uid = request()->get('uid');

    	$xiao_strict = Db::name('pinpai')->field('id,name,company,imgsrc,tel,kind')->where(['id'=>$id,'kind'=>2])->find();
    	if ($xiao_strict) {
    		$score = $this->sql($id,"sum(pingfen) allpingfen");
    		$count = $this->sql($id,"count(1) dianping_count");
    		$reply = Db::name('pinglun')->field('count(1) pinglun_count')->where('mkind',2)->where('mid','IN',function($query) use ($id	){
				$query->table('fanxiang_dianping')->field('id')->where('pinpaiid','IN',function($query) use ($id){
					$query->table('fanxiang_pinpai')->field('id')->where('id',$id);
				});
			})->find();
			$xiao_strict['allpingfen'] = $score['allpingfen'];
			$xiao_strict['dianping_count'] = $count['dianping_count'];
			$xiao_strict['pinglun_count'] = $reply['pinglun_count'];
    		
    		$review_review = Db::name('dianping')->alias('dp')->field('dp.*,u.id,u.nickname,u.headimg')
    			->join('fanxiang_user u','dp.uid=u.id','LEFT')->where('dp.pinpaiid',$id)->select();
    		foreach ($review_review as $k => $v) {
    			$review_review[$k]['imglist'] = json_decode($v['imglist']);
    			//$review_reply[] = Db::name('pinglun')->
    		}
    		
    		$res = array(
        	    'pinpai' => $xiao_strict,
        	    'dianping' => $review_review,
        	    // 'shoucang'=>$shoucang,
        	);
        	return json($res);
    	}else{
    		$res = $this->err('暂无数据！');
    		return $res;
    	}
    }

    /**
     * 诊断
     * @return [type] [description]
     */
    public function diagnosis(){
    	$data = array();
    	$index_index = new \app\index\model\Index();
    	$diagnosis = $index_index->diagnosis($data);
    	if ($diagnosis['valid'] == 0) {
    		$res = $this->err($diagnosis['msg']);
    	}else{
    		$res = $this->err($diagnosis['msg']);
    	}
    	return $res;
    }

    /**
     * banner图片所属页面
     * @param  [type] $page [string]
     * @return [type]       [description]
     */
    public function turnimg($page){
    	$xiao_imgs = Db::table('fanxiang_turnimgs')->where("page='".$page."' and status=1")->field('id,src')->select();
    	return $xiao_imgs;
    }

    /**
     * 查询点评数据
     * @param  [type] $reviewid [string]
     * @param  [type] $order    [string]
     * @return [type]           [description]
     */
    public function sql($reviewid,$order){
    	$sql = Db::name('dianping')->field($order)->where('pinpaiid','IN',function($query) use ($reviewid){
			$query->table('fanxiang_pinpai')->field('id')->where('id',$reviewid);
		})->find();
		return $sql;
    }
    /*
     * 收藏功能
     *
     * uid 用户的ID
     *kind  收藏类型1品牌，2帖子，3创业故事
     * mid 类型目标ID
     * status  是否收藏 0未收藏 1已收藏*/
    public function collect()
    {
        $uid=request()->get('uid');
        $kind=request()->get('kind');
        $mid=request()->get('mid');
        $status=request()->get('status');
        if($status=='0')
        {
            $re=Db::name('shoucang')->where(['uid'=>$uid,'kind'=>$kind,'mid'=>$mid])->find();
            if($re)
            {
                $res=Db::name('shoucang')->where('uid',$uid)->update(['status'=>1]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']=1;
                }else{
                    $data['code']=500;
                    $data['msg']='error';
                }
            }else{
                $res=Db::name('shoucang')->insert(['uid'=>$uid,'kind'=>$kind,'mid'=>$mid,'status'=>1,'time'=>time()]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']=1;
                }else{
                    $data['code']=500;
                    $data['msg']='error';
                }
            }
        }
        if($status==1)
        {
            $res=Db::name('shoucang')->where('uid',$uid)->update(['status'=>0]);
            if($res)
            {
                $data['code']=200;
                $data['msg']=0;
            }else{
                $data['code']=500;
                $data['msg']='error';
            }
        }
        return json($data);
    }
    /*
     * 评论点赞功能
     *
     *pid 此评论的ID
     * uid 用户的ID
     *kind  点赞类型1品牌，2帖子，3创业故事
     * mid 类型目标ID
     * status  是否点赞 0未点赞 1已点赞*/
    public function comment_like()
    {
        $pid=request()->get('pid');
        $kind=request()->get('kind');
        $mid=request()->get('mid');
        $myid=request()->get('uid');
        $status=request()->get('status');
        if($status=='0')
        {
            $re=Db::name('commentary')->where(['myid'=>$myid,'pid'=>$pid,'kind'=>$kind,'mid'=>$mid])->find();
            if($re)
            {
                $res=Db::name('commentary')->where(['myid'=>$myid,'pid'=>$pid,'kind'=>$kind,'mid'=>$mid])->update(['status'=>1]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']=1;
                }else{
                    $data['code']=500;
                    $data['msg']='error';
                }
            }else{
                $res=Db::name('commentary')->insert(['myid'=>$myid,'kind'=>$kind,'pid'=>$pid,'mid'=>$mid,'status'=>1]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']=1;
                }else{
                    $data['code']=500;
                    $data['msg']='error';
                }
            }
        }
        if($status==1)
        {
            $res=Db::name('commentary')->where(['myid'=>$myid,'pid'=>$pid,'kind'=>$kind,'mid'=>$mid])->update(['status'=>0]);
            if($res)
            {
                $data['code']=200;
                $data['msg']=0;
            }else{
                $data['code']=500;
                $data['msg']='error';
            }
        }
        return json($data);
    }
    /*
     * 点评回复
     *
     * uid1 发表者ID
     * uid2 第一层传0 以后是回复用户的ID
     * mid   此条类型的目标ID
     * mkind  目标类型1点评2故事3帖子4严选
     * gid   第一层传0 以后是评论ID
     * content  评论内容
     * */

    public function replys()
    {
        $uid1=request()->get('uid1');
        $uid2=request()->get('uid2');
        $mid=request()->get('mid');
        $mkind=request()->get('mkind');
        $gid=request()->get('gid');
        $time=time();
        $content=request()->get('content');
        if(!$uid1||!$mid||!$mkind||!$content){
            $data['msg']='缺少必备参数';
            return json($data);
        }
        $re=Db::name('pinglun')->insert(['uid1'=>$uid1,'uid2'=>$uid2,'mid'=>$mid,'mkind'=>$mkind,'gid'=>$gid
        ,'time'=>$time,'content'=>$content]);
        if($re)
        {
            $data['code']=200;
            $data['msg']='success';
        }else{
            $data['code']=500;
            $data['msg']='error';
        }
        return json($data);
    }
    /*
     * 收藏
     * uid  用户ID
     * */

    public function Collects()
    {
        $uid=request()->get('uid');
        if(!$uid)
        {
            $date['msg']='缺少用户ID';
            return json($date);
        }
        $daoshi = Db::name('shoucang')->alias('s')->where(['s.uid'=>$uid,'s.kind'=>4,'s.status'=>1])
            ->join('daoshi p','s.mid=p.id')
            ->field('p.id,p.headimg,p.name')->select();
        foreach ($daoshi as $k=>$v)
        {
            $count=Db::name('shoucang')->where(['kind'=>4,'mid'=>$v['id']])->count();
            $daoshi[$k]['count']=$count;
        }
        $pinpai = Db::name('shoucang')->alias('s')->where(['s.uid'=>$uid,'s.kind'=>1,'s.status'=>1])
            ->join('pinpai p','s.mid=p.id')
            ->field('p.id,p.name,p.imgsrc,p.kind')->select();
        foreach ($pinpai as $k=>$v)
        {
            $count=Db::name('shoucang')->where(['kind'=>1,'mid'=>$v['id']])->count();
            $pinpai[$k]['count']=$count;
        }
        $mytiezi=Db::name('shoucang')->alias('s')->where(['s.uid'=>$uid,'s.kind'=>2,'s.status'=>1])
            ->join('tiezi t','t.id=s.mid')
            ->field('t.id,t.title,t.imgsrc')->select();
        foreach ($mytiezi as $k=>$v){
            $count=Db::name('shoucang')->where(['kind'=>2,'mid'=>$v['id']])->count();
            $mytiezi[$k]['count']=$count;
        }
        $data=array(
            'tiezi'=>$mytiezi,
            'pinpai'=>$pinpai,
            'daoshi'=>$daoshi
        );
        return json($data);
    }


    /*
     * 我的评论
     * uid 用户ID*/

    public function myComment()
    {
        $uid=request()->get('uid');
        if(!$uid)
        {
            $date['msg']='缺少用户ID';
            return json($date);
        }
        //点评
        $dianping=Db::name('pinglun')->alias('p')
            ->join('dianping d','p.mid=d.id')
            ->where(['p.uid1'=>$uid,'p.mkind'=>1])->field('d.id,d.pinpainame,p.content')
            ->select();
        //故事
        $gushi=Db::name('pinglun')->alias('p')
            ->join('gushi g','p.mid=g.id')
            ->where(['p.uid1'=>$uid,'p.mkind'=>2])->field('g.id,g.title,p.content')
            ->select();
        //帖子
        $tiezi=Db::name('pinglun')->alias('p')
            ->join('tiezi t','p.mid=t.id')
            ->join('user u','u.id=t.uid')
            ->where(['p.uid1'=>$uid,'p.mkind'=>3])->field('t.id,t.title,p.content,u.nickname')
            ->select();
        //严选
        $yanxuan=Db::name('pinglun')->alias('p')
            ->join('pinpai a','p.mid=a.id')
            ->where(['p.uid1'=>$uid,'a.kind'=>2])->field('a.id,a.name,p.content')
            ->select();

        $data=array(
            'dianping'=>$dianping,
            'gushi'=>$gushi,
            'tiezi'=>$tiezi,
            'yanxuan'=>$yanxuan
        );
        return json($data);
    }
    /*投诉品牌
    uid  用户ID
    pname  品牌名称
    content  原因
    imglist  图片*/

    public function complain()
    {
        $uid=request()->get('uid');
        $pname=request()->get('pname');
        $content=request()->get('content');
        $imglist=request()->get('imglist');
        if(!$uid)
        {
            $date['msg']='缺少用户ID';
            return json($date);
        }
        $re=Db::name('tousu')->insert(['uid'=>$uid,'pname'=>$pname,'content'=>$content,'imglist'=>$imglist]);
        if($re)
        {
            $data['code']=200;
            $data['msg']='success';
        }else{
            $data['code']=500;
            $data['msg']='error';
        }
        return json($data);
    }
    //我的首页积分数
    //uid  用户ID
    public function fen()
    {
        $uid=request()->get('uid');
        if(!$uid)
        {
            $date['msg']='缺少用户ID';
            return json($date);
        }
        $sum = Db::name('jifen')->where('uid',$uid)->sum('jifen');
        $data['fen']=$sum;
        return json($data);
    }
    /*
     * 签到
     * uid  用户ID*/
    public function sign()
    {
        $uid=request()->get('uid');
        if(!$uid)
        {
            $date['msg']='缺少用户ID';
            return json($date);
        }
        $re=Db::name('jifen')->insert(['uid'=>$uid,'jifen'=>10,'time'=>time()]);
        if ($re)
        {
            $data['code']=200;
            $data['msg']='success';
        }else{
            $data['code']=500;
            $data['msg']='error';
        }
        return json($data);
    }
    /*
     * 举报
     * uid  用户ID
     * mid 目标ID*/
    public function report()
    {
        $uid=request()->get('uid');
        $mid=request()->get('mid');
        if(!$uid||!$mid)
        {
            $date['msg']='缺少必备参数';
            return json($date);
        }
        $re=Db::name('jubao')->insert(['uid'=>$uid,'mid'=>$mid,'time'=>time(),'status'=>0]);
        if($re)
        {
            $data['code']=200;
            $data['msg']='success';
        }else{
            $data['code']=500;
            $data['msg']='error';
        }
        return json($data);
    }
    /*
     * 发帖
     * uid 用户ID
     * title  标题
     * imgsrc  图片
     * content 内容
     * ztid  主题ID可选参数*/

    public function posted()
    {
        $uid=request()->get('uid');
        $title=request()->get('title');
        $imgsrc=request()->get('imgsrc');
        $content=request()->get('content');
        if(!$uid)
        {
            $date['msg']='缺少用户ID';
        }
        if(request()->get('ztid'))
        {
            $ztid=request()->get('ztid');
        }else{
            $ztid=0;
        }
        $re=Db::name('tiezi')
            ->insert(['uid'=>$uid,'title'=>$title,'imgsrc'=>$imgsrc,'content'=>$content,'time'=>time(),'date'=>date('Y-m-d'),
                'ztid'=>$ztid,'kind'=>1,'zhiding'=>0]);
        if($re)
        {
            $data['code']=200;
            $data['msg']='success';
        }else{
            $data['code']=500;
            $data['msg']='error';
        }
        return json($data);
    }
    /*
     * 点赞
     * uid 用户ID
     * kind 类型1品牌，2帖子，3创业故事
     *mid  目标ID
     * status 是否点赞 0未点赞 1已点赞*/
    public function likes()
    {
        $uid=request()->get('uid');
        $kind=request()->get('kind');
        $mid=request()->get('mid');
        $status=request()->get('status');
        if(!$uid)
        {
            $date['msg']='缺少用户ID';
        }
        if($status=='0')
        {
            $re=Db::name('dianzan')->where(['uid'=>$uid,'kind'=>$kind,'mid'=>$mid])->find();
            if($re)
            {
                $res=Db::name('dianzan')->where(['uid'=>$uid,'kind'=>$kind,'mid'=>$mid])->update(['status'=>1]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']='success';
                }else{
                    $data['code']=500;
                    $data['msg']='修改失败';
                }
            }else{
                $res=Db::name('dianzan')->insert(['uid'=>$uid,'kind'=>$kind,'mid'=>$mid,'status'=>1,'time'=>time()]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']='success';
                }else{
                    $data['code']=500;
                    $data['msg']='添加失败';
                }
            }
        }
        if($status==1)
        {
            $res=Db::name('dianzan')->where(['uid'=>$uid,'kind'=>$kind,'mid'=>$mid])->update(['status'=>0]);
            if($res)
            {
                $data['code']=200;
                $data['msg']='success';
            }else{
                $data['code']=500;
                $data['msg']='修改失败';
            }
        }
        return json($data);
    }
    /*
     * 消息
     *
     *kind  1为系统通知 2为商家入住 内容
     *  */
    public function message()
    {
        $message=Db::name('message')->where('status',1)->order('time desc')->select();
        foreach ($message as $k=>$v){
            $message[$k]['time']=date('H:i',$message[$k]['time']);
        }
        $count=count($message);
        $data=array(
            'message'=>$message,
            'count'=>$count
        );
        return json($data);
    }
    public function myindex()
    {
        $message=Db::name('message')->where('status',1)->select();
        $count=count($message);
        $data=array(
            'myxiaoxi_count'=>$count
        );
        return $this->jsonp($data);
    }
    /*
     * 平台规则
     * texttype 类型*/

    public function getguize()
    {
        $type=request()->get('texttype');
        $res=Db::name('setting')->where('setkey',$type)->field('setvalue')->find();
        $data=array(
            'guize'=>$res
        );
        return json($data);
    }
    /*
     * 创业说
     * */
    public function Card()
    {

        //主题
        $zhuti=Db::name('zhuti')->select();
        //人数
        $tt=Db::name('tiezi')->count('uid');
        $pl=Db::name('pinglun')->where('mkind',3)->count('uid1');
        $sc=Db::name('shoucang')->where('kind',2)->count('uid');
        $dianzan=Db::name('dianzan')->where('kind',2)->count('uid');
        $pople=$pl+$sc+$dianzan+$tt;
        //5个头像
        $img5=Db::name('tiezi')->alias('s')->join('user u','s.uid=u.id')
            ->field('u.headimg')->limit(5)->order('s.id desc')->select();

        $zt=request()->get('zt');
        $jx=request()->get('jx');
        $where = [];
        if ($jx==1) {
            $where['t.kind']=2;
        }
        if ($zt) {
            $where['t.ztid']=$zt;
        }
        $tiezi=Db::name('tiezi')->alias('t')->where($where)->order('t.time desc')
            ->join('user u','t.uid=u.id','LEFT')
            ->join('zhuti z','t.ztid=z.id','LEFT')
            ->field('t.title,t.imgsrc,t.id,z.name,u.nickname,u.headimg')->select();
        foreach ($tiezi as $k=>$v)
        {
            $plrs=Db::name('pinglun')->where(['mkind'=>3,'mid'=>$v['id']])->count('id');
            $tiezi[$k]['count']=$plrs;
        }

        $data=array(
            'imgf'=>$img5,
            'pople'=>$pople,
            'zhuti'=>$zhuti,
            'tiezi'=>$tiezi

        );
        return json($data);
    }
    /*
     * 创业说关注内容列表
     * uid  用户ID*/
    public function Cardatt()
    {
        //主题
        $zhuti=Db::name('zhuti')->select();
        //人数
        $tt=Db::name('tiezi')->count('uid');
        $pl=Db::name('pinglun')->where('mkind',3)->count('uid1');
        $sc=Db::name('shoucang')->where('kind',2)->count('uid');
        $dianzan=Db::name('dianzan')->where('kind',2)->count('uid');
        $pople=$pl+$sc+$dianzan+$tt;
        //5个头像
        $img5=Db::name('tiezi')->alias('s')->join('user u','s.uid=u.id')
            ->field('u.headimg')->limit(5)->select();

        $gz=request()->get('uid');

        if($gz)
        {
            $join=Db::name('attention')->where('noticer',$gz)->field('followers')->buildSql();
        }
        $tiezi=Db::name('tiezi')->alias('t')->order('t.time desc')
            ->join('user u','t.uid=u.id','LEFT')
            ->join('zhuti z','t.ztid=z.id','LEFT')
            ->join([$join=>'j'],'t.uid=j.followers')
            ->field('t.title,t.imgsrc,t.id,z.name,u.nickname,u.headimg')->select();
        foreach ($tiezi as $k=>$v)
        {
            $plrs=Db::name('pinglun')->where(['mkind'=>3,'mid'=>$v['id']])->count('id');
            $tiezi[$k]['count']=$plrs;
        }
        $guanzhu=1;
        if(!isset($tiezi[0]))
        {
            $guanzhu=0;
            $tiezi=Db::name('tiezi')->alias('t')->where('t.kind',2)->order('t.time desc')
                ->join('user u','t.uid=u.id','LEFT')
                ->join('zhuti z','t.ztid=z.id','LEFT')
                ->field('t.title,t.imgsrc,t.id,z.name,u.nickname,u.headimg')->select();
            foreach ($tiezi as $k=>$v)
            {
                $plrs=Db::name('pinglun')->where(['mkind'=>3,'mid'=>$v['id']])->count('id');
                $tiezi[$k]['count']=$plrs;
            }
        }

        $data=array(
            'imgf'=>$img5,
            'pople'=>$pople,
            'zhuti'=>$zhuti,
            'tiezi'=>$tiezi,
            'guanzhu'=>$guanzhu

        );
        return json($data);
    }

    /*加盟说关注功能
    noticer  关注用户id
    followers  被关注者
    status  关注状态0未关注1已关注*/

    public function attention()
    {
        $noticer=request()->get('noticer');
        $followers=request()->get('followers');
        $status=request()->get('status');
        if($status=='0')
        {
            $re=Db::name('attention')->where(['noticer'=>$noticer,'followers'=>$followers])
                ->field('status')->find();
            if($re)
            {
                $res=Db::name('attention')->where(['noticer'=>$noticer,'followers'=>$followers])->update(['status'=>1]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']='修改成功';
                }else{
                    $data['code']=500;
                    $data['msg']='修改失败';
                }
            }else{
                $res=Db::name('attention')->insert(['noticer'=>$noticer,'followers'=>$followers,'time'=>time(),'status'=>1]);
                if($res)
                {
                    $data['code']=200;
                    $data['msg']='新增成功';
                }else{
                    $data['code']=500;
                    $data['msg']='新增失败';
                }
            }
        }
        if($status==1)
        {
            $re=Db::name('attention')->where(['noticer'=>$noticer,'followers'=>$followers])->update(['status'=>0]);
            if($re)
            {
                $data['code']=200;
                $data['msg']='修改成功';
            }else{
                $data['code']=500;
                $data['msg']='修改失败';
            }
        }
        return json($data);
    }

    /*
     * 加盟说详情页
     * uid  用户ID
     * mid  帖子目标ID
     * */

    public function carddetail()
    {
        $uid=request()->get('uid');
        $mid=request()->get('mid');

        $tiezi=Db::name('tiezi')->alias('t')->where('t.id',$mid)
            ->join('zhuti z','z.id=t.ztid','LEFT')
            ->join('user u','t.uid=u.id','LEFT')
            ->field('t.imgsrc,t.id,t.uid,t.content,t.title,t.date,z.name,u.nickname,u.headimg')->select();
        //文章点赞数
        $chick=db('dianzan')->field('mid')->where('mid',$mid)->where('status',1)->where('kind',2)->select();
        $chick=count($chick);
        //文章收藏数
        $shoucang=db('shoucang')->field('mid')->where('mid',$mid)->where('status',1)->where('kind',2)->select();
        $shoucang=count($shoucang);
        //我是否关注作者
        $guanzhu='';
        foreach ($tiezi as $k=>$v){
            $guanzhu=Db::name('attention')->where(['noticer'=>$uid,'followers'=>$v['uid'],'status'=>1])->field('status')->find();
            if(!isset($guanzhu['status']))
            {
                $guanzhu['status']=0;
            }
        }
        //评论数
        $pinglun=Db::name('pinglun')->where(['mid'=>$mid,'mkind'=>3])->select();
        $pinglunnum=count($pinglun);
        //评论
        $pingl=Db::name('pinglun')->alias('p')->where(['p.mkind'=>3,'p.mid'=>$mid])
            ->join('user u','u.id=p.uid1')->order('p.id desc')
            ->field('p.id,p.uid1,p.uid2,p.content,p.time,u.nickname,u.headimg')->select();
        foreach ($pingl as $k=>$v){

            $uid2[]['uid2name']=Db::name('user')->where('id',$v['uid2'])->field('nickname')->find();
            $pingl[$k]=array_merge($v,$uid2[$k]);
            $pingl[$k]['time']=date('Y-m-d',$v['time']);
        }
      //我是否收藏 /点赞
        $myshoucang=Db::name('shoucang')->where(['uid'=>$uid,'mid'=>$mid,'kind'=>2])->field('status')->find();
        if(!isset($myshoucang['status']))
        {
            $myshoucang['status']=0;
        }
        $mydianzan=Db::name('dianzan')->where(['uid'=>$uid,'mid'=>$mid,'kind'=>2])->field('status')->find();
        if(!isset($mydianzan['status']))
        {
            $mydianzan['status']=0;
        }
        $data=array(
            'tiezi'=>$tiezi,
            'dianzannum'=>$chick,
            'shoucangnum'=>$shoucang,
            'guanzhu'=>$guanzhu,
            'pinglunnum'=>$pinglunnum,
            'pinglun'=>$pingl,
            'myshoucang'=>$myshoucang,
            'mydianzan'=>$mydianzan
        );
        return json($data);
    }
    /*加盟说个人中心
    uid  用户ID
    */
    public function joinsaid()
    {
        $uid=request()->get('uid');
        $img=Db::name('user')->where('id',$uid)->field('nickname,headimg')->find();
        $mytiez=Db::name('tiezi')->where('uid',$uid)->select();
        $mytiezinum=count($mytiez);

        $myguanzh=Db::name('attention')->where('noticer',$uid)->where('status',1)->select();
        $myguanzhu=count($myguanzh);

        $guanzhum=Db::name('attention')->where('followers',$uid)->where('status',1)->select();
        $guanzhumy=count($guanzhum);

        $tiezi=Db::name('tiezi')->alias('t')
            ->where('t.uid',$uid)
            ->order('t.time desc')
            ->join('user u','t.uid=u.id','LEFT')
            ->join('zhuti z','t.ztid=z.id','LEFT')
            ->field('t.title,t.imgsrc,t.id,z.name,u.nickname,u.headimg')->select();
        foreach ($tiezi as $k=>$v)
        {
            $plrs=Db::name('pinglun')->where(['mkind'=>3,'mid'=>$v['id']])->count('id');
            $tiezi[$k]['count']=$plrs;
        }
        $data=array(
            'ziliao'=>$img,
            'mytiezinum'=>$mytiezinum,
            'myguanzhu'=>$myguanzhu,
            'guanzhumy'=>$guanzhumy,
            'tiezi'=>$tiezi
        );
        return json($data);
    }
    //加盟说关注详情
    //uid  用户ID
    public function attentions()
    {
        $uid=request()->get('uid');
        //我的粉丝
        $followers=Db::name('attention')->alias('a')->where('a.followers',$uid)->where('status',1)
            ->join('user u','u.id=a.noticer')->field('u.id,u.nickname,u.headimg')->select();
        //我的关注
        $noticer=Db::name('attention')->alias('a')->where('a.noticer',$uid)->where('status',1)
            ->join('user u','u.id=a.followers')->field('u.id,u.nickname,u.headimg')->select();
        $data=array(
            'followers'=>$followers,
            'noticer'=>$noticer
        );
        return json($data);
    }



}