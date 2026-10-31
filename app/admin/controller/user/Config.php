<?php

// 还原自 Swoole Compiler 加密文件: Config.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/Config.php

namespace app\admin\controller\user;

class Config extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id'];
    protected array $withJoinTable = ['user'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\user\Config();
    }

}
