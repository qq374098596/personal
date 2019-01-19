<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/5
 * Time: 15:13
 */

namespace app\admin\controller;


class Comment extends Base{
    private $db_comment;
    protected function _initialize(){
        parent::_initialize();
        $this->db_comment = new \app\model\Comment();
    }
    public function index(){
        $this->assign('title','评论列表');
        $this->assign('q','');
        $comment = $this->db_comment->getPageSelect('','id desc', 10);
        $this->assign('list', $comment);
        $this->assign('page', $comment->render());
        return $this->fetch();
    }
    public function comment_set(){
        $id = input('id',0);
        if ($id) $this->assign('title','修改评论');
        else $this->assign('title','添加评论');
        $this->assign('id', $id);
        $this->assign('info', $this->db_comment->db_find($id));
        return $this->fetch();
    }
    public function comment_post(){
        $id = input('id');
        $data = input('post.');
        if ($id > 0){
            if ($this->db_comment->db_update($id, $data)) return $this->success('修改成功','comment/index');
            else return $this->success('修改失败');
        }else{
            $data['time'] = TIMESTAMP;
            if ($this->db_comment->db_insert($data)) return $this->success('新增成功','comment/index');
            else return $this->success('新增失败');
        }
    }
    public function comment_del(){
        $id = input('id',0);
        if (empty($id)) return $this->error('参数错误');
        if ($this->db_comment->db_del($id)) return $this->success('删除成功','comment/index');
        else return $this->error('删除失败');
    }
}