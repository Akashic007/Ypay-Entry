<?php

// 还原自 Swoole Compiler 加密文件: Log.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/notify/Log.php

namespace app\admin\controller\notify;

class Log extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'create_time'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\notify\Log();
        $this->request->filter('clean_xss');
    }
}
