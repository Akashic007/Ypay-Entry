<?php

// 还原自 Swoole Compiler 加密文件: Type.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/channel/Type.php

namespace app\admin\model\channel;

class Type extends \think\Model {
    protected $name = 'channel_type';
    protected $autoWriteTimestamp = false;
    protected $type = ['config' => 'array', 'extend' => 'array'];
    public function paytypetable(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(PayType, 'pay_type', 'code')
        return $this->belongsTo(\app\admin\model\channel\PayType::class, 'pay_type', 'code');
    }

    public function getconfigattr($value): array
    {
        // [opcode 还原] JSON 数组安全解码
        return is_array($value) ? $value : ($value ? (array) json_decode($value, true) : []);
    }

    public function getextendattr($value): array
    {
        // [opcode 还原] JSON 数组安全解码
        return is_array($value) ? $value : ($value ? (array) json_decode($value, true) : []);
    }

}
