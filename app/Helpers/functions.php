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

	