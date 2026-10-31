<?php

// 还原自 Swoole Compiler 加密文件: SensitiveDataLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/SensitiveDataLog.php

namespace app\admin\model;

class SensitiveDataLog extends \think\Model {
    protected $name = 'security_sensitive_data_log';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public function sensitive(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(SensitiveData, 'sensitive_id', 'id') 
        return $this->belongsTo(\app\admin\model\SensitiveData::class, 'sensitive_id', 'id');
    }

    public function admin(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\admin\model\Admin, 'admin_id', 'id')
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

}
