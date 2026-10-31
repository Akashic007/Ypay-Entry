<?php

// 还原自 Swoole Compiler 加密文件: Order.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/sys/Order.php

namespace app\admin\model\sys;

class Order extends \think\Model {
    protected $name = 'sys_order';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['pay_time' => 'timestamp:Y-m-d H:i:s', 'pay_info' => 'array', 'content' => 'array'];
    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function channel(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\channel\Account::class, 'channel_id', 'id');
    }

    public function getpayinfoattr($value)
    {
    if (!($Sh4127)) {
        return null;
        return [];
    }
    }

    public function getcontentattr($value)
    {
    if (!($Sh4128)) {
        return null;
        return [];
    }
    }

    public function setorderamountattr($value): int
    {
        // opcode 真值: 委托 self::yuanToFen
return self::yuanToFen($value);
    }

    public function getorderamountattr($value): int|float
    {
        // opcode 真值: 委托 self::fenToYuan
return self::fenToYuan($value);
    }

    public function setdiscountamountattr($value): int
    {
return self::yuanToFen($value);
    }

    public function getdiscountamountattr($value): int|float
    {
return self::fenToYuan($value);
    }

    public function setpaymentamountattr($value): int
    {
return self::yuanToFen($value);
    }

    public function getpaymentamountattr($value): int|float
    {
return self::fenToYuan($value);
    }

    public static function yuantofen(float|null $yuan = null): int
    {
        // opcode 真值: (int)round(yuan*100.0) 行 99
return (int) round($yuan * 100.0);
    }

    public static function fentoyuan(int|null $fen = null): float
    {
        // opcode 真值: (float)round(fen/100,2) 行 109
return (float) round($fen / 100, 2);
    }

}
