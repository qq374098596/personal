<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/5
 * Time: 16:11
 */

namespace app\model;


use think\Db;

class Comment extends Base{
    protected $pk = 'id';
    protected $table = 'fanxiang_pinglun';
    public function getPageSelect($where = '',$order='',$pageSize = 15){
        $join = [
            ['fanxiang_user w','a.uid1=w.id','LEFT'],
            ['fanxiang_user c','a.uid2=c.id','LEFT'],
        ];
       $res = Db::table('fanxiang_pinglun')->alias('a')->field('a.*,c.nickname nickname2, c.name name2,w.nickname nickname1,w.name name1')->join($join)
           ->paginate($pageSize);
       return $res;
    }
    public function getPingLunUserById($id){
        $sql = 'select a.*,b.nickname as user1,c.nickname as user2 from fanxiang_pinglun as a left join fanxiang_user as b on a.uid1=b.id left join fanxiang_user as c on a.uid2=c.id where a.mid = ? and a.mkind=1';
        $res = Db::query($sql,[$id]);
        return $res ? $res[0] : [];
    }
    public function getPageSelectWhere($where = [],$order='',$pageSize = 25){
        $join = [
            ['fanxiang_user w','a.uid1=w.id','LEFT'],
            ['fanxiang_user c','a.uid2=c.id','LEFT'],
        ];
        $res = Db::table('fanxiang_pinglun')->alias('a')->field('a.*,c.nickname nickname2, c.headimg headimg2, c.name name2,w.nickname nickname1,w.headimg headimg1,w.name name1')->join($join)
            ->where($where)->order($order)->paginate($pageSize);
        return $res;
    }
}