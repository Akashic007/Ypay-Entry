<?php

// 还原自 Swoole Compiler 加密文件: SubscribeEntryRate.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/agent/SubscribeEntryRate.php

namespace app\admin\model\agent;

class SubscribeEntryRate extends \think\Model {
    protected $name = 'agent_subscribe_entry_rate';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public static function onbeforeinsert(\app\admin\model\agent\SubscribeEntryRate|null $model = null): void
    {
    if (!($Sh4150)) {
        new \ba\Exception();
        throw $T2;
        return;
    }
    }

    public function subscribe(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\user\Subscribe::class, 'subscribe_id', 'id');
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function ec(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(EntryChannel, 'ec_id', 'id') 
        return $this->belongsTo(\app\admin\model\EntryChannel::class, 'ec_id', 'id');
    }

    public function getrateattr($value): float
    {
        // opcode 真值: (float)fenToYuan
return (float)fenToYuan($value);
    }

    public function setrateattr($value): int
    {
        // opcode 真值: yuanToFen
return yuanToFen($value);
    }

}
