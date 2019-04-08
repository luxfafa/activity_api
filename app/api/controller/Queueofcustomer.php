<?php

namespace app\api\controller;
use think\Request;
use think\Controller;
use app\api\model\CustomerCtrl;

Class QueueOfCustomer extends Controller{
  
  public function customerAccept(Request $request)
  {
    $param = $request->post();
    $rule = ['mobile'=>'require|number','customer_name'=>'require','company_name'=>'require','wecaht_id'=>'require'];
    $validate_res = $this->validate($param, $rule);
    if(false == $validate_res) return json(['code'=>2,'msg'=>$validate_res]);
    return json(CustomerCtrl::queueOn($param));
  }

  public function redis_test()
  {
  	// Cache::set('author','LX');
  	// echo Cache::get('author');
    $custimer_redis = $this->get_redis();
    dump($custimer_redis->lrange('customer_guzz',0,100));
    // dump($custimer_redis->del('customer_guzz'));


  }
  public function get_redis()
  {
    $custimer_redis = new \Redis(); 
    $custimer_redis->connect('127.0.0.1','7577');
    return $custimer_redis;
  }
  

}