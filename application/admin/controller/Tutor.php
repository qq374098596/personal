<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 20:15
 */

namespace app\admin\controller;


class Tutor extends Base{
    private $db_daoshi;
    private $db_hangye;
    protected function _initialize(){
        parent::_initialize();
        $this->db_daoshi = new \app\model\Daoshi();
        $this->db_hangye = new \app\model\Hangye();
    }
    public function index(){
        $this->assign('title', '导师列表');
        $list = $this->db_daoshi->getPageSelect('','',10);
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function tutor_set(){
        $id = input('id',0);
        if ($id) $this->assign('title','修改导师');
        else $this->assign('title','添加导师');
        $this->assign('id', $id);
        $hangye = $this->db_hangye->column('id,hyname');
        $info = $this->db_daoshi->db_find($id);
        $this->assign('info', $info);
        $this->assign('hangye', $hangye);
        return $this->fetch();
    }
    public function tutor_post(){
        $id = input('id', 0);
        $name = input('post.name');
        $headimg = input('post.headimg');
        $hangyeid = input('post.hangyeid');
        $are = input('post.are');
        $tel = input('post.tel');
        $mail = input('post.mail');
        $old = input('post.old');
        $zizhi = input('post.zizhi');
        $xiangmu = input('post.xiangmu');
        $xiangmuxiangqing = input('post.xiangmuxiangqing');
        $imglist = input('post.imglist');
        $licheng = input('post.licheng');
        $jieshao = input('post.jieshao');
        $status = input('post.status');
        $qrcodeurl = input('post.qrcodeurl');
        $tuijian = input('post.tuijian');
        $wxIndexImg = input('post.wxIndexImg');
        $sort = input('post.sort');
        $hangye = $this->db_hangye->column('id,hyname');
        $hangyeName = '';
        if (!empty($hangyeid) && !empty($hangye)) $hangyeName = isset($hangye[$hangyeid]) ? $hangye[$hangyeid] : '';
        $data = [
            'name' => $name,
            'headimg' => $headimg,
            'hangyeid' => $hangyeid,
            'are' => $are,
            'tel' => $tel,
            'mail' => $mail,
            'old' => $old,
            'zizhi' => $zizhi,
            'xiangmu' => $xiangmu,
            'xiangmuxiangqing' => $xiangmuxiangqing,
            'imglist' => $imglist,
            'licheng' => $licheng,
            'jieshao' => $jieshao,
            'qrcodeurl' => $qrcodeurl,
            'tuijian' => $tuijian,
            'sort' => $sort,
            'hyname' => $hangyeName,
            'status' => $status,
            'wx_index_img' => $wxIndexImg,
        ];
        //验证
        $tutorValidate = new \app\validate\tutor();
        if (!$tutorValidate->check($data)) {
            $this->error($tutorValidate->getError());
        }
        if ($id > 0){
            if ($this->db_daoshi->db_update($id, $data)) return $this->success('修改成功','tutor/index');
            else return $this->success('修改失败');
        }else{
            if ($this->db_daoshi->db_insert($data)) return $this->success('新增成功','tutor/index');
            else return $this->success('修改失败');
        }
    }
    public function tutorUpload(){
        $file = isset($_FILES['filedata']) ? @$_FILES['filedata'] : @$_FILES['uploadName'];
        if (!empty($file) && $file['name'] && $file['error'] == 0 && (isset($GLOBALS['upload_mime'][$file['type']]))) {
            $path = '/Uploads/tutor/';
            $url = $path.TIMESTAMP.'.'.pathinfo($file['name'])['extension'];
            if (!is_dir(RESOURCEPATH.$path)){
                if(!mkdir(RESOURCEPATH.$path, 0777)){
                    return json(['ret'=>1, 'msg'=>'目录创建失败']);
                }
            };
            if (!move_uploaded_file($file['tmp_name'], RESOURCEPATH.$url))  return $this->json(['ret'=>1, 'msg'=>'上传图片失败','url'=>'']);
            $is_url = input('is_url', 0);
            if(!empty($is_url)){
                return json(['ret'=>0, 'errno'=> 0,'url' => CDNRESOURCE.$url]);
            }else{
                return json(['ret'=>0, 'errno'=> 0,'url' => $url]);
            }
        }
        return json(['ret'=>1, 'msg'=>'上传图片失败!']);
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
            if ($this->db_daoshi->db_update($id,$data)) return json(['s'=>1,'msg'=>'修改成功！']);
            else return json(['s'=>0,'error'=>'修改失败！']);
        }
    }

    public function clickStrict(){
        $id = input('id',0);
        $zhiding = input('zhiding',0);
        if ($zhiding>0) {
            $data['tuijian'] = 0;
            $this->db_daoshi->db_update($id,$data);
        }else{
            $data['tuijian'] = 1;
            $this->db_daoshi->db_update($id,$data);
        }
    }

}