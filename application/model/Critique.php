<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/11
 * Time: 6:04 PM
 */

namespace app\model;

use think\Db;

/***
 * Class Critique点评
 * @package app\model
 */
class Critique extends Base{
    protected $pk = 'id';
    protected $table = 'fanxiang_tiezi';
    public function getCritiques(){
        $sql = "select
                  a.id,
                  a.name,
                  a.imgsrc,
                  a.tel,
                  a.kind,
                  a.bgcolor,
                  (select sum(b.pingfen) from fanxiang_dianping b where a.id = b.pinpaiid) allpingfen,
                  (select count(1) from fanxiang_dianping b where a.id = b.pinpaiid) dianping_count,
                  round(((select count(1) from fanxiang_dianping b where a.id = b.pinpaiid and b.pingfen >= 5)/(select count(1) from fanxiang_dianping b where a.id = b.pinpaiid))*100) fen,
                  (select count(1) from fanxiang_dianping b, fanxiang_pinglun c where b.id = c.mid and a.id = b.pinpaiid and c.mkind = 1 and c.gid = 0) pinglun_count
                from fanxiang_pinpai a
                where a.kind = 1 order by a.id desc;";
        return Db::query($sql);
    }
    public function getCritiqueById($id){
        $sql = "select
                  a.id,
                  a.name,
                  a.company,
                  a.imgsrc,
                  a.tel,
                  a.kind,
                  (select sum(b.pingfen) from fanxiang_dianping b where a.id = b.pinpaiid) allpingfen,
                  (select count(1) from fanxiang_dianping b where a.id = b.pinpaiid) dianping_count,
                  round(((select count(1) from fanxiang_dianping b where a.id = b.pinpaiid and b.pingfen >= 5)/(select count(1) from fanxiang_dianping b where a.id = b.pinpaiid))*100) fen,
                  (select count(1) from fanxiang_dianping b, fanxiang_pinglun c where b.id = c.mid and a.id = b.pinpaiid and c.mkind = 1) pinglun_count
                from fanxiang_pinpai a 
                where a.kind = 1 and a.id = ?";
        $res = Db::query($sql,[$id]);
        return  $res ? $res[0] : [];
    }
    public function getCritiquesLimit($limit=5){
        $sql = "select
                      a.id,
                      a.name,
                      a.imgsrc,
                      a.tel,
                      a.kind,
                      a.bgcolor,
                      (select sum(b.pingfen) from fanxiang_dianping b where a.id = b.pinpaiid) allpingfen,
                      (select count(1) from fanxiang_dianping b where a.id = b.pinpaiid) dianping_count,
                      round(((select count(1) from fanxiang_dianping b where a.id = b.pinpaiid and b.pingfen >= 5)/(select count(1) from fanxiang_dianping b where a.id = b.pinpaiid))*100) fen,
                      (select count(1) from fanxiang_dianping b, fanxiang_pinglun c where b.id = c.mid and a.id = b.pinpaiid and c.mkind = 1 and c.gid = 0) pinglun_count
                    from fanxiang_pinpai a
                    where a.kind = 1 and a.order_id = 1 order by a.id desc limit ".$limit;
        return Db::query($sql);
    }
}