<?php

// 还原自 Swoole Compiler 加密文件: Variable.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/sms/Variable.php

namespace app\admin\validate\sms;

class Variable extends \think\Validate {
    protected $failException = true;
    protected $rule = ['name' => 'require|unique:sms_variable'];
    protected $message = [];
    protected $scene = ['add' => ['name'], 'edit' => ['name']];
    public function __construct()
    {
        // [opcode 还原] 字段描述 + parent::__construct()
        $this->field = ['name' => '变量名称'];

        parent::__construct();
    }
}
