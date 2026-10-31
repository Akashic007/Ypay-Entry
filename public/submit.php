<?php
namespace think;

/*
 * 不在tp加载后判断，为了安全的使用exit()
 * 使用绝对路径，确保花里胡哨的url均能正确判定和跳转
 */
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
$app->route->rule('/','app\api\controller\Payment@submit');
$http = $app->http->name('api');

$response = $http->run();

$response->send();
$http->end($response);