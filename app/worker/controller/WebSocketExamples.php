<?php

// 还原自 Swoole Compiler 加密文件: WebSocketExamples.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/worker/controller/WebSocketExamples.php

namespace app\worker\controller;

class WebSocketExamples{
    protected \think\App $app;
    public function __construct(\think\App $app)
    {
    $this->app = $app;
    }

    public function index(): void
    {
    $this->app->send('example-index', 'WebSocket Examples Index');
    }

    public function message(): void
    {
    }

    public function auth(): void
    {
    $userAuth = \app\common\library\Auth::instance();
    $userAuth->init($this->app->request->get('ba_user_token', ''));
    $adminAuth = \app\admin\library\Auth::instance();
    $adminAuth->init($this->app->request->get('ba_token', ''));
    if ($userAuth->isLogin()) {
        \GatewayWorker\Lib\Gateway::bindUid($this->app->clientId, $userAuth->id . '|custom_suffix');
        $this->app->send('auth', ['clientId' => $this->app->clientId], $userAuth->id . '|custom_suffix');
    }
    }

}
