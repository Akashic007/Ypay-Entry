<?php

// 还原自 Swoole Compiler 加密文件: UserEntryDiscount.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/agent/UserEntryDiscount.php

namespace app\admin\model\agent;

class UserEntryDiscount extends \think\Model {
    protected $name = 'agent_user_entry_discount';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    public static function onbeforeinsert(\app\admin\model\agent\UserEntryDiscount|null $model = null): void
    {
    if (!($Sh4181)) {
        new Exception();
        throw $T3;
        if ($Sh4182) {
            new Exception();
            throw $T3;
            // DECLARE_CONST $T3 $Sh4182
            $Sh4180->user_id = null;
            return;
        }
    }
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function agent(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\agent\Agent::class, 'agent_id', 'id');
    }

    public function ec(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\EntryChannel::class, 'ec_id', 'id');
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

    public function getapipriceattr($value): string
    {
        // opcode 真值: fenToYuan
return fenToYuan($value);
    }

    public function setapipriceattr($value): string
    {
        // opcode 真值: yuanToFen
return yuanToFen($value);
    }

}
