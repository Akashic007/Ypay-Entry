<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

// 获取当前请求 URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '';


// 安装检测-s
$rootPath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;
$installWhite = !is_file($rootPath . 'install.lock')
    && is_file($rootPath . 'install' . DIRECTORY_SEPARATOR . 'index.html')
    && !str_starts_with($requestUri, '/api/Install');
if ($installWhite) {
    header("location:" . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR);
    exit();
}
// 安装检测-e


// 直接访问后端的URL地址
$apiWhiteList = [
    '/qrcode',
    '/api/ajax/preview', // 内部照片回显接口
    '/payment/',
    '/notify/', // 通知接口
];

// 是否是后端请求
$server = isset($_REQUEST['server'])
    || isset($_SERVER['HTTP_SERVER'])
    || substr($requestUri, 1, 9) == 'index.php'
    || $_SERVER['REQUEST_METHOD'] == 'OPTIONS'
    || $requestUri == '/'; // 前台首页需要使用后台输出，所以要在这里过掉一下！！

// 检查URL白名单,也就是不需要加server=1就可以直接访问后台接口的
foreach ($apiWhiteList as $prefix) {
    if (str_starts_with($requestUri, $prefix)) {
        $server = true;
        break;
    }
}

if (!$server) {
    /*
     * 用户访问前端
     * 不在tp加载后判断，为了安全的使用 exit()（常驻内存运行时不走本文件）
     */
    // 检测是否已编译前端（如果存在 index.html，则访问）-s
    if (is_file($rootPath . 'index.html')) {
        header("location:" . DIRECTORY_SEPARATOR . 'index.html');
        exit();
    }
    // 检测是否已编译前端-e
}

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new \think\App())->http;

$response = $http->run();

$response->send();

$http->end($response);
