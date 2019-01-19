<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 5:59 PM
 */

namespace app\api\controller;



use app\model\Product;

class Comment extends Api{
    protected $db_dianping;
    protected $db_comment;
    protected $db_product;
    public function _initialize(){
        parent::_initialize();
        $this->db_dianping = new \app\model\DianPing();
        $this->db_comment = new \app\model\Comment();
        $this->db_product = new Product();
    }

    public function search(){
        $hangye = input('hangye');
        $paixu = input('paixu');
        $text = input('text');
        $where = ' where a.kind = 1 ';
        $sxMin = input('min');
        $sxMax = input('msx');
        if ($sxMin && $sxMax) {
            $where .= "and a.jiamengfei<='".$sxMax."' and a.jiamengfei>='".$sxMin."'";
        }
        if ($text) {
            $where .= " and a.name like '%".$text."%'";
        }
        if ($hangye) {
            $where .= " and a.hangyeid = '".$hangye."'";
        }
        $order = '';
        if ($paixu == 'shijian') {
            $order .= " order by a.time desc";
        }elseif ($paixu == 'redu') {
            $order .= " order by pinglun_count desc";
        }
        $data_dianpinglist =  $this->db_product->getProductSearch($where, $order);
        $res = array(
            'dianpinglist' => $data_dianpinglist,
            'code' => 200
        );
        return $this->jsonp($res);
    }

    public function getdianpinglist(){
        $pid = input('pid', 0);
        $condition = input('condition');
        $where = ' where a.pinpaiid = '.$pid;
        switch ($condition) {
            case 'haoping':
                $where .= ' and a.pingfen >= 5';
                break;
            case 'chaping':
                $where .= ' and a.pingfen < 5';
                break;
        }
        $dianpinglist = $this->db_dianping->getDianPingByWhere($where);
        foreach ($dianpinglist as $key => $value) {
            $dianpinglist[$key]['imglist'] = json_decode($value['imglist']);
            $pinglun =  $this->db_comment->getPingLunUserById($value['id']);
            $pinglun = $this->getTree($pinglun);
            $dianpinglist[$key]['pinglun'] = $pinglun;
        }
        $res = array(
            'dianpinglist' => $dianpinglist,
            'code' => 200
        );
        return $this->jsonp($res);
    }

    /****
     * 写点评方法
     */
    public function savedianping(){
        $data = [];
        $data['uid'] = input('uid');
        $data['pingfen'] = input('starl');
        $data['jiamengfei'] = input('jiamengfei');
        $data['zongchengben'] = input('zongchengben');
        $data['date'] = input('date');
        $data['city'] = input('city');
        $data['yueyingshou'] = input('yueyingshou');
        $data['content'] = input('content');
        $data['status'] = input('status');
        $data['imglist'] = input('upload_picture_list');
        $data['pinpaiid'] = input('pinpaiid');
        $data['pinpainame'] = input('pinpainame');
        $data['company'] = input('company');
        $data['time'] = time();
        $data['imglist'] = json_decode($data['imglist']);
        foreach ($data['imglist'] as $key => $value) {
            $data['imglist'][$key]=$value;
        }
        $data['imglist'] = json_encode($data['imglist']);
        $res_data = $this->db_dianping->db_insert($data);
        $res = [
            'msg' => 'ok',
            'id' => $res_data,
            'code' => 200
        ];
        return $this->jsonp($res);
    }
    /****
     * 评论详情页码
     */
    public function comment_list(){
        $id = input('id'); //点评id
        $uid = input('uid'); //要回复人uid
        //$uid2 = input('uid2'); //回复uid
        $dianping = $this->db_dianping->getDianPingByIdOne($id);
        if (!empty($dianping) && !empty($dianping['imglist'])) $dianping['imglist'] = json_decode($dianping['imglist']);
        if (!empty($dianping) && !empty($dianping['time'])) $dianping['time'] = date('Y-m-d',$dianping['time']);
        //查询评论数据
        $where = [];
        if (!empty($dianping) && !empty($dianping['uid'])){
            $where['a.uid2'] = $dianping['uid'];
        }
        if (!empty($id)){
            $where['a.mid'] = $id;
        }
        $order = 'a.id desc';
        $comments = $this->db_comment->getPageSelectWhere($where,$order)->each(function ($items){
            $items['time'] = date('Y-m-d', $items['time']);
            return $items;
        });
        $item=$comments->items();
        $stat=db('commentary')->where(['pid'=>$dianping['id'],'kind'=>1,'status'=>1])->count();
        $dianping['zannum']=$stat;
        $status=db('commentary')->where(['pid'=>$dianping['id'],'kind'=>1,'myid'=>$uid])->field('status')->find();
        if(!isset($status['status'])) {
            $status['status']=0;
        }
        $dianping['stat']=$status['status'];

        $res = [
            'dianping' => $dianping,
            'comments' => $item,
            'code' => 200
        ];
        return $this->jsonp($res);
    }
}