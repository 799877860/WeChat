<?php

namespace App\Admin\Controllers;

use App\Model\WxUserModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use function foo\func;
use GuzzleHttp\Client;
class WxMsgController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '微信用户管理';

    public function sendMsg()
    {
        echo __METHOD__;
        $openid_arr = WxUserModel::select('openid','nickname','sex')->get()->toArray();
        // echo "<pre>";print_r($openid_arr);echo "</pre>";

        $openid = array_column($openid_arr,'openid');
        echo "<pre>";print_r($openid);echo "</pre>";
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=28_QdAUq5hOvc6Kzt_5sEMyLE88f9vt17YLlusDNz6YTmYERbGw3f8dKABMyge9MPzLloFpFyCaT1Ir3fWmREA2yCOzZjMbyuaJpETI6VhVr2VJqxmEPaSh8bMjstaawDraryC9VXTqe5Bu0zgMCFQfAHAKNY';

        $msg = date('Y-m-d H:i:s') . ' You are my sunshine';

        $data = [
            'touser'    =>$openid,
            'msgtype'   =>'text',
            'text'      =>['content'=>$msg]
        ];

        $client = new Client();
        $response = $client->request('POST',$url,[
            'body'  =>  json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        echo $response->getBody();
    }
}
