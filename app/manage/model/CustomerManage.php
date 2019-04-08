<?php
namespace app\manage\model;
use think\Model;



Class CustomerManage extends Model{
    protected $pk = 'customer_id';
    protected $table = 'guzz_customer';
    
    public function getCompanyTypeAttr($value) {
    	$type_list = ['未填写','国企', '民营', '合资', '外商独资', '上市公司', '事业单位','其他'];
    	return $type_list[$value];
    }
    public function getCompanyScaleAttr($value) {
    	$type_list = ['未填写','0-50人', '50-100人', '100-499人', '500-999人', '1000-9999人','10000人以上'];
    	return $type_list[$value];
    }
    
  

}