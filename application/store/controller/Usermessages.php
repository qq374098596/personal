<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25 0025
 * Time: 13:21
 */

namespace app\store\controller;
use think\Controller;

class Usermessages extends Controller
{
    protected $db;
    protected function _initialize()
    {
        parent::_initialize();
        $this->db = new \app\store\model\Usermessages();
    }
    //用户点评
    public function review()
    {
        $pid = session('api.pid');
        $review=$this->db->Review($pid);
        return json($review);
    }
    //点评查询
    public function reviewSearch()
    {
        $evaluate=request()->post('num');
        $content=request()->post('content');
        $pid=session('api.pid');
        $reviewSearch=$this->db->ReviewSearch($pid,$evaluate,$content);
        return json($reviewSearch);
    }

    /**
     * 回复点评
     * @return [type] [description]
     */
    public function addreview()
    {
        $input = input('post.');
        $addreview = $this->db->addreview($input);
        return json($addreview);
    }
    //用户问答
    public function question()
    {
        $pid = session('api.pid');
        $question=$this->db->Question($pid);
        return json($question);
    }
    //问答查询
    public function questionSearch()
    {
        $content=request()->post('content');
        $pid=session('api.pid');
        $questionSearch=$this->db->QuestionSearch($pid,$content);
        return json($questionSearch);
    }

    //用户咨询
    public function advice()
    {
        $pid = session('api.pid');
        $advice=$this->db->Advice($pid);
        return json($advice);
    }
    //咨询查询
    public function adviceSearch()
    {
        $content=request()->post('content');
        $pid=session('api.pid');
        $adviceSearch=$this->db->AdviceSearch($pid,$content);
        return json($adviceSearch);
    }

}