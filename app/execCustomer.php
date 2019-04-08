<?php

try {
  $redis_customer = new \Redis();
  $redis_customer->connect('127.0.0.1','7577');
} catch (RedisException $e) {
  die('Redis Connect Error: '.$e->getMessage());
}


$api_config = require './api/config.php';

$customer_key       = $api_config['customer_key'];
$exec_customer_rows = $api_config['exec_customer_rows'];
$list_len = $redis_customer->lLen($customer_key);
if($list_len<1) {
    die('暂时没有客户数据, 时间:'.date('Y-m-d H:i:s'));
}

$dbset      = require './database.php';
try{
  $myDb= new PDO("mysql:".$dbset['hostname']."=localhost:3306;dbname=".$dbset['database'],$dbset['username'],$dbset['password']); 
  $myDb->exec("set names utf8");
}catch(PDOException $e){
  die('PDO Connect Error: '.$e->getMessage());
}

$success_num = 0;
$back_num = 0;
$auto_num = $list_len-$exec_customer_rows > 0 ? $exec_customer_rows : $list_len;
for ($i=$auto_num; $i >0; $i--) { 
  $unit_v_json = $redis_customer->rPop($customer_key);
  if(!$unit_v_json && $unit_v_json=='nil') {
    sleep(1);
    continue;
  }
  $unit_v = json_decode($unit_v_json,true);
  $unit_v['create_at'] = date('Y-m-d H:i:s');
  $fields=implode(',',array_keys($unit_v));
  $values=implode("','",array_values($unit_v));
  $values="'".$values."'";
  $myDb->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $customerSQL = "INSERT INTO {$dbset['prefix']}customer ({$fields}) VALUES({$values})";
  if($myDb->exec($customerSQL)) {
      $success_num ++;
  } else {
      $back_num++;
      $redis_customer->rPush($customer_key,$unit_v_json);  // 入库错误执行回滚
  }
}
echo '本次执行共有'.$auto_num.'条客户数据,执行成功'.$success_num.'条客户数据,回滚'.$back_num.'条客户数据,时间:'.date('Y-m-d H:i:s');


// 释放
$myDb = null;
$redis_customer->close();