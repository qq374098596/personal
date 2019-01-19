<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 15:50
 */

namespace app\lib;


use think\Config;

class Util{
    public static function jsonp($data, $callback = 'callback') {
        @header('Content-Type: application/json');
        @header("Expires:-1");
        @header("Cache-Control:no-cache");
        @header("Pragma:no-cache");
        if (isset($_REQUEST[$callback])) {
            header("Access-Control-Allow-Origin:*");
            echo $_REQUEST[$callback].'('.json_encode($data, JSON_UNESCAPED_UNICODE).')';
        } else echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    //加密
    public static function up_encode($str) {
        if (is_array($str) || is_object($str)) $str = json_encode($str);
        //$cr = new Crypt3Des (Crypt3DesKey, Crypt3DesIV);
        $cr = new Aes(Config::get('Crypt3DesKey'));
        $str = $cr->encrypt($str);
        $str = str_replace('+', '-', $str);
        $str = str_replace('/', '.', $str);
        $str = str_replace('=', '!', $str);
        return $str;
    }
    //解密
    public static function up_decode($str) {
        if (!$str) return $str;
        $str = urldecode($str);
        $str = trim($str);
        //$cr = new Crypt3Des (Crypt3DesKey, Crypt3DesIV);
        $cr = new Aes(Config::get('Crypt3DesKey'));
        $str = str_replace('-', '+', $str);
        $str = str_replace('.', '/', $str);
        $str = str_replace('!', '=', $str);
        $str = $cr->decrypt($str);
        $couldbeA = json_decode($str, true);
        if (is_array($couldbeA)) return $couldbeA;
        return false;
    }
    public static function getSalt(){
        return substr(uniqid(rand()), -6);
    }
    //生成密码
    public static function password($user_password, $user_salt) {
        return md5(md5($user_password).strval($user_salt));
    }
    //对象转化数组
    public static function obj2arr($obj) {
        return json_decode(json_encode($obj),true);
    }

    /**
     * 发送GET请求的方法
     * @param string $url URL
     * @param bool $ssl 是否为https协议
     * @return string 响应主体Content
     */
    public static function curlGet($url, $ssl=true) {
        // curl完成
        $curl = curl_init();

        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间

        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }
    /**
     * 发送GET请求的方法
     * @param string $url URL
     * @param bool $ssl 是否为https协议
     * @return string 响应主体Content
     */
    public static function curlPost($url, $data, $ssl=true) {
        //curl完成
        $curl = curl_init();
        //设置curl选项
        curl_setopt($curl, CURLOPT_URL, $url);//URL
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
        //SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
        }
        // 处理post相关选项
        curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
        // 处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

        // 发出请求
        $response = curl_exec($curl);
        if (false === $response) {
            echo '<br>', curl_error($curl), '<br>';
            return false;
        }
        curl_close($curl);
        return $response;
    }
}