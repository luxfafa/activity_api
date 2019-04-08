<?php

namespace app\api\controller;
use think\Request;
use think\Controller;
use app\api\model\ProductCtrl;

Class Product extends Controller{
  
  public function getProduct(Request $request)
  {
    $param = $request->get();
    $rule = ['token'=>'require'];
    $validate_res = $this->validate($param, $rule);
    if(false == $validate_res) return json(['code'=>2,'msg'=>$validate_res]);
    return json(ProductCtrl::getProduct($param));
  }

}