<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 12:06
 */

namespace app\model;
use think\Db;

class Member extends Base{
    protected $pk = 'id';
    protected $table = 'fanxiang_user';
    public function merchart()
    {
        $merchart=Db::name('admin')->alias('a')->join('pinpai p','a.pid=p.id')
            ->field('p.name,a.*')->select();
        foreach ($merchart as $k=>$v){
            $merchart[$k]['create_time']=date('Y-m-d',$merchart[$k]['create_time']);
            if(isset($merchart[$k]['lastTime'])&&$merchart[$k]['lastTime']!=0)
            {
                $merchart[$k]['lastTime']=date('Y-m-d',$merchart[$k]['lastTime']);
            }
        }
        return $merchart;
    }
    public function merchart_edit($id)
    {
        $merchart_edit=Db::name('admin')->alias('a')
            ->where('a.id',$id)
            ->join('pinpai p','a.pid=p.id')
            ->field('p.name,a.id,a.member,a.password,a.pid,a.lastTime,a.status,a.expire_time,a.create_time')->select();
        return $merchart_edit;
    }
    public function search($search)
    {
        $merchart=Db::name('admin')->alias('a')
            ->where('name','like', '%'.$search.'%')->join('pinpai p','a.pid=p.id')
            ->field('p.name,a.*')->select();
        foreach ($merchart as $k=>$v){
            $merchart[$k]['create_time']=date('Y-m-d',$merchart[$k]['create_time']);
            if(isset($merchart[$k]['lastTime'])&&$merchart[$k]['lastTime']!=0)
            {
                $merchart[$k]['lastTime']=date('Y-m-d',$merchart[$k]['lastTime']);
            }
        }
        return $merchart;
    }
    public function message()
    {
        $message=Db::name('message')->order('id desc')->select();
        foreach ($message as $k=>$v)
        {
            $message[$k]['time']=date('Y-m-d H:i',$v['time']);
        }
        return $message;
    }
    public function message_set($data)
    {
     $res=Db::name('message')->insert($data);
     return $res;
    }

}