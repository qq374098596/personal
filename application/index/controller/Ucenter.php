<?php
namespace app\index\Controller;
use think\Controller;
use think\Db;


class Ucenter extends Controller{
	protected $db;
	protected function _initialize(){
		parent::_initialize();
		$this->db = new \app\index\model\Ucenter();
	}

	public function index(){
		if (!session('user')) {
			$this->redirect(url('/'));
		}
		$uid = session('user.id');
        //收藏数量
        $collection = Db::name('shoucang')->where(['uid'=>$uid,'status'=>1])->count();
		//帖子数量
		$cardCount = $this->db->getCount('tiezi','uid='.$uid);
		//积分数
		$integral = $this->db->getSum('jifen','jifen','uid='.$uid);
		//点评数量
		$review = $this->db->getCount('dianping',"uid='".$uid."'");
		//var_dump($review);die;
		$sessions = session('user');
		$sessions['reviewNum'] = $review;
		session('user',$sessions);
		
		//诊断记录
		$diagnosis = Db::name('zhenduan')->where(['uid'=>$uid,])->field('pinpai,jsfs,time,pid')->select();
		foreach ($diagnosis as $k => $v) {
            $diagnosis[$k]['time'] = date('Y-m-d',$v['time']);
        }
        if($diagnosis) {
            foreach ($diagnosis as $k => $v) {
                //  注：诊断记录写入的数据没有限制品牌名称没有写入品牌ID，这里根据品牌ID查询不到数据，使用array_merge报错

                if ($v['pinpai'] != '') {
                    $imgs[] = Db::name('pinpai')->where('name','like',$v['pinpai'])->field('imgsrc')->find();
                    if(!isset($imgs[$k]['imgsrc'])){
                        $imgs[$k]['imgsrc']="/static/Home/img/index/logo.png";
                    }
                    $diagnosis[$k] = array_merge($v, $imgs[$k]);
                }else{
                    $diagnosis[$k]['imgsrc']='';
                }
            }

        }


        //我的项目
        $myproject=Db::name('pinpai')->where('u_id',$uid)->field('id,name,imgsrc')->select();
        //状态
		$status=Db::name('dianping')->where('uid',$uid)->field('status')->select();
        if($myproject&&$status){
            foreach ($myproject as $k=>$v){
                $myproject[$k]=array_merge($v,$status[$k]);
            }
        }

        //严选项目
        $strictlyproject=Db::name('pinpai')->where(['kind'=>2,'u_id'=>$uid])->field('id,name,imgsrc')->select();
        if($strictlyproject&&$status){
            foreach ($strictlyproject as $k=>$v){
                $strictlyproject[$k]=array_merge($v,$status[$k]);
            }
        }
        //项目收藏

        $project=$this->getCollect('pinpai','1');
//        if ($project){
//            foreach ($project as $k=>$v){
//                 $project[$k]=array_merge($v,$status[$k]);
//            }
//        }
        //帖子收藏
        $tiezi=$this->getCollect('tiezi','2');
        if ($tiezi){
            foreach ($tiezi as $k => $v) {
                $tiezi[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
                $users[]=Db::name('user')->where('id',$tiezi[$k]['uid'])->field('nickname,headimg')->find();
            }
            if ($users){
                foreach ($tiezi as $k=>$v){
                    $tiezi[$k]=array_merge($v,$users[$k]);
                }
            }
        }
        //收藏帖子回复数
        if ($tiezi){
            foreach($tiezi as $k=>$v)
            {
                $count[]['count'] = Db::name('pinglun')->where(['mid'=>$tiezi[$k]['id'],'mkind'=>3,'gid'=>0])->count();
                $tiezi[$k]=array_merge($v,$count[$k]);
            }
        }
        //故事收藏
        $gushi=$this->getCollect('gushi','3');
        if ($gushi){
            foreach ($gushi as $k=>$v){
                $gushi[$k]['time']=date('Y-m-d',$v['time']);
            }
        }
        //导师收藏
        $daoshi=$this->getCollect('daoshi','4');


        //dump($yxdianping);die;
        $this->assign('daoshi',$daoshi);
        $this->assign('gushi',$gushi);
        $this->assign('tiezi',$tiezi);
        $this->assign('project',$project);
        $this->assign('strictlyproject',$strictlyproject);
        $this->assign('myproject',$myproject);
		$this->assign('review',$review);
		// $this->assign('card',$card);
		$this->assign('diagnosis',$diagnosis);
		$this->assign('integral',$integral[0]['sum']);
		$this->assign('cardCount',$cardCount);
		$this->assign('collection',$collection);
		$this->assign('title','个人中心');
		return $this->fetch();
	}

	/**
	 * 更换头像
	 * @return [type] [description]
	 */
	public function avatar()
	{
		$avatar = $this->db->avatar($_POST);
	}

	/**
	 * 修改密码
	 * @return [type] [description]
	 */
	public function changePwd()
	{
		$changePwd = $this->db->changePwd($_POST);
		return $changePwd;
	}

	// public function mailer()
	// {
	// 	var_dump($_POST);die;
	// }

	/**
	 * 用户修改资料
	 * @return [type] [description]
	 */
	public function userInfo()
	{
		$userInfo = $this->db->userInfo($_POST);
		return $userInfo;
	}
	//获取收藏
	public function getCollect($table,$kind)
    {
        $id = session('user.id');
        $mid=Db::name('shoucang')->where(['uid'=>$id,'kind'=>$kind,'status'=>1])->field('mid')->select();
        if ($mid){
            foreach ($mid as $k)
            {
                $collect[]=Db::name($table)->where('id',$k['mid'])->find();

            }
            return $collect;
        }

    }
    /**
     * 有分页的数据
     * @return [type] [description]
     */
    public function jsonAll(){
        $uid = session('user.id');
        //我的主贴
        $mytiezi=Db::name('tiezi')->where('uid',$uid)->field('id,title,time,read_num')
            ->order('id','desc')->paginate(1)->each(function($item, $key){
                $item['time']=date('Y-m-d H:i:s',$item['time']);
                return $item;
            });
        if($mytiezi){
            foreach ($mytiezi as $k=>$v)
            {
                $user[]=Db::name('user')->where('id',$uid)->field('nickname,headimg')->find();
                $mytiezi[$k]=array_merge($v,$user[$k]);
            }
            foreach($mytiezi as $k=>$v)
            {
                $countt[]['count'] = Db::name('pinglun')->where(['mid'=>$mytiezi[$k]['id'],'mkind'=>3,'gid'=>0])->count();
                $mytiezi[$k]=array_merge($v,$countt[$k]);
            }
        }

        //我的回帖
        $sessions = session('user');
        $reply=Db::name('pinglun')->where(['uid1'=>$sessions['id'],'mkind'=>3])->field('content,time,mid')
            ->paginate(2)->each(function($item, $key){
            $item['time']=date('Y-m-d H:i:s',$item['time']);
            return $item;
        });
        if($reply){
            foreach ($reply as $k=>$v){
                $titl[]=Db::name('tiezi')->where('id',$v['mid'])->field('title,uid')->find();
                $reply[$k]=array_merge($v,$titl[$k]);
            }
            foreach ($reply as $k=>$v){
                $username[]=Db::name('user')->where('id',$v['uid'])->field('headimg,nickname')->find();
                $reply[$k]=array_merge($v,$username[$k]);
            }
        }

        //项目点评
        $dianping1=Db::name('dianping')->where('uid',$uid)->buildSql();
        $dianping=Db::name('pinpai')->alias('a')->join([$dianping1=> 'w'], 'a.id = w.pinpaiid')
            ->join('hangye h','a.hangyeid = h.id')
            ->where('kind',1)->paginate(1);
        //严选点评
        $yxdianping1=Db::name('dianping')->where('uid',$uid)->buildSql();
        $yxdianping=Db::name('pinpai')->alias('a')->join([$yxdianping1=> 'w'], 'a.id = w.pinpaiid')
            ->join('hangye s','a.hangyeid = s.id')
            ->where('kind',2)->paginate(1);
        //我的咨询
        $advice1=Db::name('advice')->where('uid',$uid)->field('phone,time,pid')->buildSql();
        $advice=Db::name('pinpai')->alias('p')->join([$advice1=> 'a'],'a.pid=p.id')
            ->field('p.name,p.imgsrc,a.phone,a.time')
            ->paginate()->each(function($item, $key){
            $item['time']=date('Y-m-d',$item['time']);
            return $item;
        });

        //咨询回复
        $getadvice1=Db::name('advice')->where(['uid'=>$uid])->where('reply','not null')->buildSql();
        $getadvice=Db::name('pinpai')->alias('s')->join([$getadvice1=> 'd'],'d.pid=s.id')
            ->field('s.name,s.imgsrc,d.message,d.replytime,d.reply')
            ->paginate()->each(function($item, $key){
                $item['replytime']=date('Y-m-d',$item['replytime']);
                return $item;
            });

        $data = array(
            'mytiezi' => $mytiezi,
            'reply' =>$reply,
            'yxdianping' =>$yxdianping,
            'dianping' =>$dianping,
            'advice' =>$advice,
            'getadvice'=>$getadvice

        );
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
//分页请求地址
    public function page(){
        $nowpage=input('param.');
        $options=[
            'page'=>$nowpage['page']
        ];
        $uid = session('user.id');
        //我的主贴请求
        $mytiezi=Db::name('tiezi')->where('uid',$uid)->field('id,title,time,read_num')
            ->order('id','desc')->paginate(1,false,$options)->each(function($item, $key){
                $item['time']=date('Y-m-d H:i:s',$item['time']);
                return $item;
            });
        if($mytiezi){
            foreach ($mytiezi as $k=>$v)
            {
                $user[]=Db::name('user')->where('id',$uid)->field('nickname,headimg')->find();
                $mytiezi[$k]=array_merge($v,$user[$k]);
            }
            foreach($mytiezi as $k=>$v)
            {
                $countt[]['count'] = Db::name('pinglun')->where(['mid'=>$mytiezi[$k]['id'],'mkind'=>3,'gid'=>0])->count();
                $mytiezi[$k]=array_merge($v,$countt[$k]);
            }
        }

        //我的回帖
        $sessions = session('user');
        $reply=Db::name('pinglun')->where(['uid1'=>$sessions['id'],'mkind'=>3])->field('content,time,mid')
            ->paginate(2,false,$options)->each(function($item, $key){
                $item['time']=date('Y-m-d H:i:s',$item['time']);
                return $item;
            });
        if($reply){
            foreach ($reply as $k=>$v){
                $titl[]=Db::name('tiezi')->where('id',$v['mid'])->field('title,uid')->find();

                $reply[$k]=array_merge($v,$titl[$k]);
            }
            foreach ($reply as $k=>$v){
                $username[]=Db::name('user')->where('id',$v['uid'])->field('headimg,nickname')->find();
                $reply[$k]=array_merge($v,$username[$k]);
            }
        }

        //项目点评
        $dianping1=Db::name('dianping')->where('uid',$uid)->buildSql();
        $dianping=Db::name('pinpai')->alias('a')->join([$dianping1=> 'w'], 'a.id = w.pinpaiid')
            ->join('hangye h','a.hangyeid = h.id')
            ->where('kind',1)->paginate(1,false,$options);
        //严选点评
        $yxdianping1=Db::name('dianping')->where('uid',$uid)->buildSql();
        $yxdianping=Db::name('pinpai')->alias('a')->join([$yxdianping1=> 'w'], 'a.id = w.pinpaiid')
            ->join('hangye s','a.hangyeid = s.id')
            ->where('kind',2)->paginate(1,false,$options);
        $data = array(
            'mytiezi' => $mytiezi,
            'reply' => $reply,
            'yxdianping'=>$yxdianping,
            'dianping'=>$dianping
        );
        return $data;
    }
}
?>