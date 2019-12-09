<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public function checkSignature()
	{
        /**
         * 处理接入请求
         */
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
    public function getUserInfo()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN";
    }
    /**
     * 接收微信推送事件
     */
	public function receiv()
    {
        $log_file = "wx.log";
        //将接受的数据记录到日志文件中
        $xml = file_get_contents('php://input');
        $data = date('Y-m-d H:i:s') . $xml;
        file_put_contents($log_file,$data,FILE_APPEND);     //追加写入
    }
}
