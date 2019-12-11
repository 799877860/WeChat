<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WechatController extends Controller
{

    protected $access_token;

    public function __construct()
    {
        // 获取access_token
        $this->access_token = $this->getAccessToken();

    }

    /**
     * 获取access_token
     */
    protected function getAccessToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
        $data_json = file_get_contents($url);
        $arr = json_decode($data_json,true);
        return $arr['access_token'];
    }

    /**
     * 处理接入请求
     */
    public function checkSignature()
	{
		$token = '981118';
	    $signature = $_GET["signature"];
	    $timestamp = $_GET["timestamp"];
	    $nonce = $_GET["nonce"];
		$echostr = $_GET['echostr'];

	    $tmpArr = array($token, $timestamp, $nonce);
	    sort($tmpArr, SORT_STRING);
	    $tmpStr = implode( $tmpArr );
	    $tmpStr = sha1( $tmpStr );

	    if( $tmpStr == $signature ){
	        echo $echostr;
	    }else{
	        die('Not OK!');
	    }
	}


    /**
     * 获取用户基本信息
     */
    public function getUserInfo($access_token,$openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        // 发送网络请求
        $json_str = file_get_contents($url);
        $log_file = 'wx_user.log';
        file_put_contents($log_file,$json_str,FILE_APPEND);
    }

    /**
     * 接收微信推送事件
     */
	public function receiv()
    {
        $log_file = "wx.log";
        // 将接受的数据记录到日志文件中
        $xml_str = file_get_contents('php://input');
        $data = date('Y-m-d H:i:s') . $xml_str;
        file_put_contents($log_file,$data,FILE_APPEND);     //追加写入

        // 处理xml数据
        $xml_obj = simplexml_load_string($xml_str);

        $event = $xml_obj->Event;       //获取事件类型
        if ($event=='subscribe'){
            // 获取用户的openID
            $openid = $xml_obj->FromUserName;
            // 获取用户信息
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
            $user_info = file_get_contents($url);       //
            file_put_contents('wx_user.log',$user_info,FILE_APPEND);
        }

        //判断消息类型
        $msg_type = $xml_obj->msgType;

        $toUser = $xml_obj->FromUserName;       //接收回复消息用户的openID
        $fromUser = $xml_obj->ToUserName;       //开发者公众号的ID
        $time = time();
        $content = date('Y-m-d H:i:s') . $xml_obj->Content;

        if ($msg_type=='text'){
            $response_text = '<xml>
                  <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                  <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
                  <CreateTime>'.$time.'</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA[你好]]></Content>
                </xml>';
            echo $response_text;        //回复用户消息

        }
    }
}
