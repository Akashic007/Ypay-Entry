<?php

namespace think;

//  定义这个文件,是因为易支付接口统一规定,并且tp的路由没办法定义PHP为后缀,所以需要定义这个文件
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");
    header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: " . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    header("Access-Control-Allow-Origin: *");
    exit();
}

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$app =new App();
$app->route->rule('/','/Payment/mapi');
$http = $app->http->name('api');

$response = $http->run();

$response->send();
$http->end($response);