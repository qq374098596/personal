<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25 0025
 * Time: 13:23
 */

namespace app\store\model;
use think\Model;

class Usermessages extends Model
{
    //点评
    public function Review($pid)
    {
        $re=db('dianping')->where('pinpaiid',$pid)->order('time desc')->field('id,content,time,pingfen,uid,status')->select();
        if($re) {
            foreach ($re as $k=>$v){
                $re[$k]['time'] = date('Y-m-d',$v['time']);
            }
            foreach ($re as $k=>$v) {
                $user[] = db('user')->field('nickname')->where('id', $v['uid'])->find();
                $re[$k]=array_merge($v,$user[$k]);
            }
        }
        return $re;
    }
    //点评查询
    public function ReviewSearch($pid,$evaluate='',$content='')
    {
        if($content!=''){
            $map['content']=array('like','%'.$content.'%');
        }
        if($evaluate!=''){
            if($evaluate==1){
                $map['pingfen']=array('lt',4);
            }
            if($evaluate==2){
                $map['pingfen']=array('in','4,5');
            }
            if($evaluate==3){
                $map['pingfen']=array('gt',5);
            }
        }
        $re=db('dianping')->where('pinpaiid',$pid)->where($map)->field('content,time,pingfen,uid,status')->select();

        if($re) {
            foreach ($re as $k=>$v){
                $re[$k]['time'] = date('Y-m-d',$v['time']);
            }
            foreach ($re as $k=>$v) {
                $user[] = db('user')->field('nickname')->where('id', $v['uid'])->find();
                $re[$k]=array_merge($v,$user[$k]);
            }
        }
        return $re;
    }

    public function addreview($data)
    {
        $ins['uid1'] = '100'.$data['uid1'];
        $ins['uid2'] = 0;
        $ins['mid'] = $data['mid'];
        $ins['mkind'] = 4;
        $ins['gid'] = 0;
        $ins['content'] = $data['content'];
        $ins['time'] = time();
        $addreview = db('pinglun')->insert($ins);
        if (!$addreview) {
            return ['s'=>500,'error'=>'回复失败！'];
        }else{
            return ['s'=>200,'msg'=>'回复成功！'];
        }
    }

    //问答
    public function Question($pid)
    {
        $re=db('wenda')->where('pid',$pid)->select();
        if($re){
            foreach ($re as $k=>$v){
                $re[$k]['time'] = date('Y-m-d',$v['time']);
            }
            foreach ($re as $k=>$v) {
                $user[] = db('user')->field('nickname')->where('id', $v['uid'])->find();
                $re[$k]=array_merge($v,$user[$k]);
            }
        }
        return $re;
    }
    //问答查询
    public function QuestionSearch($pid,$content)
    {
        $map['content']=array('like','%'.$content.'%');
        $re=db('wenda')->where('pid',$pid)->where($map)->select();
        if($re){
            foreach ($re as $k=>$v){
                $re[$k]['time'] = date('Y-m-d',$v['time']);
            }
            foreach ($re as $k=>$v) {
                $user[] = db('user')->field('nickname')->where('id', $v['uid'])->find();
                $re[$k]=array_merge($v,$user[$k]);
            }
        }
        return $re;
    }
    //咨询
    public function Advice($pid)
    {
        $re=db('advice')->where('pid',$pid)->order('time desc')->select();
        if($re) {
            foreach ($re as $k => $v)
            {
                $user[] = db('user')->field('nickname')->where('id', $v['uid'])->find();
                $re[$k]=array_merge($v,$user[$k]);
                $re[$k]['time'] = date('Y-m-d', $v['time']);
            }
        }
        return $re;
    }
    //咨询查询
    public function AdviceSearch($pid,$content)
    {
        $map['content']=array('like','%'.$content.'%');
        $re=db('advice')->where('pid',$pid)->where($map)->select();
        if($re) {
            foreach ($re as $k => $v)
            {
                $re[$k]['time'] = date('Y-m-d', $v['time']);
            }
        }
        return $re;
    }

}