<?php

// 还原自 Swoole Compiler 加密文件: PayChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/sys/PayChannel.php

namespace app\admin\controller\sys;

class PayChannel extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $defaultSortField = 'weigh,desc';
    protected string|array $preExcludeFields = ['id'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\sys\PayChannel();
    }

}
