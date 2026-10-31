<?php

// 还原自 Swoole Compiler 加密文件: RealName.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/auth/RealName.php

namespace app\admin\model\auth;

class RealName extends \think\Model {
    protected $name = 'admin_real_name';
    protected $autoWriteTimestamp = true;
    protected $type = ['real_time' => 'timestamp:Y-m-d H:i:s'];
    public function admin(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\admin\model\Admin, 'admin_id', 'id')
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

}
