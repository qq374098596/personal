<?php
namespace app\index\controller;
use think\Controller;
use app\lib\Qr;

class Weixin extends Controller{

	public function erweima()
	{
        $qr = new Qr();
		$weixin = $qr->getXcxCode('1','2');
        var_dump($weixin);exit;
	}

	public function getwxxiao($image){
        $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
        // if (strstr($image,",")){
        //     $image = explode(',',$image);
        //     $image = $image[1];
        // }
        // var_dump($image);exit;
        $path = "Uploads/".date("Ymd",time());
        // if (!is_dir(RESOURCEPATH.$path)){//判断目录是否存在 不存在就创建
        //   mkdir(RESOURCEPATH.$path,0777,true);
        // }
        if (!is_dir(RESOURCEPATH.$path)){
            if(!mkdir(RESOURCEPATH.$path, 0777)){
                return "";
            }
        };
        $imageSrc= RESOURCEPATH.$path."/". $imageName; //图片名字
        $r = file_put_contents($imageSrc, base64_decode($image));//返回的是字节数
        if (!$r){
            return "";
        }
        return $path."/".$imageName;

    }

    public function getwx($img){
        //生成图片 
        $imgDir = 'uploadImg/'; 
        $filename="nissangcj".''.".png";///要生成的图片名字 
        //$xmlstr = $img;
        //$xmlstr = $GLOBALS[HTTP_RAW_POST_DATA]; 
        /**if(empty($xmlstr)) { 
          $xmlstr = file_get_contents('php://input'); 
        }**/
        $jpg = $img;//得到post过来的二进制原始数据 
        if(empty($jpg)){
            echo "nostream";exit;
        }
       if (!is_dir(RESOURCEPATH.$imgDir)){
            if(!mkdir(RESOURCEPATH.$imgDir, 0777)){
                return json(['ret'=>1, 'msg'=>'目录创建失败']);
            }
        };
        $file = fopen(RESOURCEPATH.$imgDir.$filename,"w");//打开文件准备写入 
        fwrite($file,$jpg);//写入 
        fclose($file);//关闭 
        $filePath = './'.$imgDir.$filename; 
        //图片是否存在 
        if(!file_exists($filePath)) {
        }
    }

	private function curl($url='',$type='',$data='')
    {
        $ch = curl_init();
        // 2. 设置选项，包括URL
        if ($type == 'post') {
        	curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($data != '') {
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


        // 3. 执行并获取HTML文档内容
        $output = curl_exec($ch);
        // if($output === FALSE ){
        //     echo "CURL Error:".curl_error($ch);
        // }
        // 4. 释放curl句柄
        curl_close($ch);
        return $output;
    }
}

?>