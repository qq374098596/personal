<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 12:59
 */

namespace app\admin\controller;


use app\lib\Tree;
use app\lib\Util;
use think\Request;

class User extends Base{
    protected $db_role;
    protected $db_user;
    protected $db_node;
    protected $db_access;
    protected function _initialize()
    {
        parent::_initialize();
        $this->db_role = new \app\model\UserRole();
        $this->db_user = new \app\model\User();
        $this->db_node = new \app\model\Node();
        $this->db_access = new \app\model\UserAccess();
    }

    /***
     * 用户列表
     */
    public function index(){
        $this->assign('title','用户列表');
        $this->assign('list', $this->db_user->db_select());
        $this->assign('role',$this->db_role->column('id,name'));
        return $this->fetch();
    }
    public function user_del(){
        $id = input('id');
        if (empty($id)) return $this->error('参数错误');
        if ($this->db_user->db_del($id)) return $this->success('删除成功',url('user/index'));
        else return $this->error('删除失败');
    }

    /***
     * 用户菜单
     */
    public function user_node(){
        $this->assign('title','菜单列表');
        $node = $this->db_node->select();
        $this->assign('count',count($node));
        $node = Util::obj2arr($node);
        $array = array();
        // 构建生成树中所需的数据
        foreach($node as $k => $r) {
            $r['id']      = $r['id'];
            $r['title']   = $r['title'];
            $r['name']    = $r['name'];
            $r['status']  = $r['status']==1 ? '<span class="label label-success radius">启用</span>' :'<span class="label label-danger radius">关闭</span>';
            $r['submenu'] = $r['level']==3 ? '<font color="#cccccc">添加子菜单</font>' : "<a class='href-a' href='".url('/user/user_node_set/pid/'.$r['id'])."'>添加子菜单</a>";
            $r['edit']    = $r['level']==1 ? '<font color="#cccccc">修改</font>' : "<a class='href-a' href='".url('/user/user_node_set/id/'.$r['id'].'/pid/'.$r['pid'])."'>修改</a>";
            $r['del']     = $r['level']==1 ? '<font color="#cccccc">删除</font>' : "<a class='href-a' onClick='confirm_del(\"".url('/user/node_del/id/'.$r['id'])."\")' href='javascript:void(0)'>删除</a>";
            switch ($r['display']) {
                case 0:
                    $r['display'] = '不显示';
                    break;
                case 1:
                    $r['display'] = '主菜单';
                    break;
                case 2:
                    $r['display'] = '子菜单';
                    break;
            }
            switch ($r['level']) {
                case 0:
                    $r['level'] = '非节点';
                    break;
                case 1:
                    $r['level'] = '应用';
                    break;
                case 2:
                    $r['level'] = '模块';
                    break;
                case 3:
                    $r['level'] = '方法';
                    break;
            }
            $array[]      = $r;
        }
        $str  = "<tr class='tr'>
				    <td><input type='text' value='\$sort' size='3' name='sort[\$id]'></td>
				    <td>\$id</td> 
				    <td >\$spacer \$title (\$name)</td> 
				    <td>\$level</td> 
				    <td>\$status</td> 
				    <td>\$display</td> 
					<td>
						\$submenu | \$edit | \$del
					</td>
				  </tr>";

        $Tree = new Tree();
        $Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
        $Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $Tree->init($array);
        $html_tree = $Tree->get_tree(0, $str);
        $this->assign('html_tree',$html_tree);
        return $this->fetch();
    }
    public function user_node_set(){
        $id = input('id',0);
        $pid = input('pid', 0); //选择子菜单
        if ($id) $this->assign('title','修改菜单(节点)');
        else $this->assign('title','添加菜单(节点)');
        $node = $this->db_node->select();
        $node = Util::obj2arr($node);
        $array = array();
        foreach($node as $k => $r) {
            $r['id']         = $r['id'];
            $r['title']      = $r['title'];
            $r['name']       = $r['name'];
            $r['disabled']   = $r['level']==3 ? 'disabled' : '';
            $array[$r['id']] = $r;
        }
        $str  = "<option value='\$id' \$selected \$disabled >\$spacer \$title</option>";
        $Tree = new Tree();
        $Tree->init($array);
        $select_categorys = $Tree->get_tree(0, $str, $pid);
        $this->assign('pid', $pid);
        $this->assign('id', $id);
        $this->assign('info', $this->db_node->db_find($id));
        $this->assign('node', $select_categorys);
        return $this->fetch();
    }
    public function node_del(){
        $id = input('id');
        if (empty($id)) return $this->error('参数错误');
        if ($this->db_node->db_del($id)) return $this->success('删除成功',url('user/user_node'));
        else return $this->error('删除失败');
    }
    public function user_node_post(){
        $id = input('id', 0);
        $pid = input('post.pid');
        $title = input('post.title');
        $display = input('post.display');
        $level = input('post.level');
        $name = input('post.name');
        $data = input('post.data');
        $status = input('post.status');
        $remark = input('post.remark');
        $sort = input('post.sort', 0);
        $data = [
            'pid' => $pid,
            'title' => $title,
            'display' => $display,
            'level' => $level,
            'name' => $name,
            'data' => $data,
            'status' => $status,
            'remark' => $remark,
            'sort' => $sort,
        ];
        //验证
        $nodeValidate = new \app\validate\node;
        if (!$nodeValidate->check($data)) {
            $this->error($nodeValidate->getError());
        }
        if ($id > 0){
            if ($this->db_node->db_update($id, $data)) return $this->success('修改成功','user/user_node');
            else return $this->success('修改失败');
        }else{
            if ($this->db_node->db_insert($data)) return $this->success('新增成功','user/user_node');
            else return $this->success('修改失败');
        }
    }

    public function user_set(){
        $id = input('id', 0);
        if ($id) $this->assign('title','修改用户');
        else $this->assign('title','添加用户');
        $role = $this->db_role->column('id,name');
        $this->assign('info', $this->db_user->db_find($id));
        $this->assign('role', $role);
        $this->assign('id', $id);
        return $this->fetch();
    }
    public function user_post(){
        $id = input('id', 0);
        $username = input('post.username');
        $password = input('post.password');
        $password_confirm = input('post.password_confirm');
        $role = input('post.role');
        $status = input('post.status');
        $remark = input('post.remark');
        $data = [
            'username' => $username,
            'password' => $password,
            'password_confirm' => $password_confirm,
            'role' => $role,
            'status' => $status,
            'remark' => $remark,
        ];
        //验证
        $userValidate = new \app\validate\user;
        if ($id > 0){
            if (!empty($data['password'])){
                //获取密码
                $salt = Util::getSalt();
                $pwd = Util::password($data['password'], $salt);
                $data['password'] = $pwd;
                $data['salt'] = $salt;
                if (!$userValidate->scene('add')->check($data)) {
                    $this->error($userValidate->getError());
                }
            }else{
                //删除多余字段
                unset($data['password_confirm']);
                unset($data['password']);
                if (!$userValidate->scene('edit')->check($data)) {
                    $this->error($userValidate->getError());
                }
            }
            //删除多余字段
            unset($data['password_confirm']);
            if ($this->db_user->db_update($id, $data)) return $this->success('修改成功','user/index');
            else return $this->success('修改失败');
        }else{
            if (!$userValidate->scene('add')->check($data)) {
                $this->error($userValidate->getError());
            }
            //获取密码
            $salt = Util::getSalt();
            $pwd = Util::password($data['password'], $salt);
            $data['password'] = $pwd;
            $data['salt'] = $salt;
            unset($data['password_confirm']);
            if ($this->db_user->db_insert($data)) return $this->success('新增成功','user/index');
            else return $this->success('新增失败');
        }
    }

    /**
     * 角色管理
     */
    public function role(){
        $this->assign('title','角色列表');
        $list = $this->db_role->db_select();
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function role_set(Request $request){
        $id = input('id', 0);
        if ($id) $this->assign('title','修改角色');
        else $this->assign('title','添加角色');
        $this->assign('id', $id);
        $this->assign('info', $this->db_role->db_find($id));
        return $this->fetch();
    }
    public function role_del(){
        $id = input('id');
        if (empty($id)) return $this->error('参数错误');
        if ($this->db_role->db_del($id)) return $this->success('删除成功',url('user/role'));
        else return $this->error('删除失败');
    }
    public function role_post(){
        $id = input('id', 0);
        $name = input('post.name');
        $status = input('post.status');
        $remark = input('post.remark');
        $data = [
            'name' => $name,
            'status' => $status,
            'remark' => $remark,
        ];
        //验证
        $roleValidate = new \app\validate\role;
        if (!$roleValidate->check($data)) {
            return $this->error($roleValidate->getError());
        }
        if ($id) $this->db_role->db_update($id, $data);
        else $this->db_role->db_insert($data);
        return $this->redirect(url('/user/role'));
    }

    /***
     * 用户权限设置
     */
    public function user_access(){
        $role_id = input('role_id', 0);
        $this->assign('title','权限设置');
        $Tree = new Tree();
        $Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
        $Tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $node = $this->db_node->getAllNode();
        $node = Util::obj2arr($node);
        $access = $this->db_access->getAllAccess('','role_id,node_id,pid,level');
        $access = Util::obj2arr($access);
        foreach ($node as $n=>$t) {
            $node[$n]['checked'] = ($this->db_access->is_checked($t,$role_id,$access))? ' checked' : '';
            $node[$n]['depth'] = $this->db_access->get_level($t['id'],$node);
            $node[$n]['pid_node'] = ($t['pid'])? ' class="tr lt child-of-node-'.$t['pid'].'"' : '';
        }
        $str  = "<tr id='node-\$id' \$pid_node>                                                                                                                                                                        
            <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='nodeid[]' value='\$id' class='radio' level='\$depth' \$checked onclick='javascript:checknode(this);' > \$title (\$name)</td>   
        </tr>";

        $Tree->init($node);
        $html_tree = $Tree->get_tree(0, $str);
        $this->assign('html_tree',$html_tree);
        $this->assign('role_id',$role_id);
        return $this->fetch();
    }
    public function user_access_post(){
        $role_id = input('role_id',0);
        $nodeid = isset($_POST['nodeid']) ? $_POST['nodeid'] : [];
        if(!$role_id) return $this->error('参数错误!');
        if (is_array($nodeid) && count($nodeid) > 0) {  //提交得有数据，则修改原权限配置
            $this->db_access->delAccess(array('role_id'=>$role_id));  //先删除原用户组的权限配置
            $node = Util::obj2arr($this->db_node->getAllNode());
            foreach ($node as $_v) $node[$_v['id']] = $_v;
            foreach($nodeid as $k => $node_id){
                $data[$k] = $this->db_access-> get_nodeinfo($node_id,$node);
                $data[$k]['role_id'] = $role_id;
            }

            $this->db_access->saveAll($data);   // 重新创建角色的权限配置
        } else {    //提交的数据为空，则删除权限配置
            $this->db_access->delAccess(array('role_id'=>$role_id));
        }
        return $this->success('设置成功！',url('user/user_access',array('role_id'=>$role_id)));
    }
}