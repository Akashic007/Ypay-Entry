<?php

// 还原自 Swoole Compiler 加密文件: Merchant.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/merchant/Merchant.php

namespace app\admin\controller\merchant;

class Merchant extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'create_time'];
    protected array $withJoinTable = ['user'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\merchant\Merchant();
    }

}
