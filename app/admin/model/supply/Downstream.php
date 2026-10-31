<?php

// 还原自 Swoole Compiler 加密文件: Downstream.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/supply/Downstream.php

namespace app\admin\model\supply;

class Downstream extends \think\Model {
    protected $name = 'supply_downstream';
    protected $autoWriteTimestamp = true;
    public function agent(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\admin\model\agent\Agent, 'agent_id', 'id')
        return $this->belongsTo(\app\admin\model\agent\Agent::class, 'agent_id', 'id');
    }

    public static function onbeforeinsert(\app\admin\model\supply\Downstream|null $model = null): void
    {
    if (!($Sh4306)) {
        new Exception();
        throw $T2;
        return;
    }
    }

}
