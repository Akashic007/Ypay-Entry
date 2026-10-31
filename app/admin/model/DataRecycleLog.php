<?php

// 还原自 Swoole Compiler 加密文件: DataRecycleLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/DataRecycleLog.php

namespace app\admin\model;

class DataRecycleLog extends \think\Model {
    protected $name = 'security_data_recycle_log';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public function recycle(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(DataRecycle, 'recycle_id', 'id') 
        return $this->belongsTo(\app\admin\model\DataRecycle::class, 'recycle_id', 'id');
    }

    public function admin(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\admin\model\Admin, 'admin_id', 'id')
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }

}
