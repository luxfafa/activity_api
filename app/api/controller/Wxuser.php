<?php
namespace app\api\controller;
use app\api\model\Users;
use app\api\model\CustomerCtrl;
use think\Session;
use think\Controller;
use think\Request;
Class Wxuser extends Controller{
    public $getkeyurl = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";
    public function __construct(Request $request) {
        if('GET' == $request->method()) {
            return (json_encode(['msg'=>'非法访问!']));die;
        }
    }
    public function exchange(Request $request)
    {
        $requestData   = $request->post();
        $statusInfo    = array();
        if(!empty($requestData['code'])){
            $jsCode    = $requestData['code'];
            $appId     = config('AppId');
            $appSecret = config('AppSercert');
            $getkeyurl = sprintf($this->getkeyurl,$appId,$appSecret,$jsCode);
            $loginInfo = $this->curl_get_https($getkeyurl);
            if(!$loginInfo) {
                $statusInfo['msg'] = '登录失败';
            }
          
            if(isset($loginInfo['session_key'])) {
                Session::set('userinfo',$loginInfo);  // 请求成功存入session
                return json($loginInfo);
            } else {
                $statusInfo['msg'] = '登录失败';
            }
        } else {
            $statusInfo['msg'] = '参数缺省';  //请求参数没有code
        }
        return json($statusInfo);
    }
    // 非静默授权注册
    public function register(Request $request) {
        $requestData  = $request->post();
        return json(Users::saveUser($requestData));
    }

    public function customerHistory(Request $request) {
        $requestData  = $request->get();
        $rule = ['openid'=>'require'];
        $validate_res = $this->validate($requestData, $rule);
        if(false == $validate_res) return json(['code'=>2,'msg'=>$validate_res]);
        return json(CustomerCtrl::customerHistory($requestData));
    }
    
    static public function curl_post_https($url,$data='')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $tmpInfo = curl_exec($curl);
        curl_close($curl);
        return json_decode($tmpInfo,true);
    }
    static public function curl_get_https($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $tmpInfo = curl_exec($curl);
        curl_close($curl);
        return json_decode($tmpInfo,true);
    }
    public function getAccessToken()
    {
        
    }
}