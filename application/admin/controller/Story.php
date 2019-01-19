<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 17:56
 */

namespace app\admin\controller;


class Story extends Base{
    private $db_story;
    private $db_daoshi;
    protected function _initialize(){
        parent::_initialize();
        $this->db_story = new \app\model\Story();
        $this->db_daoshi = new \app\model\Daoshi();
    }
    public function index(){
        $this->assign('title', '故事列表');
        $list = $this->db_story->getPageSelect('','',10);
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function story_set(){
        $id = input('id',0);
        if ($id) $this->assign('title','修改故事');
        else $this->assign('title','添加故事');
        $daoshi = $this->db_daoshi->column('id,name');
        $this->assign('id', $id);
        $this->assign('info', $this->db_story->db_find($id));
        $this->assign('daoshi', $daoshi);
        return $this->fetch();
    }
    public function story_post(){
        $id = input('id',0);
        $title= input('post.title');
        $banner = input('post.banner');
        $daoshiid = input('post.daoshiid');
        $gushi = input('post.gushi');
        $version = input('post.version');
        $read_num = input('post.read_num');
        $status = input('post.status');
        $data = [
            'title' => $title,
            'banner' => $banner,
            'daoshiid' => $daoshiid,
            'gushi' => $gushi,
            'version' => $version,
            'read_num' => $read_num,
            'status' => $status,
        ];
        //验证
        $storyValidate = new \app\validate\story();
        if (!$storyValidate->check($data)) {
            $this->error($storyValidate->getError());
        }
        if ($id > 0){
            if ($this->db_story->db_update($id, $data)) return $this->success('修改成功','story/index');
            else return $this->success('修改失败');
        }else{
            if ($this->db_story->db_insert($data)) return $this->success('新增成功','story/index');
            else return $this->success('修改失败');
        }
    }
    public function story_del(){
        $id = input('id',0);
        if (empty($id)) return $this->error('参数错误');
        if ($this->db_story->db_del($id)) return $this->success('删除成功','story/index');
        else return $this->error('删除失败');
    }
}