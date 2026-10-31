<?php

// 还原自 Swoole Compiler 加密文件: RefundRecord.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/RefundRecord.php

namespace app\admin\model;

class RefundRecord extends \think\Model {
    protected $name = 'refund_record';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['extend' => 'array'];
    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(User, 'user_id', 'id')
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function channel(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(channel\Account, 'channel_id', 'id')
        return $this->belongsTo(\app\admin\model\channel\Account::class, 'channel_id', 'id');
    }

    public function ordertable(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(Order, 'order_id', 'id')
        return $this->belongsTo(\app\admin\model\Order::class, 'order_id', 'id');
    }

    public function getextendattr($value): array
    {
    if (!($Sh4107)) {
        return null;
    }
    }

    public function getorderamountattr($value): int|float
    {
        // opcode 真值: fenToYuan（无 CAST）
return fenToYuan($value);
    }

    public function getrefundamountattr($value): int|float
    {
return fenToYuan($value);
    }

    public function setorderamountattr($value): int
    {
        // opcode 真值: yuanToFen
return yuanToFen($value);
    }

    public function setrefundamountattr($value): int
    {
return yuanToFen($value);
    }

}
