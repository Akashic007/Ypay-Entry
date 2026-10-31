<?php

// 还原自 Swoole Compiler 加密文件: HttpExamples.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/worker/controller/HttpExamples.php

namespace app\worker\controller;

class HttpExamples extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['index', 'url'];
    public function index(): void
    {
    $this->success('worker-http-index');
    }

    public function url(): void
    {
    echo 'pathInfo:' . $this->request->pathinfo() . PHP_EOL;
    echo 'param:';
    print_r($this->request->param());
    echo PHP_EOL;
    echo 'get:';
    print_r($this->request->get());
    echo PHP_EOL;
    echo 'url:' . $this->request->url() . PHP_EOL;
    echo 'host:' . $this->request->host() . PHP_EOL;
    echo 'domain:' . $this->request->domain() . PHP_EOL;
    echo 'method:' . $this->request->method() . PHP_EOL;
    echo 'controller:' . $this->request->controller() . PHP_EOL;
    echo 'action:' . $this->request->action() . PHP_EOL;
    echo 'protocol:' . $this->request->protocol() . PHP_EOL;
    echo 'ip:' . $this->request->ip() . PHP_EOL;
    echo 'root:' . $this->request->root() . PHP_EOL;
    echo 'port:' . $this->request->port() . PHP_EOL;
    echo 'baseUrl:' . $this->request->baseUrl() . PHP_EOL;
    echo 'isCli:' . $this->request->isCli() . PHP_EOL;
    }

    public function auth(): void
    {
    echo 'isLogin:' . $this->auth->isLogin() . PHP_EOL;
    if (!$this->auth->isLogin()) {
        return;
    }
    echo 'userId:' . $this->auth->id . PHP_EOL;
    echo 'ruleIds:';
    print_r($this->auth->getRuleIds());
    echo PHP_EOL;
    echo 'ruleList:';
    print_r($this->auth->getRuleList());
    echo PHP_EOL;
    echo 'menus:';
    print_r($this->auth->getMenus());
    echo PHP_EOL;
    }

    public function other(): void
    {
    echo __('Example language') . PHP_EOL;
    echo __('HTTP example language') . PHP_EOL;
    }

}
