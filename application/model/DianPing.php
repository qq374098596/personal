<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/12
 * Time: 10:59 AM
 */

namespace app\model;


use think\Db;

class DianPing extends Base {
    protected $pk = 'id';
    protected $table = 'fanxiang_dianping';
    public function getDianPingById($id){
        $sql = 'select a.*,b.nickname,b.headimg,b.id as xxuid1,a.company from fanxiang_dianping as a left join fanxiang_user as b on a.uid=b.id 
          where a.pinpaiid = ? order by a.id desc';
        $res = Db::query($sql,[$id]);
        return $res;
    }
    public function getDianPingByWhere($where=''){
        $sql = 'select a.*,b.nickname,b.headimg from fanxiang_dianping a left join fanxiang_user b on a.uid=b.id ' .$where;
        return Db::query($sql);
    }
    public function getDianPingByIdOne($id){
        $sql = 'select a.*,b.nickname,b.headimg,b.id as xxuid1,a.company from fanxiang_dianping as a left join fanxiang_user as b on a.uid=b.id 
          where a.id = ? order by a.id desc';
        $res = Db::query($sql,[$id]);
        return $res ? $res[0] : [];
    }
}