<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/4
 * Time: 12:05
 */

namespace app\admin\controller;


use think\Db;

class Member extends Base{
    private $db_member;
    protected function _initialize(){
        parent::_initialize();
        $this->db_member = new \app\model\Member();
    }
    public function index(){
        $q = input('post.q');
        $this->assign('title','会员列表');
        $where = '';
        if (!empty($q)){
            $where = " name like '%{$q}%' or nickname like '%{$q}%' or tel like '%{$q}%'";
        }
        $member = $this->db_member->getPageSelect($where,'id desc', 10);
        //修改图片路径
        if (!empty($member)){
            foreach ($member as $item){
//                var_dump(strpos($item->headimg, 'http') !== false);
//                echo "<br>";
                if (!empty($item->headimg) && strpos($item->headimg, 'http') === false) {
                    $item->headimg = BASEURI.'/static/'.$item->headimg;
                }
            }
        }
         //exit();
        $this->assign('list', $member);
        $this->assign('q', $q);
        $this->assign('page', $member->render());
        return $this->fetch();
    }
    public function member_set(){
        $id = input('id');
        if ($id) $this->assign('title','修改会员');
        else $this->assign('title','添加会员');
        $this->assign('id',$id);
        $this->assign('info',$this->db_member->db_find($id));
        return $this->fetch();

    }
    public function memberUpload(){
        $file = isset($_FILES['filedata']) ? @$_FILES['filedata'] : @$_FILES['uploadName'];
        if (!empty($file) && $file['name'] && $file['error'] == 0 && (isset($GLOBALS['upload_mime'][$file['type']]))) {
            $path = '\Uploads\member\\';
            $url = $path.TIMESTAMP.'.'.pathinfo($file['name'])['extension'];
            if (!is_dir(RESOURCEPATH.$path)){
                if(!mkdir(RESOURCEPATH.$path, 0777)){
                    return json(['ret'=>1, 'msg'=>'目录创建失败']);
                }
            };
            if (!move_uploaded_file($file['tmp_name'], RESOURCEPATH.$url))  return $this->json(['ret'=>1, 'msg'=>'上传图片失败']);
            $is_url = input('is_url', 0);
            if(!empty($is_url)){
                return json(['ret'=>0, 'errno'=> 0,'url' => CDNRESOURCE.$url]);
            }else{
                return json(['ret'=>0, 'errno'=> 0,'url' => $url]);
            }
        }
        return json(['ret'=>1, 'msg'=>'上传图片失败!']);
    }
    public function member_post(){
        $id = input('id');
        $data = input(('post.'));
        if (empty($data)) return $this->error('参数错误');
        if (!empty($data['pwd'])) $data['pwd'] = md5($data['pwd']);
        else unset($data['pwd']);
        unset($data['filedata']);
        //验证
        $memberValidate = new \app\validate\member();
        if (!$memberValidate->check($data)) {
            $this->error($memberValidate->getError());
        }
        if ($id > 0){
            if ($this->db_member->db_update($id, $data)) return $this->success('修改成功','member/index');
            else return $this->success('修改失败');
        }else{
            if ($this->db_member->db_insert($data)) return $this->success('新增成功','member/index');
            else return $this->success('修改失败');
        }
    }
    public function member_del(){
        $id = input('id',0);
        if (empty($id)) return $this->error('参数错误');
        if ($this->db_member->db_del($id)) return $this->success('删除成功','member/index');
        else return $this->error('删除失败');
    }
    //商家管理
    public function merchart()
    {
        if(request()->post())
        {
            $search=request()->post('search');
            $merchart=$this->db_member->search($search);
            $count=count($merchart);
            $this->assign('title','商家管理');
            $this->assign('count',$count);
            $this->assign('merchart',$merchart);
            return $this->fetch();
        }else{
        $merchart=$this->db_member->merchart();
        $count=count($merchart);
        $this->assign('title','商家管理');
        $this->assign('count',$count);
        $this->assign('merchart',$merchart);
        return $this->fetch();
    }
    }
    public function merchart_dle()
    {
        $id=request()->get('id');
        $re=db('admin')->where('id',$id)->delete();
        if($re)
        {
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function merchart_edit()
    {
        $id=request()->get('id');
        $merchart=$this->db_member->merchart_edit($id);
        foreach ($merchart as $k)
        {
            $pinpai=db('pinpai')->where('id','<>',$k['pid'])->where('kind',2)->field('id,name')->select();
        }
        $this->assign('merchart',$merchart);
        $this->assign('pinpai',$pinpai);
        $this->assign('title','商户编辑');
        return $this->fetch();

    }
    public function editnow()
    {
        $id=request()->get('id');
        $name=request()->post('name');
        $pid=request()->post('op');
        $password=request()->post('password');
        $status=request()->post('or');
        $expire_time=request()->post('expire_time');
        if(empty($password)){
            $res=['member'=>$name,'pid'=>$pid,'status'=>$status,'expire_time'=>$expire_time];
        }else{
            $password=md5($password);
            $res=['member'=>$name,'password'=>$password,'pid'=>$pid,'status'=>$status,'expire_time'=>$expire_time];
        }
        $re=db('admin')->where('id',$id)->update($res);
        if($re){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }
    public function merchart_set()
    {
        $pinpai=db('pinpai')->where('kind',2)->field('id,name')->select();
        $this->assign('pinpai',$pinpai);
        $this->assign('title','商户添加');
        return $this->fetch();
    }
    public function merchartset()
    {
        $member=request()->post('name');
        $password=request()->post('password');
        $pid=request()->post('op');
        $status=request()->post('or');
        $expire_time=request()->post('expire_time');
        if(empty($member)){
            $this->error('账号不能为空');
        }
        if(empty($password)){
            $this->error('密码不能为空');
        }
        if(empty($expire_time)){
            $this->error('过期时间不能为空');
        }
        $re=db('admin')
            ->insert(['member'=>$member,'password'=>$password,'create_time'=>time(),'pid'=>$pid,'status'=>$status,'expire_time'=>$expire_time]);
        if($re){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }
    //消息推送
    public function message()
    {
        $message=$this->db_member->message();
        $count=count($message);
        $this->assign('title','消息管理');
        $this->assign('count',$count);
        $this->assign('message',$message);
        return $this->fetch();
    }

    public function message_add()
    {
        $this->assign('title','消息管理新增');
        return $this->fetch();
    }
    public function message_set()
    {
        $datas=request()->post();
        $datas['time']=time();
        $data=$this->db_member->message_set($datas);
        if($data){
            $this->success('增加成功');
        }else{
            $this->error('增加失败');
        }
    }
    public function message_dle()
    {
        $id=request()->get('id');
        $res=Db::name('message')->where('id',$id)->delete();
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function message_edit()
    {
        $id=request()->get('id');
        $message=Db::name('message')->where('id',$id)->select();
        $this->assign('title','消息管理');
        $this->assign('message',$message);
        return $this->fetch();
    }
    public function message_edits()
    {
        $id=request()->get('id');
        $data=request()->post();

        $res=Db::name('message')->where('id',$id)->update($data);
        if($res)
        {
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }

    }

}