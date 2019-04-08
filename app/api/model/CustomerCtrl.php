<?php

namespace app\api\model;

use think\Model;

Class CustomerCtrl extends Model {
  
  protected $pk = 'customer_id';
  protected $table = 'guzz_customer';
  public static function queueOn($param)
  {
    $custimer_key = 'customer_guzz';
    $guzz_string = json_encode($param);
    $custimer_redis = self::get_redis();
    $r = $custimer_redis->lpush($custimer_key,$guzz_string);
    // dump($custimer_redis->delete($custimer_key));
    // dump($custimer_redis->lrange($custimer_key,0,100));
    $custimer_redis->close();
    if($r) {
        return ['code'=>1,'msg'=>'提交成功'];
    } else {
         return ['code'=>2,'msg'=>'提交失败'];
    }
  }
  public static function customerHistory($param)
  {
    $r = self::where(['openid'=>$param['openid']])->find();
    if($r) {
        return ['code'=>1,'msg'=>'已有提交记录','data'=>count($r)];
    } else {
         return ['code'=>2,'msg'=>'没有提交记录'];
    }
  
  }
  public static function get_redis()
  {
    $custimer_redis = new \Redis(); 
    $custimer_redis->connect('127.0.0.1','7577');
    return $custimer_redis;
  }
}