<?php

// 还原自 Swoole Compiler 加密文件: DomainApply.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/site/DomainApply.php

namespace app\admin\validate\site;

class DomainApply extends \think\Validate {
    protected $failException = true;
    protected $rule = ['id' => 'require|integer|>:0', 'status' => 'require|integer|in:1,2', 'reason' => 'max:500'];
    protected $message = ['id.require' => '记录ID不能为空', 'status.in' => '审核状态无效'];
    protected $scene = ['add' => [], 'edit' => [], 'audit' => ['id', null, 'status']];
}
