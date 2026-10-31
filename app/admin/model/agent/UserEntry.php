<?php

// 还原自 Swoole Compiler 加密文件: UserEntry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/agent/UserEntry.php
//
// [2026-10-01 opcode 全量对照还原] 修复前文件为半途断链的拼接垃圾（agent() 方法体内混入
// 注释化签名与 `}To(...)` 残留、user()/me() 返回 null 违反 BelongsTo 返回类型）。
// 五方法 opcode 真值（行号 28/33/38/43/48）：
//   agent(): belongsTo(agent\Agent, 'agent_id', 'id')
//   user():  belongsTo(admin\model\User, 'user_id', 'id')  ← admin 系模型指向 admin\User（已知坑 5）
//   me():    belongsTo(merchant\MerchantEntry, 'me_id', 'id')

namespace app\admin\model\agent;

class UserEntry extends \think\Model {
    protected $name = 'agent_user_entry';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public function agent(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\agent\Agent::class, 'agent_id', 'id');
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function me(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\merchant\MerchantEntry::class, 'me_id', 'id');
    }

    public function getpriceattr($value): float
    {
        return (float)fenToYuan($value);
    }

    public function setpriceattr($value): int
    {
        return yuanToFen($value);
    }

}
