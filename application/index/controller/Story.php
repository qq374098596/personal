<?php
namespace App\index\Controller;
use think\Controller;
use think\Db;

class Story extends Controller
{
    protected $db;

    protected function _initialize()
    {
        parent::_initialize();
        $this->db = new \app\index\model\Story();
    }

    /**
     * 获取首页数据
     * @return [type] [description]
     */
    public function index()
    {
        $story = Db::name('gushi')->paginate('5')->each(function ($item, $key) {
            $item['time'] = date('Y-m-d', $item['time']);
            return $item;
        });

        $this->assign('story', $story);
        $this->assign('title', '创业故事');

        return $this->fetch();
    }

    public function detail()
    {
        $uid = session('user.id');
        $id = input('param.id');
        $readNum = Db::name('gushi')->where("id", $id)->setInc('read_num');
        //	获取详情页数据（包含评论）
        $detail = $this->db->detail($id);
        $detail['detail']['time'] = date('Y-m-d H:i:s', $detail['detail']['time']);
        //评论自己是否点赞
        foreach ($detail['comment'] as $k => $v) {
            $re[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'myid' => $uid, 'status' => 1])->field('status')->find();
            foreach ($re as $ki => $vi) {
                if ($vi == null) {
                    $re[$ki] = array('status' => 0);
                }
            }
            $detail['comment'][$k] = array_merge($v, $re[$k]);
        }
        //评论总点赞
        foreach ($detail['comment'] as $k => $v) {
            $rea[] = array();
            $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'status' => 1])->field('mid')->count();
            foreach ($rea as $ki => $vi) {
                if (!$vi) {
                    unset($rea[$ki]);
                }
            }
            $rea = array_values($rea);
            $detail['comment'][$k] = array_merge($v, $rea[$k]);
        }
        //总收藏数
        $getshoucang = $this->getNum('shoucang', $id);
        //文章总点赞数
        $getdianzan = $this->getNum('dianzan', $id);
        //自己是否点赞
        $mydianzan = $this->myNum('dianzan', $id, $uid);
        //自己是否收藏
        $myshoucang = $this->myNum('shoucang', $id, $uid);
        //文章总评论
        $pinglun=Db::name('pinglun')->where(['mid'=>$id,'mkind'=>2,'gid'=>0])->count();
        $data = array(
            'thisone' => $detail,
            'getshoucang' => $getshoucang,
            'getdianzan' => $getdianzan,
            'mydianzan' => $mydianzan,
            'pinglun' => $pinglun,
            'myshoucang' => $myshoucang,

        );
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (input('param.type') == 'json') {
            echo $json;
        }
        $this->assign('data', $json);
        $this->assign('title', '创业故事');
        return $this->fetch();
    }

    public function comment()
    {
        //var_dump($_POST);die;
        $comment = $this->db->comment($_POST);
        return $comment;
    }


    // public function detailJson()
    // {
    // 	$id = input('param.id');
    // 	$detailJson = $this->db->detailJson($id);
    // 	$detailJson['time'] = date('Y-m-d H:i:s',$detailJson['time']);
    // 	$data = array(
    // 		'thisone' => $detailJson,
    // 	);
    // 	echo json_encode($data,JSON_UNESCAPED_UNICODE);
    // }

    // public function getStory()
    // {
    // 	$story = $this->db->getAll('gushi');


    // 	foreach ($story as $k => $v) {
    // 		$story[$k]['time'] = date('Y-m-d',$v['time']);
    // 		$story[$k]['gushi'] = mb_substr($v['gushi'],0,480);
    // 		$story[$k]['gushi'] = $this->img_empty($story[$k]['gushi'])."\"".">";
    // 		//var_dump($story[$k]['gushi']);
    // 	}

    // 	$data = ['gushi'=>$story];
    // 	echo json_encode($data,JSON_UNESCAPED_UNICODE);
    // }

    //截取文章IMG
    public function img_empty($str)
    {
        $pattern = '/<img\s+src=[\\\'| \\\"](.*?(?:[\.gif|\.jpg]))[\\\'|\\\"].*?[\/]?>/';
        $str1 = preg_replace($pattern, " ", $str);
        return $str1;
    }

    //总收藏点赞数量
    public function getNum($table, $id)
    {
        $num = Db::name($table)->where(['kind' => 3, 'mid' => $id, 'status' => 1])->count();
        return $num;
    }

    //自己是否点赞/收藏
    public function myNum($table, $id, $uid)
    {
        $num = Db::name($table)->where(['uid' => $uid, 'kind' => 3, 'mid' => $id, 'status' => 1])->count();
        return $num;
    }

    //文章点赞事件
    public function changedianzan()
    {
        $mid = input('param.id');
        $uid = session('user.id');
        if(!$uid){
            return;
        }
        $status = Db::name('dianzan')->where(['uid' => $uid, 'kind' => 3, 'mid' => $mid])->field('status')->find();
        if (!$status) {
            $re = Db::name('dianzan')->insert(['uid' => $uid, 'kind' => 3, 'mid' => $mid, 'status' => 1, 'time' => time()]);
            $data['mydianzan'] = 1;
        }
        if ($status['status'] == 1) {
            $re = Db::name('dianzan')->where(['uid' => $uid, 'kind' => 3, 'mid' => $mid])->update(['status' => 0]);
            $data['mydianzan'] = 0;
        }
        if ($status['status'] == 0) {
            $re = Db::name('dianzan')->where(['uid' => $uid, 'kind' => 3, 'mid' => $mid])->update(['status' => 1]);
            $data['mydianzan'] = 1;
        }
        $num = Db::name('dianzan')->where(['mid' => $mid, 'kind' => 3, 'status' => 1])->count();
        $data['getdianzan'] = $num;
        return $data;
    }

    //收藏事件
    public function changeshoucang()
    {
        $mid = input('param.id');
        $uid = session('user.id');
        if(!$uid){
            return;
        }
        $status = Db::name('shoucang')->where(['uid' => $uid, 'kind' => 3, 'mid' => $mid])->field('status')->find();
        if (!$status) {
            $re = Db::name('shoucang')->insert(['uid' => $uid, 'kind' => 3, 'mid' => $mid, 'status' => 1, 'time' => time()]);
            $data['myshoucang'] = 1;
        }
        if ($status['status'] == 1) {
            $re = Db::name('shoucang')->where(['uid' => $uid, 'kind' => 3, 'mid' => $mid])->update(['status' => 0]);
            $data['myshoucang'] = 0;
        }
        if ($status['status'] == 0) {
            $re = Db::name('shoucang')->where(['uid' => $uid, 'kind' => 3, 'mid' => $mid])->update(['status' => 1]);
            $data['myshoucang'] = 1;
        }
        $num = Db::name('shoucang')->where(['mid' => $mid, 'kind' => 3, 'status' => 1])->count();
        $data['getshoucang'] = $num;
        return $data;
    }

    //评论分页
    public function page()
    {
        $nowpage = request()->post('dj');
        $id = request()->post('id');
        $uid = session('user.id');
        $options = [
            'page' => $nowpage
        ];
        $comment = $this->review($id, $options);
        //评论自己是否点赞
        foreach ($comment as $k => $v) {
            $re[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'myid' => $uid, 'status' => 1])->field('status')->find();
            foreach ($re as $ki => $vi) {
                if ($vi == null) {
                    $re[$ki] = array('status' => 0);
                }
            }
            $comment[$k] = array_merge($v, $re[$k]);
        }
        //评论总点赞
        foreach ($comment as $k => $v) {
            $rea[] = array();
            $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'status' => 1])->field('mid')->count();
            foreach ($rea as $ki => $vi) {
                if (!$vi) {
                    unset($rea[$ki]);
                }
            }
            $rea = array_values($rea);
            $comment[$k] = array_merge($v, $rea[$k]);
        }
        return $comment;
    }

    public function review($mid, $options)
    {
        // 第一层评论
        $comment = Db::name('pinglun')->alias('p')->join('fanxiang_user u', 'p.uid1=u.id')
            ->field('u.nickname,u.headimg,p.id,p.uid1,p.gid,p.content,p.time')
            ->where('mkind', 2)->where("p.mid='" . $mid . "' AND p.uid2=0")->order('p.time desc')->paginate(2, false, $options);
        // 循环合并第二层评论
        foreach ($comment as $k => $v) {

            $coments[]['reply'] = Db::name('pinglun')->field('id,uid1,uid2,gid,content,time')->where('mkind', 2)->where("mid='" . $mid . "' AND gid='" . $v['id'] . "'")->select();
            $comment[$k] = array_merge($v, $coments[$k]);

        }
        $items=$comment->items();

        foreach ($items as $k => $v) {
            foreach ($v['reply'] as $key => $value) {
                $uid1[] = $this->db->user($value['uid1']);
                $uid2[] = $this->db->user($value['uid2']);
                $items[$k]['reply'][$key]['uid1name'] = $uid1[$key]['nickname'];
                $items[$k]['reply'][$key]['uid2name'] = $uid2[$key]['nickname'];
                $items[$k]['reply'][$key]['time'] = date('Y-m-d', $value['time']);
            }
        }
        foreach ($comment as $k=>$v){
            $comment[$k] = array_merge($v, $items[$k]);
        }
        //var_dump($comment);die;
        return $comment;
    }
    //评论点赞
    public function pdianzan()
    {
        $nowpage = request()->post('page');
        $options = [
            'page' => $nowpage
        ];
        $pid = request()->post('mid');
        $mid = request()->post('pid');
        $myid = session('user.id');
        if (!$myid) {
            return;
        }
        $comment = $this->review($mid, $options);
        $res = Db::name('commentary')->where(['pid' => $pid, 'kind' => 3, 'mid' => $mid, 'myid' => $myid])->find();
        if ($res) {
            if($res['status']==1){
                $res=Db::name('commentary')->where(['pid' => $pid, 'kind' => 3, 'mid' => $mid, 'myid' => $myid])->update(['status'=>0]);
                //评论自己是否点赞
            foreach ($comment as $k => $v) {
                $req[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'myid' => $myid, 'status' => 1])->field('status')->find();
                foreach ($req as $ki => $vi) {
                    if ($vi == null) {
                        $req[$ki] = array('status' => 0);
                    }
                }
                $comment[$k] = array_merge($v, $req[$k]);
            }
            //评论总点赞
            foreach ($comment as $k => $v) {
                $rea[] = array();
                $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'status' => 1])->field('mid')->count();
                foreach ($rea as $ki => $vi) {
                    if (!$vi) {
                        unset($rea[$ki]);
                    }
                }
                $rea = array_values($rea);
                $comment[$k] = array_merge($v, $rea[$k]);
            }
            }else{
                $res=Db::name('commentary')->where(['pid' => $pid, 'kind' => 3, 'mid' => $mid, 'myid' => $myid])->update(['status'=>1]);
                //评论自己是否点赞
                foreach ($comment as $k => $v) {
                    $req[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'myid' => $myid, 'status' => 1])->field('status')->find();
                    foreach ($req as $ki => $vi) {
                        if ($vi == null) {
                            $req[$ki] = array('status' => 0);
                        }
                    }
                    $comment[$k] = array_merge($v, $req[$k]);
                }
                //评论总点赞
                foreach ($comment as $k => $v) {
                    $rea[] = array();
                    $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'status' => 1])->field('mid')->count();
                    foreach ($rea as $ki => $vi) {
                        if (!$vi) {
                            unset($rea[$ki]);
                        }
                    }
                    $rea = array_values($rea);
                    $comment[$k] = array_merge($v, $rea[$k]);
                }
            }
        } else {
            $re = Db::name('commentary')->insert(['pid' => $pid, 'kind' => 3, 'mid' => $mid, 'myid' => $myid, 'status' => 1]);
            //评论自己是否点赞
            foreach ($comment as $k => $v) {
                $req[] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'myid' => $myid, 'status' => 1])->field('status')->find();
                foreach ($req as $ki => $vi) {
                    if ($vi == null) {
                        $req[$ki] = array('status' => 0);
                    }
                }
                $comment[$k] = array_merge($v, $req[$k]);
            }
            //评论总点赞
            foreach ($comment as $k => $v) {
                $rea[] = array();
                $rea[]['cou'] = Db::name('commentary')->where(['pid' => $v['id'], 'kind' => 3, 'status' => 1])->field('mid')->count();
                foreach ($rea as $ki => $vi) {
                    if (!$vi) {
                        unset($rea[$ki]);
                    }
                }
                $rea = array_values($rea);
                $comment[$k] = array_merge($v, $rea[$k]);
            }
        }
        return $comment;
    }
}
?>