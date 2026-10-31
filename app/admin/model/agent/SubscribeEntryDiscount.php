<?php

// 还原自 Swoole Compiler 加密文件: SubscribeEntryDiscount.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/agent/SubscribeEntryDiscount.php

namespace app\admin\model\agent;

class SubscribeEntryDiscount extends \think\Model {
    protected $name = 'agent_subscribe_entry_discount';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public static function onbeforeinsert(\app\admin\model\agent\SubscribeEntryDiscount|null $model = null): void
    {
    if (!($Sh4154)) {
        new Exception();
        throw $T2;
        return;
    }
    }

    public function ec(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\EntryChannel::class, 'ec_id', 'id');
    }

    public function subscribe(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\user\Subscribe::class, 'subscribe_id', 'id');
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function getdiscountattr($value): float
    {
        // opcode 真值: (float)fenToYuan
return (float)fenToYuan($value);
    }

    public function setdiscountattr($value): int
    {
        // opcode 真值: yuanToFen
return yuanToFen($value);
    }

}
