<?php

// 还原自 Swoole Compiler 加密文件: Complaint.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/Complaint.php
//
// [2026-10-02 opcode 全量对照重写] 修复前 merchant() 内混入注释化签名与 `}To(...)` 拼接残留
// （同 UserEntry 事故型），ec()/sp()/getrescontentattr() 为 null 空壳。五方法 opcode 真值：
//   user():     belongsTo(User, 'user_id', 'id')
//   merchant(): belongsTo(merchant\Merchant, 'merchant_id', 'id')
//   ec():       belongsTo(EntryChannel, 'ec_id', 'id')
//   sp():       belongsTo(ServiceProvider, 'sp_id', 'id')
//   getResContentAttr: $value ? json_decode($value, true) : []

namespace app\admin\model;

class Complaint extends \think\Model {
    protected $name = 'complaint';
    protected $autoWriteTimestamp = true;
    protected $type = ['complaint_time' => 'timestamp:Y-m-d H:i:s', 'res_content' => 'array'];

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function merchant(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\merchant\Merchant::class, 'merchant_id', 'id');
    }

    public function ec(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\EntryChannel::class, 'ec_id', 'id');
    }

    public function sp(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\ServiceProvider::class, 'sp_id', 'id');
    }

    public function getrescontentattr($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

}
