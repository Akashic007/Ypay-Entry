<?php

// 还原自 Swoole Compiler 加密文件: Domain.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/site/Domain.php

namespace app\admin\validate\site;

class Domain extends \think\Validate {
    protected $failException = true;
    protected $rule = ['domain' => 'require|max:100', 'status' => 'in:0,1'];
    protected $message = ['domain.require' => '域名不能为空', 'domain.max' => '域名长度不能超过100个字符', 'status.in' => '状态值不合法'];
    protected $scene = ['add' => ['domain', null], 'edit' => ['domain', null]];
}
