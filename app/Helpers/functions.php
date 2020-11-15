<?php
use GuzzleHttp\Client;
 function index(){
        $grant_type = env("GRANT_TYPE");
		$appid = env("APP_Id");
		$secret = env("SECRET");
	    $http="https://api.weixin.qq.com/cgi-bin/token?grant_type=".$grant_type."&appid=".$appid."&secret=".$secret;
		$aa = file_get_contents($http);
		$aa = json_decode($aa,true);
	    $array = $aa['access_token'];
		return $array;
	}

	function success($error_msg){
	
	     echo json_encode(['error_no'=>0,'error_msg'=>$error_msg]);
	
	}
    function error($error_msg){
	
	     echo json_encode(['error_no'=>1,'error_msg'=>$error_msg]);exit;
	
    }
	//xml格式数据转换成数组函数
	function array_xml($a){
	
	   $obj = simplexml_load_string($a, "SimpleXMLElement", LIBXML_NOCDATA);
	   $datat = json_decode(json_encode($obj),true);
	   return $datat;
	
	}
      //发送post请求 （微信创建临时素材）
	  function clent_post($url,$images){
	    $client = new Client();
        $response = $client->request('POST',$url,[      
         'verify'    => false,    //忽略 HTTPS证书 验证
         'multipart' => [
         [
            'name'  => 'media',
            'contents'  => fopen($images,'r') //上传的文件路径]
           ],
         ]
       ]); 
		$json_str = $response->getBody();
		echo $json_str;
	  }
	  function clent_get($url){
	    $client = new Client();
        $response = $client->request("GET",$url,['verify' => false]);
		$json_str = $response->getBody();
		echo $json_str;
	  }

	  function client_menu($url,$menu){
	     
		  $client = new Client();
		  $response = $client->request('POST',$url,[

			  'verify' => false,
			  'body'=> json_encode($menu,JSON_UNESCAPED_UNICODE)
			  
		  ]);
		  $data = $response->getBody();
		  echo $data;
	  }
   
	function checkSignature(){

    $signature = $_GET["signature"];

    $timestamp = $_GET["timestamp"];

    $nonce = $_GET["nonce"];
	
    $token = env("WX_Token");

    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode( $tmpArr );
    $tmpStr = sha1( $tmpStr );
    
    if($tmpStr == $signature ){
        return true;
    }else{
        return false;
    }

	
  }
  function get_info($ip)
    {
    $url = "http://whois.pconline.com.cn/jsFunction.jsp?callback=jsShow&ip=".$ip;
    $ch = curl_init();
    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    $info = iconv('GB2312', 'UTF-8', $output); //因为是js调用 所以接收到的信息为字符串，注意编码格式
    preg_match_all("/[\x{4e00}-\x{9fa5}]+/u", $info, $regs);//preg_match_all（“正则表达式”,"截取的字符串","成功之后返回的结果集（是数组）"）
    $s = join('', $regs[0]);//join("可选。规定数组元素之间放置的内容。默认是 ""（空字符串）。","要组合为字符串的数组。")把数组元素组合为一个字符串
    $s = mb_substr($s, 0, 80, 'utf-8');//mb_substr用于字符串截取，可以防止中文乱码的情况
    return $s;

    }
    function ips(){
	
	 $ip = '117.132.192.126';//填写所要查找的ip地址
     $res = get_info($ip);
     $ips = mb_strlen($res)-3;
	 $ips = mb_substr($res,0,$ips);
	 echo $ips;
	}

	