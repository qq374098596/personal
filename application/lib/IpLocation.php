<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 11:20
 */

namespace app\lib;


class IpLocation {
    var $fp;
    var $firstip; //第一条ip索引的偏移地址
    var $lastip; //最后一条ip索引的偏移地址
    var $totalip; //总ip数
    public static $ipl = null;
    public static function getIPAddress() {
        if (!IpLocation::$ipl) IpLocation::$ipl = new IpLocation();
        $ip = IpLocation::$ipl->getIP();
        if (!$ip) return false;
        $address = IpLocation::$ipl->getaddress($ip);
        return mb_convert_encoding($address["area1"].$address["area2"], 'utf-8', 'GB2312');
    }
    public static function getIPAddress2() {
        if (!IpLocation::$ipl) IpLocation::$ipl = new IpLocation();
        $ip = IpLocation::$ipl->getIP();
        if (!$ip) return false;
        return IpLocation::$ipl->getaddress($ip);
    }
    public static function getAddressByIP($ip) {
        if (!$ip) return "未知";
        if (!IpLocation::$ipl) IpLocation::$ipl = new IpLocation();
        $address = IpLocation::$ipl->getaddress($ip);
        return mb_convert_encoding($address["area1"].$address["area2"], 'utf-8', 'GB2312');
    }
    public function __construct() {
        $datfile = 'QQWry.Dat';
        $this->fp = fopen($datfile, 'rb') or die("QQWry.Dat不存在，请去网上下載纯真IP数据库, 'QQWry.dat' 放到当前目录下"); //二制方式打开
        $this->firstip = $this->get4b(); //第一条ip索引的绝对偏移地址
        $this->lastip = $this->get4b(); //最后一条ip索引的绝对偏移地址
        $this->totalip = ($this->lastip - $this->firstip)/7; //ip總數 索引区是定长的7个字节,在此要除以7,
        register_shutdown_function(array($this, "closefp")); //为了兼容php5以下版本,本类没有用析构函数,自动关闭ip库.
    }
    function closefp() {
        fclose($this->fp);
    }
    function get4b() {
        $str = unpack("V", fread($this->fp, 4));
        return $str[1];
    }
    function getoffset() {
        $str = unpack("V", fread($this->fp, 3).chr(0));
        return $str[1];
    }
    function getstr() {
        $str = '';
        $split = fread($this->fp, 1);
        while (ord($split)!=0) {
            $str .= $split;
            $split = fread($this->fp, 1);
        }
        return $str;
    }
    function iptoint($ip) {
        return pack("N", intval(ip2long($ip)));
    }
    public static function getIP() {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('remote_addr') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER ['REMOTE_ADDR'] && strcasecmp($_SERVER ['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        preg_match('/[\d\.]{7,15}/', $onlineip, $onlineipmatches);
        return isset($onlineipmatches[0]) ? $onlineipmatches [0] : false;
    }
    function readaddress() {
        $now_offset = ftell($this->fp); //得到当前的指针位址
        $flag = $this->getflag();
        switch (ord($flag)) {
            case 0:
                $address = "";
                break;
            case 1:
            case 2:
                fseek($this->fp, $this->getoffset());
                $address = $this->getstr();
                break;
            default:
                fseek($this->fp, $now_offset);
                $address = $this->getstr();
                break;
        }
        return $address;
    }
    function getflag() {
        return fread($this->fp,1);
    }
    function searchip($ip) {
        $ip = gethostbyname($ip); //将域名转成ip
        $ip_offset["ip"] = $ip;
        $ip = $this->iptoint($ip); //将ip转换成长整型
        $firstip = 0; //搜索的上边界
        $lastip = $this->totalip; //搜索的下边界
        $ipoffset = $this->lastip; //初始化为最后一条ip地址的偏移地址
        while ($firstip <= $lastip) {
            $i = floor(($firstip + $lastip) / 2); //计算近似中间记录 floor函数记算给定浮点数小的最大整数,说白了就是四舍五也舍
            fseek($this->fp, $this->firstip + $i * 7); //定位指针到中间记录
            $startip = strrev(fread($this->fp, 4)); //读取当前索引区内的开始ip地址,并将其little-endian的字节序转换成big-endian的字节序
            if ($ip < $startip) {
                $lastip = $i - 1;
            } else {
                fseek($this->fp, $this->getoffset());
                $endip = strrev(fread($this->fp, 4));
                if ($ip > $endip) {
                    $firstip = $i + 1;
                } else {
                    $ip_offset["offset"] = $this->firstip + $i * 7;
                    break;
                }
            }
        }
        return $ip_offset;
    }
    function getaddress($ip) {
        $ip_offset = $this->searchip($ip); //获取ip 在索引区内的绝对编移地址
        $ipoffset = $ip_offset["offset"];
        $address["ip"] = $ip_offset["ip"];
        fseek($this->fp, $ipoffset); //定位到索引区
        $address["startip"] = long2ip($this->get4b()); //索引区内的开始ip 地址
        $address_offset = $this->getoffset(); //获取索引区内ip在ip记录区内的偏移地址
        fseek($this->fp, $address_offset); //定位到记录区内
        $address["endip"] = long2ip($this->get4b()); //记录区内的结束ip 地址
        $flag = $this->getflag(); //读取标志字节
        switch (ord($flag)) {
            case 1: //地区1地区2都重定向
                $address_offset = $this->getoffset(); //读取重定向地址
                fseek($this->fp, $address_offset); //定位指针到重定向的地址
                $flag = $this->getflag(); //读取标志字节
                switch (ord($flag)) {
                    case 2: //地区1又一次重定向,
                        fseek($this->fp, $this->getoffset());
                        $address["area1"] = $this->getstr();
                        fseek($this->fp, $address_offset+4); //跳4个字节
                        $address["area2"] = $this->readaddress(); //地区2有可能重定向,有可能没有
                        break;
                    default: //地区1,地区2都没有重定向
                        fseek($this->fp, $address_offset); //定位指针到重定向的地址
                        $address["area1"] = $this->getstr();
                        $address["area2"] = $this->readaddress();
                        break;
                }
                break;
            case 2: //地区1重定向 地区2没有重定向
                $address1_offset = $this->getoffset(); //读取重定向地址
                fseek($this->fp, $address1_offset);
                $address["area1"] = $this->getstr();
                fseek($this->fp, $address_offset+8);
                $address["area2"] = $this->readaddress();
                break;
            default: //地区1地区2都没有重定向
                fseek($this->fp, $address_offset+4);
                $address["area1"] = $this->getstr();
                $address["area2"] = $this->readaddress();
                break;
        }
        //*过滤一些无用数据
        if (strpos($address["area1"],"CZ88.NET") != false) {
            $address["area1"] = "未知";
        }
        if (strpos($address["area2"],"CZ88.NET") != false) {
            $address["area2"] = "";
        }
        return $address;
    }
}