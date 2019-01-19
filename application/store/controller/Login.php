<?php
namespace app\store\controller;
use think\Controller;

class Login extends Controller
{
	protected $db;
	protected function _initialize()
	{
		parent::_initialize();
		$this->db = new \app\store\model\Login();
	}
	
	/**
	 * 管理员登陆
	 * @return [type] [description]
	 */
	public function login()
	{
		
		//var_dump(request()->post());die;
		$posts = request()->post();
		$inputs = json_decode(key($posts),true);
		$input['username'] = $inputs['username'];
		$input['password'] = $inputs['password'];
		//var_dump($input);die;
		//$input['username'] = request()->post('username');
		//$input['password'] = request()->post('password');

		$data = $this->db->login($input);
		return json($data);
	}

	/**
	 * 修改密码
	 * @return [type] [description]
	 */
    public function editpassword()
    {
        //$oldpassword=request()->post('oldpassword');
        //$oldpassword=md5($oldpassword);
        //$newpassword=request()->post('newpassword');
        //$id=session('api.id');
        //$re=db('api')->where(['id'=>$id,'password'=>$oldpassword])->find();

        $input = input('post.');
        $oldPass = md5($input['oldPass']);
        $newPass = md5($input['newPass']);
        $id=session('admin.id');
        $re=db('admin')->where(['id'=>$id,'password'=>$oldPass])->find();
        if(!$re)
        {
            $data['s']=500;
            $data['error']='原密码输入错误,请重新输入!';
        }else{
            //$re=db('api')->where('id',$id)->update(['password'=>$newpassword]);
            //$data['code']=200;
            //$data['mgs']='密码修改成功';

            $re = db('admin')->where('id',$id)->update(['password'=>$newPass]);
            $data['s']=200;
            $data['msg']='密码修改成功';
        }
        return json($data);
    }

	/**
	 * 管理员退出
	 */
	public function quit()
	{
		session('user',null);
		return json(['s'=>200,'msg'=>'已退出！']);
	}
}
?>