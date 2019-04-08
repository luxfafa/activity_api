<?php
$domain = 'https://'.$_SERVER['SERVER_NAME'];
define('APP_PATH', __DIR__ . '/../app/');
define('__LAYUI__',$domain.'/static/layui/');
define('__PHOTO__',$domain.'/static/');
define('__APP__',__DIR__.'/');
define('__APPURL__',$domain.'/');
require __DIR__ . '/../thinkphp/start.php';