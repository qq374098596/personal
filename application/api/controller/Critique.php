<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 6:13 PM
 */

namespace app\api\controller;


use app\model\Collection;
use app\model\DianPing;

class Critique extends Api{
    protected $db_critique;
    protected $db_turn;
    protected $db_hangye;
    protected $db_dianping;
    protected $db_comment;
    protected $db_collection;
    protected $db_product;
    public function _initialize(){
        parent::_initialize();
        $this->db_critique = new \app\model\Critique();
        $this->db_turn = new \app\model\Turns();
        $this->db_hangye = new \app\model\Hangye();
        $this->db_dianping = new DianPing();
        $this->db_comment = new \app\model\Comment();
        $this->db_collection = new Collection();
        $this->db_product= new \app\model\Product();
    }
    public function index(){
        $data_critiques =  $this->db_critique->getCritiques();
        $data_imgs = $this->db_turn->getTurnByPage('pinpaidianping');
        $data_hangye =  $this->db_hangye->getHangYes();
        $data_critiques = $this->db_product->arraySortByKey($data_critiques,'fen');
        $res = array(
            'turns' => $data_imgs,
            'dianpinglist' => $data_critiques,
            'hangye' => $data_hangye,
            'code' => 200
        );
        return $this->jsonp($res);
    }
    public function detail(){
        $id = input('id', 0);
        $uid = input('uid', 0);
        $data_pinpai = $this->db_critique->getCritiqueById($id);
        $data_dianping = $this->db_dianping->getDianPingById($id);
        foreach ($data_dianping as $key => $value) {
            $pinglun =  $this->db_comment->getPingLunUserById($value['id']);
            $pinglun = $this->getTree($pinglun);
            $data_dianping[$key]['pinglun'] = $pinglun;
            $data_dianping[$key]['time'] = date('Y-m-d', $value['time']);
            $data_dianping[$key]['imglist'] = json_decode($value['imglist']);
        }
        foreach ($data_dianping as $key => $value){
            $stat['zannum']=db('commentary')->where(['pid'=>$value['id'],'kind'=>1,'status'=>1])->count();
            $data_dianping[$key]=array_merge($value,$stat);
        }
        foreach ($data_dianping as $k => $v)
        {
            $status['mystatus']=db('commentary')->where(['pid'=>$v['id'],'kind'=>1,'myid'=>$uid])->field('status')->find();
            if(!isset($status['mystatus']['status']))
            {
                $status['mystatus']['status']=0;
            }
            $data_dianping[$k]=array_merge($v,$status);
        }
        foreach ($data_dianping as $k => $v)
        {
            $counts[]['counts']=db('pinglun')->where(['mkind'=>1,'mid'=>$v['id']])->count();
            $data_dianping[$k]['counts']=$counts[$k]['counts'];
        }


        $shoucang =  $this->db_collection->getCollectionByUidId($data_pinpai ? $data_pinpai['id'] : 0,$uid);
        if ($shoucang) {
            $shoucang = $shoucang['status'];
        }else{
            $shoucang = 0;
        }
        $jiamengdetila=db('turnimgs')->where('page','jiamengdetila')->field('src')->find();
        $jiamengdetila['src'] = json_decode($jiamengdetila['src']);
        $res = array(
            'pinpai' => $data_pinpai,
            'dianping' => $data_dianping,
            'shoucang'=>$shoucang,
            'banner'=>$jiamengdetila,
            'code' => 200
        );
        return $this->jsonp($res);
    }
    public function gethangye()
    {
        $hangye=db('hangye')->select();
        $data=array(
          'hangye'=>$hangye
        );
        return json($data);
    }
    public function getpinpai()
    {
        $res=request()->get('name');
        $pinpai=db('pinpai')->where('name','like','%'.$res.'%')->field('id,name,imgsrc,hangyeid')->select();
        if(empty($pinpai)) return $this->jsonp('未找到相关数据');
        $arr = [];
        foreach ($pinpai as $v){
            $arr[$v['id']] = $v;
        }
        $data=array(
            'pinpai'=>$arr,
            'code'=>200
        );
        return $this->jsonp($data);
    }
}