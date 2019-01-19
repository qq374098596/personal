<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 10:48
 */

namespace app\admin\controller;


use think\Session;

class Index extends Base
{
    private $db_user;
    protected function _initialize(){
        parent::_initialize();
        $this->db_user = new \app\model\User();
    }
    public function index(){
        $this->assign('title','返乡创业网后台');
        $uid = Session::get('uid', 'admin');
        if (empty($uid)) return $this->redirect('/admin.php/login/index');
        $user = $this->db_user->db_find($uid);
        $this->assign('username', $user->username);
        $this->assign('remark', $user->remark);
        $menu = $this->_menu($user->role);
        $this->assign('menu', $menu);
        return $this->fetch();
    }
    public function welcome(){
        $this->assign('title','首页');
        return $this->fetch();
    }

    /***
     * @param $role_id 用户role_id
     * 查询用户菜单信息
     */
    private function _menu($role_id){
        $db_access = new \app\model\UserAccess();
        $nodes  = $db_access->getUserAccessNodes($role_id);
        if (empty($nodes)) return [];
        $data = [];
        foreach ($nodes as $node) {
            if ($node['pid'] != 1 ) continue;
            $data[$node['id']]['id'] 		= $node['id'];
            $data[$node['id']]['name'] 		= $node['name'];
            $data[$node['id']]['title'] 		= $node['title'];
            $data[$node['id']]['status'] 		= $node['status'];
            $data[$node['id']]['remark'] 		= $node['remark'];
            $data[$node['id']]['level'] 	= $node['level'];
            $data[$node['id']]['data'] 	= $node['data'];
            $data[$node['id']]['pid'] 	= $node['pid'];
            foreach($nodes as $item){
                if($node['id'] == $item['pid']){
                    $data[$node['id']]['children'][$item['id']]['id']		= $item['id'];
                    $data[$node['id']]['children'][$item['id']]['name'] 		= $item['name'];
                    $data[$node['id']]['children'][$item['id']]['title'] 	= $item['title'];
                    $data[$node['id']]['children'][$item['id']]['status'] 		= $item['status'];
                    $data[$node['id']]['children'][$item['id']]['remark'] 		= $item['remark'];
                    $data[$node['id']]['children'][$item['id']]['level'] 	= $item['level'];
                    $data[$node['id']]['children'][$item['id']]['data'] 	= $item['data'];
                }
            }

        }
//        echo "<pre>";
//        var_dump($data);exit;
        return $data;
    }
}