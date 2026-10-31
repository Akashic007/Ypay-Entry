<?php

// 还原自 Swoole Compiler 加密文件: Template.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/mail/Template.php

namespace app\admin\validate\mail;

class Template extends \think\Validate {
    protected $failException = true;
    protected $rule = ['name' => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{2,50}$|unique:mail_template', 'title' => 'require', 'content' => 'require'];
    protected $message = [];
    protected $scene = ['add' => ['name', null, 'title'], 'edit' => ['name', null, 'title']];
    public function __construct()
    {
        // [opcode 还原] __() 为 common.php 翻译助手；array_merge 合并 name.regex 消息
        $this->field = [
            'name'    => __('name'),
            'title'   => __('title'),
            'content' => __('content'),
        ];

        $this->message = array_merge($this->message, ['name.regex' => __('name regex')]);

        parent::__construct();
    }
}
