<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Model\User;
use App\Model\PWxMedia;
use App\Model\Goods;
use App\Model\HistoryModel;
class WechatController extends Controller
{
    public function wechat(){
            $str = file_get_contents("php://input");

			file_put_contents("ddd.txt",$str);

            $obj = simplexml_load_string($str, "SimpleXMLElement", LIBXML_NOCDATA);

            switch ($obj->MsgType) {
                case 'event':
                    if ($obj->Event == "subscribe") {
                        //用户扫码的 openID
                        $openid = $obj->FromUserName;//获取发送方的 openid
                        $access_token = $this->assecc_token();//获取token,
                        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
                        //掉接口
                        $user = json_decode($this->http_get($url), true);//跳方法 用get  方式调第三方类库

                        // $this->writeLog($fens);
                        if (isset($user["errcode"])) {
                            file_put_contents("bbb.txt",$user["errcode"]);
                            $this->writeLog("获取用户信息失败");
                        } else {
                            //说明查找成功 //可以加入数据库
                            $first =  User::where("openid",$user['openid'])->first();
                            if($first){
                             $data =[
                                    "subscribe"=>1,
                                    "openid"=>$user["openid"],
                                    "nickname"=>$user["nickname"],
                                    "sex"=>$user["sex"],
                                    "city"=>$user["city"],
                                    "country"=>$user["country"],
                                    "province"=>$user["province"],
                                    "language"=>$user["language"],
                                    "headimgurl"=>$user["headimgurl"],
                                    "subscribe_time"=>$user["subscribe_time"],
                                    "subscribe_scene"=>$user["subscribe_scene"],
                              ];

                                     User::where("openid",$user['openid'])->update($data);
                                     $content ="欢迎回来";
                             }else{
                             $users = new User();
                             $data =[
                                    "subscribe"=>$user["subscribe"],
                                    "openid"=>$user["openid"],
                                    "nickname"=>$user["nickname"],
                                    "sex"=>$user["sex"],
                                    "city"=>$user["city"],
                                    "country"=>$user["country"],
                                    "province"=>$user["province"],
                                    "language"=>$user["language"],
                                    "headimgurl"=>$user["headimgurl"],
                                    "subscribe_time"=>$user["subscribe_time"],
                                    "subscribe_scene"=>$user["subscribe_scene"],
                              ];
                            $users->insert($data);
                            $content = "您好!感谢您的关注";
                          }
                        }
                    }
                    if ($obj->Event == "unsubscribe") {
                          //用户扫码的 openID
                        $openid = $obj->FromUserName;//获取发送方的 openid
                        $access_token = $this->assecc_token();//获取token,
                        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
                        //掉接口
                        $user = json_decode($this->http_get($url), true);//跳方法 用get  方式调第三方类库
                        User::where("openid",$user['openid'])->update(['subscribe'=>0]);
                        $content = "取消关注成功,期待您下次关注";
                    }
					if($obj->Event == "pic_photo_or_album"){
					
					   $content = "";
					}
					if($obj->Event == "pic_weixin"){
					
					   $content = "";
					}
					if($obj->Event == "VIEW"){


						if($obj->EventKey == "http://www.wangshuxin.top"){
					

						 $content = "";
						
						}else{

						 $content = "";

						}
					   
					}
					 if ($obj->Event == "CLICK") {
						 if($obj->EventKey=="wx_521"){
                              $key = $obj->FromUserName;
							  $times = date("Ymd",time());
							  //$times = "20201111";
                              $date = Redis::zrange($key,0,-1);
							  if($date){
							      $date = $date[0];
							  }
						       if($date==$times){   
									 $content = "您今日已经签到过了!";
								 }elseif(intval($times)-intval($date)>=2&&!empty($date)){
									   $keys = array_xml($str);
									   $keys = $keys['FromUserName'];
                                       Redis::zremrangebyrank($key,0,0);
									   Redis::zadd($key,1,$times);
									   $score = Redis::zincrby($key,-1,$keys);
									   Redis::zincrby($key,-intval($score)+1,$keys);
									   $score = Redis::incrby($keys."_score",100);
									   $content = "签到成功，由于您之前没有进行签到，今日已积累签到第一天,已拥有".$score."积分";
                                 }else{
									 $zcard = Redis::zcard($key);
									 if($zcard>=1){
										 Redis::zremrangebyrank($key,0,0);
									}
									 $keys = array_xml($str);
                                     $keys = $keys['FromUserName'];
									 $zincrby = Redis::zincrby($key,1,$keys);
							         Redis::zadd($key,$zincrby,$times);
	                                 $score = Redis::incrby($keys."_score",100);
	                             
					            	 $content="签到成功您以积累签到".$zincrby."天!"."您以积累获得".$score."积分";  
							   }
						 }else if($obj->EventKey=="wx_data"){
                                echo $this->picture($obj);exit;
                         }else{
							//$address = ips();
						    $city =  urlencode("昌平");
                            $key = "2f3d1615c28f0a5bc54da5082c4c1c0c";
                            $url = "http://apis.juhe.cn/simpleWeather/query?city=".$city."&key=".$key;
                            $user = json_decode($this->http_get($url), true);//跳方法 用get  方式调第三方类库
                            if($user['reason']=="查询成功!"){
                                $content = $user['result']['city']."天气情况:".
                                    "\r\n"."天气:".$user['result']['realtime']['info'].
                                    "\r\n"."温度:".$user['result']['realtime']['temperature'].
                                    "\r\n"."湿度:".$user['result']['realtime']['humidity'].
                                    "\r\n"."风向:".$user['result']['realtime']['direct'].
                                    "\r\n"."风力:".$user['result']['realtime']['power'].
                                    "\r\n"."空气质量:".$user['result']['realtime']['aqi'].
                                    "\r\n"."近五天天气情况如下:".
                                    "\r\n".$user['result']['future'][0]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][0]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][0]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][0]['direct'].
                                    "\r\n".$user['result']['future'][1]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][1]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][1]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][1]['direct'].
                                    "\r\n".$user['result']['future'][2]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][2]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][2]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][2]['direct'].
                                    "\r\n".$user['result']['future'][3]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][3]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][3]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][3]['direct'].
                                    "\r\n".$user['result']['future'][4]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][4]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][4]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][4]['direct'];
                         } 
					  }
                    }
                    break;
                case 'text':
					    $key = "284fc0755b050a79ab2895c9a5566588";
						$text = $obj->Content;
						$touser = $obj->FromUserName;

						$keys = "select_".$text;
						$contents = Redis::get($keys);
						$contents = unserialize($contents);

						if($contents){

							if($contents["code"]=='200'){
							
								$content = "redis查询:".$contents['newslist'][0]['pinyin'];
								$data = [
								   "touser"=>$touser,
								   'contents'=>$content,
								   'time'=>time()
								];
								HistoryModel::insert($data);
							
							}
						  

						}else{
						
						    $url = "http://api.tianapi.com/txapi/pinyin/index?key=".$key."&text=".$text;
						    $contents = json_decode(file_get_contents($url),true);
						    Redis::set($keys,serialize($contents));
						   	if($contents["code"]=='200'){
						
							$content = "首次查询:".$contents['newslist'][0]['pinyin'];
							$data = [
							   "touser"=>$touser,
							   'contents'=>$content,
							   'time'=>time()
							];
							HistoryModel::insert($data);	
						  }
						}


					
						
						
					/*
                        if ($obj->Content == "天气") {
                            $content = "您好,请输入您想查询的您的地区的天气，比如:'北京'";
                        }else{
                            $city =  urlencode($obj->Content);
                            $key = "2f3d1615c28f0a5bc54da5082c4c1c0c";
                            $url = "http://apis.juhe.cn/simpleWeather/query?city=".$city."&key=".$key;
                            $user = json_decode($this->http_get($url), true);//跳方法 用get  方式调第三方类库
                            if($user['reason']=="查询成功!"){
                                $content = $user['result']['city']."天气情况:".
                                    "\r\n"."天气:".$user['result']['realtime']['info'].
                                    "\r\n"."温度:".$user['result']['realtime']['temperature'].
                                    "\r\n"."湿度:".$user['result']['realtime']['humidity'].
                                    "\r\n"."风向:".$user['result']['realtime']['direct'].
                                    "\r\n"."风力:".$user['result']['realtime']['power'].
                                    "\r\n"."空气质量:".$user['result']['realtime']['aqi'].
                                    "\r\n"."近五天天气情况如下:".
                                    "\r\n".$user['result']['future'][0]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][0]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][0]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][0]['direct'].
                                    "\r\n".$user['result']['future'][1]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][1]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][1]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][1]['direct'].
                                    "\r\n".$user['result']['future'][2]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][2]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][2]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][2]['direct'].
                                    "\r\n".$user['result']['future'][3]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][3]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][3]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][3]['direct'].
                                    "\r\n".$user['result']['future'][4]['date'].":".
                                    "\r\n"."天气:".$user['result']['future'][4]['weather'].
                                    "\r\n"."温度:".$user['result']['future'][4]['temperature'].
                                    "\r\n"."风向:".$user['result']['future'][4]['direct'];
                            }else{
                               
                                $apiKey="3537d051f0ec483e86f81fbc8689ec9d";
                                $perception = $obj->Content;
                                $url = "http://openapi.tuling123.com/openapi/api/v2";
                                $data  = [
                                    'perception'=>[
                                        'inputText'=>[
                                            'text'=>$perception
                                        ],
                                    ],
                                    'userInfo'=>[
                                        'apiKey'=>$apiKey,
                                        'userId'=>'520',
                                    ],
                                ];
                                $data = json_encode($data);


								$datas = json_decode($this->curl($url,$data),true);

								$content = $datas['results'][0]['values']['text'];

                            }
                        }
						*/
                    break;
                case "voice":
					  $apiKey="3537d051f0ec483e86f81fbc8689ec9d";
	                  $perception = $obj->Recognition;
		              $url = "http://openapi.tuling123.com/openapi/api/v2";

                               $data  = [
                                    'perception'=>[
                                        'inputText'=>[
                                            'text'=>$perception
                                        ],
                                    ],
                                    'userInfo'=>[
                                        'apiKey'=>$apiKey,
                                        'userId'=>'520',
                                    ],
                                ];
                                $data = json_encode($data);
								$datas = json_decode($this->curl($url,$data),true);

                                 $access_token = $this->assecc_token();
                                    $datax = [
                                        "tousername"=>$obj->ToUserName,
                                        "fromusername"=>$obj->FromUserName,
                                        "msgtype"=>$obj->MsgType,
                                        "content"=>$obj->Content,
                                        "msgid" =>$obj->MsgId,
                                        "createtime"=>$obj->CreateTime,
                                        "mediaid"=>$obj->MediaId,
                                        "format"=>$obj->Format,
                                        "recognition"=>$obj->Recognition,
                                        "picurl"=>$obj->PicUrl,
                                        "event"=>$obj->Event,
                                        "eventkey"=>$obj->EventKey
                                    ];
                                 PWxMedia::insert($datax);
					             $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$obj->MediaId;
					             $get = file_get_contents($url);
                                 $uploads_dir = './voice/'.date('Y-m-d H:i:s',time());
                                 if (!file_exists($uploads_dir)) {
                                              mkdir($uploads_dir,0777,true);
                                 }
                                 file_put_contents($uploads_dir."/voice.amr",$get);

								$content = $datas['results'][0]['values']['text'];

				break;
				case "image":
				    
				    			$data = [
		                              "tousername"=>$obj->ToUserName,  
									  "fromusername"=>$obj->FromUserName,
									  "msgtype"=>$obj->MsgType,
									  "content"=>$obj->Content,
									  "msgid" =>$obj->MsgId,
									  "createtime"=>$obj->CreateTime,
									  "mediaid"=>$obj->MediaId,
									  "format"=>$obj->Format,
									  "recognition"=>$obj->Recognition,
									  "picurl"=>$obj->PicUrl,
									  "event"=>$obj->Event,
									  "eventkey"=>$obj->EventKey
	                              ];
					$access_token = $this->assecc_token();
                    PWxMedia::insert($data);
                    $url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$obj->MediaId;
					$get = file_get_contents($url);
                    $uploads_dir = './image/'.date('Y-m-d H:i:s',time());
                    if (!file_exists($uploads_dir)) {
                        mkdir($uploads_dir,0777,true);
                    }
					file_put_contents($uploads_dir."/image.jpg",$get);
				    $content ="此功能暂时还未开放，您可以发消息与图灵机器人'小柯'进行交流或者输入'天气'查询某地区的天气状况，更多功能正在火速进行中，尽请期待。。。";
				break;
				case "video":
					$access_token = $this->assecc_token();
					$url="https://api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$obj->MediaId;
					$get = file_get_contents($url);
                    $datax = [
                        "tousername"=>$obj->ToUserName,
                        "fromusername"=>$obj->FromUserName,
                        "msgtype"=>$obj->MsgType,
                        "content"=>$obj->Content,
                        "msgid" =>$obj->MsgId,
                        "createtime"=>$obj->CreateTime,
                        "mediaid"=>$obj->MediaId,
                        "format"=>$obj->Format,
                        "recognition"=>$obj->Recognition,
                        "picurl"=>$obj->PicUrl,
                        "event"=>$obj->Event,
                        "eventkey"=>$obj->EventKey
                    ];
                    PWxMedia::insert($datax);
                    $uploads_dir = './video/'.date('Y-m-d H:i:s',time());
                    if (!file_exists($uploads_dir)) {
                        mkdir($uploads_dir,0777,true);
                    }
                    file_put_contents($uploads_dir."/video.mp4",$get);
				    $content ="此功能暂时还未开放，您可以发消息与图灵机器人'小柯'进行交流或者输入'天气'查询某地区的天气状况，更多功能正在火速进行中，尽请期待。。。";

				break;
				default:
                 $content="表达式的值不等于 label1 及 label2 时执行的代码";
            }
            echo $this->xiaoxi($obj, $content);
	}
    public function code(){
       $code = $_GET['code'];

	   $appid = env("APP_Id");
 
       $refresh_token = $this->assecc_token();

	   $secret = env("SECRET");

	   $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";

	   $array = json_decode(file_get_contents($url),true);

       $refresh_token = $array['refresh_token'];

	   $user = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$appid."&grant_type=refresh_token&refresh_token=".$refresh_token;
	   
	   $get = json_decode(file_get_contents($user),true);

	   $access_token = $get["access_token"];

	   $openid = $get["openid"];

	   $users = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";

	   $xinxi = json_decode(file_get_contents($users),true);

	  if($xinxi){
	  
	    return redirect("http://www.wangshuxin.top/");
	  }
  }
    public function assecc_token(){
	  $key = "AccessToken";
	  $get = Redis::get($key);
	  if(!$get){
		  $get = index();
		  Redis::set($key,$get);
		  Redis::expire($key,3600);
	  }
	  return $get;
  }
    private  function writeLog($data){
        if(is_object($data) ||is_array($data)){
            $data=json_encode($data);
        }
        file_put_contents("aaa.txt",$data);die;
    }
    function xiaoxi($obj,$content){
        //我们可以恢复一个文本|图片|视图|音乐|图文列如文本
        //接收方账号
        $toUserName=$obj->FromUserName;
        //开发者微信号
        $fromUserName=$obj->ToUserName;
        //时间戳
        $time=time();
        //返回类型
        $msgType="text";

        $xml = "<xml>
                      <ToUserName><![CDATA[%s]]></ToUserName>
                      <FromUserName><![CDATA[%s]]></FromUserName>
                      <CreateTime>%s</CreateTime>
                      <MsgType><![CDATA[%s]]></MsgType>
                      <Content><![CDATA[%s]]></Content>
                    </xml>";
        //替换掉上面的参数用 sprintf
        echo sprintf($xml,$toUserName,$fromUserName,$time,$msgType,$content);
    }
    function http_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);//向那个url地址上面发送
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//设置发送http请求时需不需要证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置发送成功后要不要输出1 不输出，0输出
        $output = curl_exec($ch);//执行
        curl_close($ch);    //关闭
        return $output;
    }
    public function curl($url,$menu){
        //1.初始化
        $ch = curl_init();
        //2.设置
        curl_setopt($ch,CURLOPT_URL,$url);//设置提交地址
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//设置返回值返回字符串
        curl_setopt($ch,CURLOPT_POST,1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$menu);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //3.执行
        $output = curl_exec($ch);
        //关闭
        curl_close($ch);
        return $output;
    }
    //回复图文
    public function picture($obj){

        $count = Goods::count();

        $rand = rand(1,$count);

        $goods = Goods::select("goods_name","goods_desc","goods_img","goods_url")->orderBy("goods_id","asc")->where("goods_id",$rand)->first()->toArray();

        $goods_name = $goods['goods_name'];

        $goods_desc = $goods['goods_desc'];

        $goods_img ="http://www.wangshuxin.top/".$goods['goods_img'];

        $goods_url = $goods['goods_url'];

        $touser = $obj->FromUserName;

        $fromuser = $obj->ToUserName;
        $xml = "<xml>
                  <ToUserName><![CDATA[".$touser."]]></ToUserName>
                  <FromUserName><![CDATA[".$fromuser."]]></FromUserName>
                  <CreateTime>time()</CreateTime>
                  <MsgType><![CDATA[news]]></MsgType>
                  <ArticleCount>1</ArticleCount>
                  <Articles>
                    <item>
                      <Title><![CDATA[".$goods_name."]]></Title>
                      <Description><![CDATA[".$goods_desc."]]></Description>
                      <PicUrl><![CDATA[".$goods_img."]]></PicUrl>
                      <Url><![CDATA[".$goods_url."]]></Url>
                    </item>
                  </Articles>
               </xml>";
        echo $xml;
    }
	public function history(){
	
	   
	    $history = HistoryModel::orderBy("time","desc")->limit(10)->get()->toArray();

		foreach($history as $k=>$v){
		
		   echo date("Y-m-d H:i",$v['time'])."\r\n".$v['contents']. "\r\n";
		
		}
       
		
	}
}
