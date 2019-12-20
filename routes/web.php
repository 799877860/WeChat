<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::any('info',function(){
    phpinfo();
});

// 测试方法
Route::any('test/hello','Test\TestController@test');
Route::any('test/redis1','Test\TestController@redis1');
Route::any('test/guzzle1','Test\TestController@guzzle1');
Route::any('test/adduser','User\LoginController@addUser');
Route::any('test/xml','Test\TestController@xmlTest');
Route::any('dev/redis/del','VoteController@delKey');

// 微商城
Route::any('/','Index\IndexController@index');      // 商城首页
Route::any('goods/detail','Goods\IndexController@detail');      // 商城首页

// 微信开发
Route::get('wechat','Wechat\WechatController@checkSignature');
Route::post('wechat','Wechat\WechatController@receiv');         //接收微信推送事件
Route::get('wechat/media','Wechat\WechatController@testMedia');         //获取临时素材
Route::get('wechat/ext',function (){
        $file_name = 'av.mp3';
        $info = pathinfo($file_name);

        echo $file_name . "的文件扩展名为：" . $info['extension'];die;
        echo "<pre>";print_r($info);echo "</pre>";
});         //获取文件拓展名测试
Route::get('wechat/test','Wechat\WechatController@test');         //获取临时素材
Route::get('wechat/flush','Wechat\WechatController@flushAccessToken');         //刷新access_token
Route::get('wechat/menu','Wechat\WechatController@createMenu');         //创建菜单
Route::get('vote','VoteController@index');         //微信投票

