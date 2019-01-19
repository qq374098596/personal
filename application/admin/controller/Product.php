<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3 0003
 * Time: 11:18
 */

namespace app\admin\controller;
use think\Db;

class Product extends Base{
    protected $db;
    protected function _initialize(){
        parent::_initialize();
        $this->db = new \app\model\Product();
    }
    //严选访问量
    public function access()
    {
        $access=Db::name('pinpai')->alias('p')
            ->join('access a','a.pid=p.id')->group('pid')->where('kind',2)
            ->field('a.pid,a.ip,a.time,p.id,p.name,p.imgsrc')
            ->select();
        foreach ($access as $k=>$v){
            $count[]['count']=Db::name('access')->where('pid',$v['pid'])->count();
            $access[$k]=array_merge($v,$count[$k]);
        }
        $access=$this->db->arraySortByKey($access,'count');
        $this->assign('title','严选品牌访问量');
        $this->assign('access',$access);
        return $this->fetch();
    }
    //访问详情
    public function askaccess()
    {
        $id=request()->get('id');
        $access=Db::name('access')->where('pid',$id)->order('time','desc')->select();
        foreach ($access as $k=>$v)
        {
            $access[$k]['time']=date("Y-m-d H:i:s",$access[$k]['time']);
        }
        $name=Db::name('pinpai')->where('id',$id)->field('name')->find();
        $this->assign('title','严选品牌访问量');
        $this->assign('name',$name);
        $this->assign('access',$access);
        return $this->fetch();
    }
    //普通品牌
    public function index()
    {
        $product=$this->db->product();
        $count=count($product);
        $hangye=Db::name('hangye')->field('id,hyname')->select();
        $this->assign('hangye',$hangye);
        $this->assign('count',$count);
        $this->assign('product',$product);
        $this->assign('title','普通品牌');
        return $this->fetch();
    }
    //添加普通品牌
    public function addCommon()
    {
        $productname=request()->post('productname');
        $hyid=request()->post('hyid');
        $company=request()->post('company');
        $money=request()->post('money');
        $sort=request()->post('sort');
        $server = explode('/',$_SERVER['PHP_SELF']);
        $file = request()->file('file');
        if($file==null)
        {
            $this->error('图片不能为空');
        }
        if(!$productname)
        {
            $this->error('品牌名称不能为空');
        }
        if(!$company)
        {
            $this->error('公司不能为空');
        }
        if(!$money)
        {
            $this->error('加盟费不能为空');
        }
        if ($file->isValid())
        {
            $appPath = 'static/Uploads/';
            $info = $file->move(ROOT_PATH . 'public' . DS . $appPath);
            if ($info) {
                if (!is_numeric(substr($_SERVER['HTTP_HOST'],0,1))) {
                    $ishttp = $_SERVER['HTTP_HOST'] . '/';
                }else{
                    $ishttp = $_SERVER['HTTP_HOST'] . '/' . $server[1] . '/public/';
                }
                $data = ['http://' . $ishttp . $appPath . $info->getSaveName()];
            }
        }
        $re=Db::name('pinpai')->insert(['name'=>$productname,'hangyeid'=>$hyid,'imgsrc'=>$data[0],'company'=>$company,'kind'=>1,'time'=>time(),'jiamengfei'=>$money,'sort'=>$sort]);
        if($re){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }
    //删除普通品牌
    public function dle(){
        $id=request()->get('id');
        $re=$this->db->del('pinpai',$id);
        if($re){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //编辑普通品牌
    public function edit()
    {
        $id=request()->get('id');
        $product=$this->db->edit($id);
        foreach ($product as $k){
            $hangye=Db::name('hangye')->where('id','<>',$k['hangyeid'])->field('id,hyname')->select();
        }
        $this->assign('hangye',$hangye);
        $this->assign('product',$product);
        $this->assign('title','编辑普通品牌');
        return $this->fetch();
    }
    //编辑操作
    public function editnow()
    {
        $id=request()->get('id');
        $name=request()->post('name');
        $op=request()->post('op');
        $company=request()->post('company');
        $money=request()->post('money');
        $order_id=request()->post('or');
        $sort = request()->post('sort');
        $server = explode('/',$_SERVER['PHP_SELF']);
        $file = request()->file('img');
        $rs=Db::name('pinpai')->where('id',$id)->field('imgsrc')->find();
        $data=$rs['imgsrc'];
        if($file!=null) {
            if ($file->isValid()) {
                $appPath = 'static/Uploads/';
                $info = $file->move(ROOT_PATH . 'public' . DS . $appPath);
                if ($info) {
                    if (!is_numeric(substr($_SERVER['HTTP_HOST'], 0, 1))) {
                        $ishttp = $_SERVER['HTTP_HOST'] . '/';
                    } else {
                        $ishttp = $_SERVER['HTTP_HOST'] . '/' . $server[1] . '/public/';
                    }
                    $data = 'http://' . $ishttp . $appPath . $info->getSaveName();
                }
            }
        }
        $re=Db::name('pinpai')->where('id',$id)
            ->update(['name'=>$name,'hangyeid'=>$op,'company'=>$company,'imgsrc'=>$data,'jiamengfei'=>$money,'order_id'=>$order_id,'sort'=>$sort]);
            if($re){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }

    }
    //行业管理
    public function trade()
    {
        $hangye=Db::name('hangye')->select();
        $count=count($hangye);
        $this->assign('count',$count);
        $this->assign('hangye',$hangye);
        $this->assign('title','行业管理');
        return $this->fetch();
    }
    //添加行业
    public function addtrade()
    {
    $productname=request()->post('productname');
    $bgcolor=request()->post('bgcolor');
    if(!$productname){
        $this->error('行业名称不能为空');
    }
    if(!$bgcolor){
        $this->error('背景颜色不能为空');
    }
    $re=Db::name('hangye')->insert(['hyname'=>$productname,'bgcolor'=>$bgcolor]);
    if($re){
        $this->success('添加成功');
    }else{
        $this->error('添加失败');
    }
    }
    //删除行业
    public function dlehy()
    {
        $id=request()->get('id');
        if(!$id){
            $this->error('id错误');
        }
        $re=$this->db->del('hangye',$id);
        if($re){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }

    }
    //编辑行业
    public function edithy()
    {
        $id=request()->get('id');
        $hy=Db::name('hangye')->where('id',$id)->select();
        $this->assign('title','编辑行业');
        $this->assign('hangye',$hy);
        return $this->fetch();
    }
    //编辑操作
    public function hyedit()
    {
        $id=request()->get('id');
        $name=request()->post('name');
        $bgcolor=request()->post('bgcolor');
        $re=Db::name('hangye')->where('id',$id)->update(['hyname'=>$name,'bgcolor'=>$bgcolor]);
        if($re){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }
    //严选品牌
    public function strict()
    {
        $product=$this->db->strict();
        $count=count($product);
        //项目咨询
        foreach ($product as $k=>$v){
            $advice=Db::name('advice')->where('pid',$v['id'])->field('city')->count();
            $v['count']=$advice;
            $product[$k]=$v;
        }
        $this->assign('count',$count);
        $this->assign('product',$product);
        $this->assign('title','严选品牌');
        return $this->fetch();
    }
    //用户咨询
    public function advice()
    {
        $id=request()->get('id');
        $advice=$this->db->advice($id);
        $count=count($advice);
        $this->assign('count',$count);
        $this->assign('advice',$advice);
        $this->assign('title','用户咨询');
        return $this->fetch();
    }
    //严选品牌删除
    public function dlestrict()
    {
        $id=request()->get('id');
        if(!$id){
            $this->error('id错误');
        }
        $re=$this->db->del('pinpai',$id);
        if($re){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    //品牌是否首页显示
    public function clickStrict(){
        $id = input('id',0);
        $zhiding = input('zhiding',0);
        if ($zhiding>0) {
            $data['is_index'] = 0;
            $this->db->db_update($id,$data);
        }else{
            $data['is_index'] = 1;
            $this->db->db_update($id,$data);
        }
    }
    //编辑严选品牌
    public function editstrict()
    {
        $id=request()->get('id');
        $product=$this->db->editstrict($id);
        foreach ($product as $k){
            $hangye=Db::name('hangye')->where('id','<>',$k['hangyeid'])->field('id,hyname')->select();
        }
        $product[0]['ppimglist']=json_decode($product[0]['ppimglist']);
        $this->assign('hangye',$hangye);
        $this->assign('product',$product);
        $this->assign('title','编辑严选品牌');
        return $this->fetch();
    }
    //修改严选品牌
    public function edit_strict()
    {
        $pplist=request()->post('hidden');
        $ppimglist=json_encode(explode(',',$pplist));
        if(strlen($ppimglist)<=5){
            $ppimglist=null;
        }
        $id=request()->get('id');
        $name=request()->post('name');
        $hangyeid=request()->post('op');
        $order_id=request()->post('or');
        $sort = request()->post('sort');
        $company=request()->post('company');
        $tel=request()->post('tel');
        $jiamengfei=request()->post('money');
        $jiamengshop=request()->post('jiamengshop');
        $zhiyingshop=request()->post('zhiyingshop');
        $yueyingshou=request()->post('yueyingshou');
        $jianjie=request()->post('jianjie');
        $shifang=request()->post('shifang');
        $prospect=request()->post('prospect');
        $investment=request()->post('investment');
        if(!$name||!$company||!$jiamengfei)
        {
            $this->error('品牌名称,公司名称,加盟费不能为空');
        }
        $server = explode('/',$_SERVER['PHP_SELF']);
        $file = request()->file('imgsrc');
        $rs=Db::name('pinpai')->where('id',$id)->field('imgsrc')->find();
        $data=$rs['imgsrc'];
        if($file!=null) {
            if ($file->isValid()) {
                $appPath = 'static/Uploads/';
                $info = $file->move(ROOT_PATH . 'public' . DS . $appPath);
                if ($info) {
                    if (!is_numeric(substr($_SERVER['HTTP_HOST'], 0, 1))) {
                        $ishttp = $_SERVER['HTTP_HOST'] . '/';
                    } else {
                        $ishttp = $_SERVER['HTTP_HOST'] . '/' . $server[1] . '/public/';
                    }
                    $data = 'http://' . $ishttp . $appPath . $info->getSaveName();
                }
            }
        }
        $re=Db::name('pinpai')->where('id',$id)->update(['name'=>$name,'imgsrc'=>$data,'ppimglist'=>$ppimglist,'hangyeid'=>$hangyeid,'company'=>$company,'tel'=>$tel,
            'jiamengfei'=>$jiamengfei,'jiamengshop'=>$jiamengshop,'zhiyingshop'=>$zhiyingshop,'yueyingshou'=>$yueyingshou,
            'jianjie'=>$jianjie,'shifang'=>$shifang,'order_id'=>$order_id,'sort'=>$sort,'prospect'=>$prospect,'investment'=>$investment]);
        if($re)
        {
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }
    //添加严选品牌视图
    public function addstrict()
    {
        $hangye=Db::name('hangye')->field('id,hyname')->select();
        $this->assign('hangye',$hangye);
        $this->assign('title','添加严选品牌');
        return $this->fetch();
    }
    //添加严选品牌操作
    public function add_strict()
    {
        $pplist=request()->post('hidden');
        $ppimglist=json_encode(explode(',',$pplist));
        if(strlen($ppimglist)<=5){
            $ppimglist=null;
        }
        $name=request()->post('name');
        $hangyeid=request()->post('op');
        $company=request()->post('company');
        $tel=request()->post('tel');
        $jiamengfei=request()->post('money');
        $jiamengshop=request()->post('jiamengshop');
        $zhiyingshop=request()->post('zhiyingshop');
        $time=time();
        $yueyingshou=request()->post('yueyingshou');
        $sort = request()->post('sort');
        $jianjie=request()->post('jianjie');
        $shifang=request()->post('shifang');
        $prospect=request()->post('prospect');
        $investment=request()->post('investment');
        if(!$name||!$company||!$jiamengfei)
        {
            $this->error('品牌名称,公司名称,加盟费不能为空');
        }
        $server = explode('/',$_SERVER['PHP_SELF']);
        $file = request()->file('img');
        $data='';
        if($file!=null) {
            if ($file->isValid()) {
                $appPath = 'static/Uploads/';
                $info = $file->move(ROOT_PATH . 'public' . DS . $appPath);
                if ($info) {
                    if (!is_numeric(substr($_SERVER['HTTP_HOST'], 0, 1))) {
                        $ishttp = $_SERVER['HTTP_HOST'] . '/';
                    } else {
                        $ishttp = $_SERVER['HTTP_HOST'] . '/' . $server[1] . '/public/';
                    }
                    $data = 'http://' . $ishttp . $appPath . $info->getSaveName();
                }
            }
        }
        $re=Db::name('pinpai')->insert(['name'=>$name,'imgsrc'=>$data,'ppimglist'=>$ppimglist,'hangyeid'=>$hangyeid,'company'=>$company,'tel'=>$tel,
            'jiamengfei'=>$jiamengfei,'jiamengshop'=>$jiamengshop,'zhiyingshop'=>$zhiyingshop,'time'=>$time,'yueyingshou'=>$yueyingshou,
            'jianjie'=>$jianjie,'kind'=>2,'shifang'=>$shifang,'prospect'=>$prospect,'sort'=>$sort]);
        if($re)
        {
            $pid=Db::name('pinpai')->where(['name'=>$name,'kind'=>2])->field('id')->find();
            $message=Db::name('message')->insert(['kind'=>2,'mid'=>$pid['id'],'time'=>time(),'name'=>$name]);
        }
        if($re)
        {
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

//图片列表上传
    public function upimg()
    {
        // 第一步 接收上传的图片资源
        $file = request()->file('img');

        if($file){
            // 第二步 验证并且上传到我们项目的/public/uploads/目录下
            $appPath = 'static/Uploads/';
            $info = $file->validate(['size'=>1567800,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . $appPath);
            // 第三步 判断图片是否上传成功
            if($info){
                // 第四步 成功上传后 获取上传信息
                $server = explode('/',$_SERVER['PHP_SELF']);
                if (!is_numeric(substr($_SERVER['HTTP_HOST'], 0, 1))) {
                    $ishttp = $_SERVER['HTTP_HOST'] . '/';
                } else {
                    $ishttp = $_SERVER['HTTP_HOST'] . '/' . $server[1] . '/public/';
                }
                $img_src = 'http://' . $ishttp . $appPath . $info->getSaveName();
                echo $img_src; //返回ajax请求
            }else{
                // 第四步 上传失败获取错误信息
                $this->error($file->getError());
            }
        }
    }

        //热门推荐
    public function status()
    {
        $id=request()->post('id');
        $order_id=request()->post('status');
        $re=Db::name('pinpai')->where('id',$id)->update(['order_id'=>$order_id]);
        if($re)
        {
            $data['code']=200;
        }else{
            $data['code']=500;
            $data['msg']='修改失败';
        }
        return $data;
    }

    //加盟诊断记录
    public function diagnose()
    {
        $diagnose=Db::name('zhenduan')->order('time desc')->select();
        foreach ($diagnose as $k=>$v)
        {
            $diagnose[$k]['time']=date('Y-m-d',$diagnose[$k]['time']);
        }
        $count=count($diagnose);
        $this->assign('count',$count);
        $this->assign('diagnose',$diagnose);
        $this->assign('title','诊断记录');
        return $this->fetch();
    }
    //诊断报告书
    public function diagnoseimg(){
        $diagnoseimg = Db::name('zhenduan')->field('id,jsfs,record')->where('record','diagnoseimg')->find();
        //var_dump($diagnoseimg);die;
        $this->assign('diagnoseimg',$diagnoseimg);
        $this->assign('title','诊断报告书');
        return $this->fetch();
    }
    //添加诊断报告书
    public function addDiagnoseimg(){
        $id = input('id',0);
        $img = input('post.diagnoseImg');
        $record = input('post.record');
        $data['pinpai'] = 0;
        $data['addr'] = 0;
        $data['jsfs'] = $img;
        $data['time'] = time();
        $data['uid'] = 0;
        $data['record'] = $record;
        if ($id>0) {
            $find = Db::name('zhenduan')->where('id',$id)->data($data)->update();
        }else{
            $find = Db::name('zhenduan')->insert($data);
        }
        if ($find) {
            return $this->success('操作成功','product/diagnoseimg');
        }else{
            return $this->success('操作失败');
        }
    }

    //用户点评
    public function dianping()
    {
        $id=request()->get('id');
        $dianping=Db::name('dianping')->where('pinpaiid',$id)->select();
        foreach ($dianping as $k=>$v)
        {
            $dianping[$k]['time']=date('Y-m-d',$dianping[$k]['time']);
        }
        $count=count($dianping);
        $this->assign('count',$count);
        $this->assign('dianping',$dianping);
        $this->assign('title','用户点评');
        return $this->fetch();
    }
    public function dledianping()
    {
        $id=request()->get('id');
        $re=$this->db->del('dianping',$id);
        if ($re){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    public function dleadvice()
    {
        $id=request()->get('id');
        $re=$this->db->del('advice',$id);
        if($re)
        {
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
//品牌问答
    public function questions()
    {
        $id=request()->get('id');
        $questions=$this->db->questions($id);
        $count=count($questions);
        $this->assign('count',$count);
        $this->assign('questions',$questions);
        $this->assign('title','品牌问答');
        return $this->fetch();
    }
    public function dlequestions()
    {
        $id=request()->get('id');
        $re=$this->db->del('wenda',$id);
        if($re)
        {
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 排序
     * @return [type] [description]
     */
    public function sort(){
        $id = input('id',0);
        $sort = input('post.sort');
        
        $valid = new \think\Validate([
            'sort'=>'require|number'
        ],[
            'sort.require'=>'排序不能为空！',
            'sort.number'=>'排序必须为数字！']);
        $data['sort'] = $sort;
        if (!$valid->check($data)) {
            return json(['s'=>0,'error'=>$valid->getError()]);
        }else{
            if ($this->db->db_update($id,$data)) return json(['s'=>1,'msg'=>'修改成功！']);
            else return json(['s'=>0,'error'=>'修改失败！']);
        }
    }
}