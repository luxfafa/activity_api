<?php

namespace app\api\model;

use think\Model;

Class ProductCtrl extends Model {
  
  protected $pk = 'product_id';
  protected $table = 'guzz_product';
  public static function getProduct($param)
  {
    $product_row = self::get(['product_id'=>$param['token']])->find();
    if($product_row) {
        return ['code'=>1,'msg'=>'获取成功 加载中','data'=>$product_row];
    } else {
         return ['code'=>2,'msg'=>'获取产品数据失败'];
    }
  }
  public static function get_redis()
  {
    $custimer_redis = new \Redis(); 
    $custimer_redis->connect('127.0.0.1','7577');
    return $custimer_redis;
  }
}