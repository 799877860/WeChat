<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;
use Illuminate\Support\Facades\Redis;

use GuzzleHttp\Client;

class WechatController extends Controller
{

    protected $access_token;

    public function __construct()
    {
        // 获取access_token
        $this->access_token = $this->getAccessToken();

    }

    public function test()
    {
        echo $this->access_token;
    }

    /**
     * 获取access_token
     */
    protected function getAccessToken()
    {
        $key = 'wx_access_token';

        $access_token = Redis::get($key);
        if ($access_token){
            return $access_token;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
        $data_json = file_get_contents($url);
        $arr = json_decode($data_json,true);

        Redis::set($key,$arr['access_token']);
        Redis::expire($key,3600);       //过期时间
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
        $data = date('Y-m-d H:i:s') . " >>>>>> \n" . $xml_str . "\n\n";
        file_put_contents($log_file,$data,FILE_APPEND);     //追加写入

        // 处理xml数据
        $xml_obj = simplexml_load_string($xml_str);

        $event = $xml_obj->Event;       //获取事件类型
        $openid = $xml_obj->FromUserName;       // 获取用户的openID

        if ($event=='subscribe'){
            //判断用户是不是已存在
            $u = WxUserModel::where(['openid' => $openid])->first();
            if ($u){
                //TODO  How old are you ?
                $msg = '怎么又是你 ?';
                $xml = '<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$xml_obj->ToUserName.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA['.$msg.']]></Content>
                </xml>';
                echo $xml;
            }else{
//                echo __LINE__;die;

                // 获取用户信息
                $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
                $user_info = file_get_contents($url);       //
//                echo "<pre>";print_r($u);echo "</pre>";die;
                $u = json_decode($user_info,true);
                //入库用户信息
                $user_data = [
                    'openid'       => $openid,
                    'nickname'    => $u['nickname'],
                    'sex'           => $u['sex'],
                    'headimgurl'  => $u['headimgurl'],
                    'subscribe_time'=> $u['subscribe_time']
                ];

                //openID入库
                $uid = WxUserModel::insertGetId($user_data);

                //回复用户关注
                $msg = '怎么是你 ?';
                $xml = '<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$xml_obj->ToUserName.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA['.$msg.']]></Content>
                </xml>';
                    echo $xml;
            }
        }elseif($event=='CLICK'){       // 菜单点击事件
            // 如果是获取天气
            if ($xml_obj->EventKey=='weather'){

                // 请求第三方接口  获取天气
                $weather_api = 'https://free-api.heweather.net/s6/weather/now?location=auto_ip&key=7e8a753e3c60449287b7cf0c3d601c38';
                $weather_info = file_get_contents($weather_api);
                $weather_info_arr = json_decode($weather_info,true);
                // print_r($weather_info_arr);die;

                $cnty = $weather_info_arr['HeWeather6'][0]['basic']['cnty'];
                $location = $weather_info_arr['HeWeather6'][0]['basic']['location'];
                $cond_txt = $weather_info_arr['HeWeather6'][0]['now']['cond_txt'];
                $tmp = $weather_info_arr['HeWeather6'][0]['now']['tmp'];
                $wind_dir = $weather_info_arr['HeWeather6'][0]['now']['wind_dir'];

                $msg = $cnty . ' ' . $location . ' ' .  $cond_txt . ' 温度:' . $tmp . '℃' . ' 风向:' . $wind_dir;
                // echo $msg;die;
                $response_xml = '<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$xml_obj->ToUserName.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA['.date('Y-m-d H:i:s ') . $msg . ']]></Content>
                </xml>';
                echo $response_xml;
            }
        }

        //判断消息类型
        $msg_type = $xml_obj->MsgType;

        $toUser = $xml_obj->FromUserName;       //接收回复消息用户的openID
        $fromUser = $xml_obj->ToUserName;       //开发者公众号的ID
        $time = time();

        $media_id = $xml_obj->MediaId;

        if ($msg_type=='text'){                 // 文本消息

            $content = date('Y-m-d H:i:s') . $xml_obj->Content . ' ';

            $response_text = '<xml>
                  <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                  <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
                  <CreateTime>'.$time.'</CreateTime>
                  <MsgType><![CDATA[text]]></MsgType>
                  <Content><![CDATA['.$content.']]></Content>
                </xml>';
            echo $response_text;        //回复用户消息
            // TODO 消息入库
        }elseif ($msg_type=='image'){       // 图片消息
            // TODO 下载图片
            $this->getMedia($media_id,$msg_type);
            // TODO 回复图片
            $response_img = '<xml>
                  <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                  <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
                  <CreateTime>'.time().'</CreateTime>
                  <MsgType><![CDATA[image]]></MsgType>
                  <Image>
                    <MediaId><![CDATA['.$media_id.']]></MediaId>
                  </Image>
                </xml>';
            echo $response_img;
        }elseif ($msg_type=='voice'){        // 语音消息
            // TODO 下载语音
            $this->getMedia($media_id,$msg_type);
            // TODO 回复语音
            $response_voice = '<xml>
                  <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                  <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
                  <CreateTime>'.time().'</CreateTime>
                  <MsgType><![CDATA[voice]]></MsgType>
                  <Voice>
                    <MediaId><![CDATA['.$media_id.']]></MediaId>
                  </Voice>
                </xml>';
            echo $response_voice;
        }elseif($msg_type=='video'){
            // TODO 下载小视频
            $this->getMedia($media_id,$msg_type);
            // TODO 回复小视频
            $response = '<xml>
              <ToUserName><![CDATA['.$toUser.']]></ToUserName>
                  <FromUserName><![CDATA['.$fromUser.']]></FromUserName>
              <CreateTime>'.time().'</CreateTime>
              <MsgType><![CDATA[video]]></MsgType>
              <Video>
                <MediaId><![CDATA['.$media_id.']]></MediaId>
                <Title><![CDATA[测试]]></Title>
                <Description><![CDATA[不可描述]]></Description>
              </Video>
            </xml>';
            echo $response;
        }
    }

    /**
     * 刷新 access_token
     */
    public function flushAccessToken()
    {
        $key = 'wx_access_token';
        Redis::del($key);
        echo $this->getAccessToken();
    }

    /**
     * 获取素材(TEST)
     */
    public function testMedia()
    {
        $midia_id = 'zlLa9YLUER1mOIf-7iMUmpkB2AXMXUDUbpmwd5PbvR9KiC6twlZxqXG0sSGgGB8D';
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$midia_id;

        //获取素材内容
        $data = file_get_contents($url);
        //保存文件
        $file_name = date('YmdHis') . mt_rand(11111,99999) . '.amr';
        file_put_contents($file_name,$data);

        echo '素材下载成功';echo "</br>";
        echo "文件名： " . $file_name;
    }

    public function getMedia($media_id,$media_type)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$media_id;

        //获取素材内容
            //$data = file_get_contents($url);
            //$finfo = finfo_open(FILEINFO_MIME_TYPE);
            //$file_info = finfo_file($finfo,$data);
            //var_dump($file_info);die;

        $client = new Client();
        $response = $client->request('GET',$url);
        // 获取文件类型
        $content_type = $response->getHeader('Content-Type')[0];
//        echo $content_type;echo "</br>";
        $pos = strpos($content_type,'/');
//        echo '/:' . $pos;
        $extension = '.' . substr($content_type,$pos+1);
//        echo "</br>ext:" . $extension;die;
        // 获取文件内容
        $file_content = $response->getBody();

        //保存文件
        $save_path = 'wx_media/';
        if ($media_type=='image'){      // 保存图片文件
            $file_name = date('YmdHis') . mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'imgs/' . $file_name;
        }elseif ($media_type=='voice'){     // 保存语音文件
            $file_name = date('YmdHis') . mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'voice/' . $file_name;
        }

        file_put_contents($save_path,$file_content);
        echo "save success!" . $save_path;die;
    }

    /**
     * 创建自定义菜单
     */
    public function createMenu()
    {

        $url = 'http://wx.xx20.top/vote';
        $url2 = 'http://wx.xx20.top';
        $redirect_uri = urlencode($url);        // 授权后跳转页面
        $redirect_uri2 = urlencode($url2);        // 授权后跳转页面

        // 创建自定义菜单的接口地址
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->access_token;
        $menu = [
            'button'    => [
                [
                    'type' =>'click',
                    'name' =>'今日天气',
                    'key'  =>'weather'
                ],
                [
                    'type' =>'view',
                    'name' =>'投票',
                    'url'  =>'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxea60b03810c5ab51&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=WYS1905#wechat_redirect'
                ],
                [
                    'type' =>'view',
                    'name' =>'商城',
                    'url'  =>'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxea60b03810c5ab51&redirect_uri='.$redirect_uri2.'&response_type=code&scope=snsapi_userinfo&state=WYS1905#wechat_redirect'
                ],
            ]
        ];
        $menu_json = json_encode($menu,JSON_UNESCAPED_UNICODE);
        $client = new Client();
        $response = $client->request('POST',$url,['body'  =>$menu_json]);
        echo "<pre>";print_r($menu);echo "</pre>";
        echo $response->getBody();      // 接收微信接口的响应数据
    }
}
