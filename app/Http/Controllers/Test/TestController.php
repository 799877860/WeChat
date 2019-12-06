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
}
