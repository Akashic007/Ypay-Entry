<?php

// 还原自 Swoole Compiler 加密文件: Local.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/validate/entrychannel/Local.php

namespace app\admin\validate\entrychannel;

class Local extends \think\Validate {
    protected $failException = true;
    protected $rule = ['channel_id' => 'require|integer'];
    protected $message = ['channel_id.require' => '通道ID不能为空', 'channel_id.integer' => '通道ID格式错误'];
    protected $scene = ['add' => ['channel_id'], 'edit' => ['channel_id']];
}
