<?php

// 还原自 Swoole Compiler 加密文件: Template.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/notify/Template.php

namespace app\admin\validate\notify;

class Template extends \think\Validate {
    protected $failException = true;
    protected $rule = ['event' => 'require|length:1,120', 'name' => 'require|length:1,120', 'notify_type' => 'require|in:sms,ems,email', 'title' => 'require|length:1,255', 'content' => 'require', 'template_ids' => 'array', 'status' => 'require', 'remark' => 'max:500'];
    protected $message = [];
    protected $scene = ['add' => ['event', null, 'name', null, 'notify_type', null, 'title', null], 'edit' => ['event', null, 'name', null, 'notify_type', null, 'title', null]];
}
