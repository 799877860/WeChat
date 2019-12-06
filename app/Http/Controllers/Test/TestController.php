<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

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
}
