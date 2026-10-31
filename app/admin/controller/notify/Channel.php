<?php

// 还原自 Swoole Compiler 加密文件: Channel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/notify/Channel.php

namespace app\admin\controller\notify;

class Channel extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $defaultSortField = 'weigh,desc';
    protected string|array $preExcludeFields = ['id', null];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\notify\Channel();
        $this->request->filter('clean_xss');
    }
}
