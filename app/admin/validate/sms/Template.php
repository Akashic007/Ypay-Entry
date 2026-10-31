<?php

// 还原自 Swoole Compiler 加密文件: Template.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/sms/Template.php

namespace app\admin\validate\sms;

class Template extends \think\Validate {
    protected $failException = true;
    protected $rule = ['code' => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{2,50}$|unique:sms_template'];
    protected $message = [];
    protected $scene = ['add' => ['code'], 'edit' => ['code']];
    public function __construct()
    {
        // [opcode 还原] 字段描述 + parent::__construct()
        $this->field = ['code' => '模板唯一标识'];

        parent::__construct();
    }
}
