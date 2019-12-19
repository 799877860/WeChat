<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
class VoteController extends Controller
{
    public function index()
    {
//        echo "<pre>";print_r($_GET);echo "</pre>";

        $code = $_GET['code'];

        // 获取access_token
        $data = $this->getAccessToken($code);
        // 获取用户信息
        $user_info = $this->getUserInfo($data['access_token'],$data['openid']);

        // 处理业务逻辑
        $openid = $user_info['openid'];
        $key = 'ss:vote:wanyang';

        //   TODO    判断是否已经投过    使用redis集合 或 有序集合
        if (Redis::zrank($key,$user_info['openid'])){
            echo "已经投过票了";
        }else{
            Redis::Zadd($key,time(),$openid);
        }
        $mumbers = Redis::zRange($key,0,-1,true);       // 获取所有投票人的openID
        echo "<pre>";print_r($mumbers);echo "</pre>";die;
        $total = Redis::Scard($key);        // 统计投票总人数
        echo "投票总人数：".$total;

    }

    /**
     * 根据code获取access_token
     */
    protected function getAccessToken($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET').'&code='.$code.'&grant_type=authorization_code';
        $json_data = file_get_contents($url);
        $data = json_decode($json_data,true);
//        echo "<pre>";print_r($data);echo "</pre>";
        if (isset($data['errcode'])){
            // TODO 错误处理
            die("出错了   40001");     // 40001   表示获取access_token失败
        }
        return $data;       // 返回access_token信息
    }

    /**
     * 获取用户基本信息
     */
    protected function getUserInfo($access_token,$openid)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $json_data = file_get_contents($url);
        $data = json_decode($json_data,true);
//        echo "<pre>";print_r($user_info);echo "</pre>";
        if (isset($data['errcode'])){
            // TODO 错误处理
            die("出错了   40001");     // 40001   表示获取用户信息失败
        }
        return $data;       // 返回用户信息

    }
}
