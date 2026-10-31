<?php

// 还原自 Swoole Compiler 加密文件: Subscribe.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/site/Subscribe.php

namespace app\admin\validate\site;

class Subscribe extends \think\Validate {
    protected $failException = true;
    protected $rule = ['custom_domain' => 'in:0,1'];
    protected $message = ['custom_domain.in' => '自定义域名选项无效'];
    protected $scene = ['add' => [], 'edit' => []];
}
