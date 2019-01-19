<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 10:36
 */

namespace app\admin\controller;
use think\Controller;
use think\Session;


class Base extends Controller{
    protected function _initialize(){
        parent::_initialize();
        @define('TIMESTAMP', time());
        @define('DATETIME', date('Y-m-d H:i:s', TIMESTAMP));
        $this->is_check_login();
    }

    private function is_check_login(){
        $uid = Session::get('uid', 'admin');
        $username = Session::get('username', 'admin');
        if (empty($uid) && empty($username)) return $this->redirect('login/index');
    }
    public function uploads(){
        $file = isset($_FILES['filedata']) ? @$_FILES['filedata'] : @$_FILES['uploadName'];
        if (!empty($file) && $file['name'] && $file['error'] == 0 && (isset($GLOBALS['upload_mime'][$file['type']]))) {
            $path = '/Uploads/'.date('Ymd');
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
}