<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 2019/1/6
 * Time: 2:00 PM
 */

namespace app\lib;


use think\Config;

class qr{
    /***
     * 获取微信小程序二维码
     * @return bool|string [小程序二维码图片]
     */
    public static function getXcxCode($itemid, $moduleid=1){
        //获取参数值
        $token_file = ROOT_PATH;
        $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".self::getAccessToken($token_file);
        $path = 'pages/pinpai/xiangqing';
        if ($moduleid == 2) {
            $path = 'pages/dianping/xiangqing';
        }
        $data=[
            'page' => $path,
            'scene'=> $itemid,
            'width'=>200,
            'auto_color'=>false,
        ];
        $data=json_encode($data);
        $result = Util::curlPost($url,$data);
        if (!$result) {
            return false;
        }
        $fileName=$moduleid."-".$itemid;
        if ($fileName) {
            //判断file文件中是否存在数据库当中
            //$isfile=Db::name('xcxcode')->where('fileName=:fileName',['fileName'=>$fileName])->select();
            //if(!$isfile){
                file_put_contents($token_file."\public\static\qrcode\\".$fileName.".jpeg", $result);
            //    $datafile=['fileName'=>$fileName];
            //    Db::name('xcxcode')->insert($datafile);
            //}
            return "static/qrcode/".$fileName.".jpeg";
        }
    }

    /**
     * @param string $token_file 用来存储token的临时文件
     * @return bool|string
     */
    private static function getAccessToken($token_file='D:\phpStudy\PHPTutorial\WWW\fanxiang\public\access_token') {
        // 考虑过期问题，将获取的access_token存储到某个文件中
        $token_file = $token_file.'public\access_token';
        $life_time = 7200;
        if (file_exists($token_file) && time()-filemtime($token_file)<$life_time) {
            // 存在有效的access_token
            return file_get_contents($token_file);
        }
        // 目标URL：
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".Config::get('appid')."&secret=".Config::get('appsecret');
        //向该URL，发送GET请求
        $result = Util::curlGet($url);
        if (!$result) {
            return false;
        }
        // 存在返回响应结果
        $result_obj = json_decode($result);
        // 写入
        file_put_contents($token_file, $result_obj->access_token);
        return $result_obj->access_token;
    }
}