<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class TestController extends Controller
{
	public function test()
	{
		echo "Hello World!啊啊啊啊";
	}

	public function redis1()
    {
    	$key = 'wechat';
    	$value = 'wys';
    	Redis::set($key,$value);
    	echo time();
    	echo "</br>";
    	echo date('Y-m-d H:i:s');
		echo "</br>";
		echo Redis::get($key);
    }

    public function guzzle1()
    {
    	$url = "http://baijiahao.baidu.com/s?id=1652068021212894503";
    	$client = new Client();
    	$response = $client->request('GET',$url);
    	echo $response->getBody();
    }

    public function xmlTest()
    {
        $xml_str = "
        <xml>
            <ToUserName><![CDATA[gh_b4f025fdb07b]]></ToUserName>
            <FromUserName><![CDATA[osgsMxMbmUxMyYTHPW_j2sit27T0]]></FromUserName>
            <CreateTime>1575888950</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[asd]]></Content>
            <MsgId>22561306592957963</MsgId>
        </xml>";

        $xml_obj = simplexml_load_string($xml_str);
        echo '<pre>';print_r($xml_obj);echo '</pre>';echo "<hr>";
        echo 'ToUserName'.$xml_obj->ToUserName;echo "<hr>";
        echo 'FromUserName'.$xml_obj->FromUserName;echo "<hr>";

    }
}
