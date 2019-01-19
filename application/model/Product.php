<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3 0003
 * Time: 11:36
 */

namespace app\model;


use think\Db;

class Product extends Base{
    protected $pk = 'id';
    protected $table = 'fanxiang_pinpai';
    //普通品牌
    public function product()
    {
        $product=Db::name('pinpai')->alias('a')
            ->join('hangye h','a.hangyeid=h.id')->where('kind',1)
            ->field('a.id,a.name,a.imgsrc,a.hangyeid,a.company,a.order_id,a.jiamengfei,a.sort,a.is_index,h.hyname')
            ->paginate(5);
        foreach ($product as $k=>$v){
            $dianping=Db::name('dianping')->where('pinpaiid',$v['id'])->field('city')->count();
            $v['count']=$dianping;
            $product[$k]=$v;
        }
        return $product;
    }
    public function getProducts(){
        $sql = "select
                  a.id,
                  a.name,
                  a.imgsrc,
                  a.tel,
                  a.kind,
                  a.company,
                  a.jiamengfei,
                  a.jiamengshop,
                  a.zhiyingshop,
                  a.bgcolor,
                  (select sum(b.pingfen) from fanxiang_dianping b where a.id = b.pinpaiid) allpingfen,
                  (select count(1) from fanxiang_dianping b where a.id = b.pinpaiid) dianping_count,
                  round(((select count(1) from fanxiang_dianping b where a.id = b.pinpaiid and b.pingfen >= 5)/(select count(1) from fanxiang_dianping b where a.id = b.pinpaiid))*100) fen,
                  (select count(1) from fanxiang_dianping b, fanxiang_pinglun c where b.id = c.mid and a.id = b.pinpaiid and c.mkind = 1 and c.gid = 0) pinglun_count,
                  (select id from fanxiang_hangye h where a.hangyeid = h.id) hyid
                from fanxiang_pinpai a
                where a.kind = 2 order by id desc;";
        return Db::query($sql);

    }
    public function getProductById($id){
        $sql = "select
                  a.id,
                  a.name,
                  a.company,
                  a.imgsrc,
                  a.weixinurl,
                  a.tel,
                  a.kind,
                  (select sum(b.pingfen) from fanxiang_dianping b where a.id = b.pinpaiid) allpingfen,
                  (select count(1) from fanxiang_dianping b where a.id = b.pinpaiid) dianping_count,
                  round(((select count(1) from fanxiang_dianping b where a.id = b.pinpaiid and b.pingfen >= 5)/(select count(1) from fanxiang_dianping b where a.id = b.pinpaiid))*100) fen,
                  (select count(1) from fanxiang_dianping b, fanxiang_pinglun c where b.id = c.mid and a.id = b.pinpaiid and c.mkind = 1 and c.gid = 0) pinglun_count
                from fanxiang_pinpai a
                where a.kind = 2 and a.id = ".$id.";";
        return Db::query($sql);
    }
    public function getProductSearch($where = '', $order = ''){
        $sql = "select
                  a.id,
                  a.name,
                  a.imgsrc,
                  a.tel,
                  a.kind,
                  a.company,
                  a.jiamengfei,
                  a.jiamengshop,
                  a.zhiyingshop,
                  (select sum(b.pingfen) from fanxiang_dianping b where a.id = b.pinpaiid) allpingfen,
                  (select count(1) from fanxiang_dianping b where a.id = b.pinpaiid) dianping_count,
                  round(((select count(1) from fanxiang_dianping b where a.id = b.pinpaiid and b.pingfen > 5)/(select count(1) from fanxiang_dianping b where a.id = b.pinpaiid))*100) fen,
                  (select count(1) from fanxiang_dianping b, fanxiang_pinglun c where b.id = c.mid and a.id = b.pinpaiid and c.mkind = 1 and c.gid = 0) pinglun_count
                from fanxiang_pinpai a ".$where.$order;
        return Db::query($sql);
    }
    //修改普通品牌视图
    public function edit($id)
    {
        $product=Db::name('pinpai')->alias('a')
            ->join('hangye h','a.hangyeid=h.id')
            ->where('a.id',$id)
            ->field('a.id,a.name,a.imgsrc,a.hangyeid,a.company,a.jiamengfei,a.order_id,a.sort,h.hyname')
            ->select();
        return $product;
    }
    //严选品牌视图
    public function strict()
    {
        $product=Db::name('pinpai')->alias('a')
            ->join('hangye h','a.hangyeid=h.id')->where('kind',2)
            ->field('a.id,a.name,a.imgsrc,a.hangyeid,a.tel,a.jiamengshop,a.order_id,a.zhiyingshop,a.company,a.jiamengfei,a.is_index,a.sort,h.hyname')
            ->select();
        foreach ($product as $k=>$v){
            $dianping=Db::name('dianping')->where('pinpaiid',$v['id'])->field('city')->count();
            $v['counto']=$dianping;
            $product[$k]=$v;
        }
        foreach ($product as $k=>$v){
            $dianping=Db::name('wenda')->where('pid',$v['id'])->field('status')->count();
            $v['countoi']=$dianping;
            $product[$k]=$v;
        }
        return $product;
    }
    //用户咨询
    public function advice($id)
    {
        $advice=Db::name('advice')->where('pid',$id)->order('time desc')->select();
        foreach ($advice as $k=>$v)
        {
            $pinpai=Db::name('pinpai')->where('id',$v['pid'])->field('name')->find();
            $v['name']=$pinpai['name'];
            $advice[$k]=$v;
            $advice[$k]['time']=date('Y-m-d',$advice[$k]['time']);
            if($advice[$k]['replytime'])
            {
                $advice[$k]['replytime']=date('Y-m-d',$advice[$k]['replytime']);
            }
        }
        return $advice;
    }
    //修改严选品牌
    public function editstrict($id)
    {
        $product=Db::name('pinpai')->alias('a')
            ->join('hangye h','a.hangyeid=h.id')
            ->where('a.id',$id)
            ->field('a.id,a.name,a.imgsrc,a.hangyeid,a.ppimglist,a.yueyingshou,a.jianjie,a.shifang,a.prospect,a.tel,a.jiamengshop,a.zhiyingshop,a.company,a.order_id,a.jiamengfei,a.sort,h.hyname,a.investment')
            ->select();
        return $product;
    }
    //问答
    public function questions($id)
    {
        $questions=Db::name('wenda')->where('pid',$id)->select();
        foreach ($questions as $k=>$v)
        {
            $user=Db::name('user')->where('id',$v['uid'])->field('nickname')->find();
            $questions[$k]['nickname']=$user['nickname'];
            $questions[$k]['time']=date('Y-m-d',$questions[$k]['time']);
        }
        return $questions;
    }
    //删除
    public function del($table,$id)
    {
        $re=Db::name($table)->where('id',$id)->delete();
        return $re;
    }
    //二维数组排序倒序
    public function arraySortByKey($array=array(), $key='', $asc = false)
    {
        $result = array();
        // 整理出准备排序的数组
        foreach ( $array as $k => &$v ) {
            $values[$k] = isset($v[$key]) ? $v[$key] : '';
        }
        unset($v);
        // 对需要排序键值进行排序
        $asc ? asort($values) : arsort($values);
        // 重新排列原有数组
        foreach ( $values as $k => $v ) {
            $result[] = $array[$k];
        }

        return $result;
    }
}