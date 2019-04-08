<?php

namespace app\api\model;
use think\Model;

class Users extends Model
{
	protected $table = 'guzz_weapp_user';
  
    static public function saveUser($param)
    {
    	$userDetailInfo = $param['userDetail'];
        $isExists = self::get(['openid'=>$userDetailInfo['openid']])->find();
        $userDetailInfo['reg_time'] = time();
        if(!$isExists) {
            $responseInfo = array();
            $insertinfo = self::insert($userDetailInfo);
            if($insertinfo) {
                $responseInfo['code']=2;
                $responseInfo['msg'] ='注册成功';
            } else {
                $responseInfo['code']=1;
                $responseInfo['msg'] ='注册失败';
            }
        } else {
            $openid = $userDetailInfo['openid'];
            unset($userDetailInfo['reg_time']);
            unset($userDetailInfo['openid']);
            $userDetailInfo['update_at'] = time();
            self::where(['openid'=>$openid])->update($userDetailInfo);
            $responseInfo['code']=3;
            $responseInfo['msg'] ='用户已注册';
        }
      
        return $responseInfo;
    }

    public function getGenderAttr($value)
	{
		$status = [0=>'',1=>'男',2=>'女'];
		return $status[$value];
	}
}