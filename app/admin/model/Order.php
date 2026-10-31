<?php

// 还原自 Swoole Compiler 加密文件: Order.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/Order.php

namespace app\admin\model;

class Order extends \think\Model {
    protected $name = 'order';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['pay_info' => 'array', 'extend' => 'array'];
    protected $append = ['status_text'];
    const STATUS_WAIT = 0;
    const STATUS_PAID = 1;
    const STATUS_REFUND = 2;
    const STATUS_FAIL = 3;
    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id');
    }

    public function userconfig(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\admin\model\user\Config::class, 'user_id', 'user_id');
    }

    public function channel(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\channel\Account::class, 'channel_id', 'id'); // [opcode 真值 2026-09-22 B38 批：原类引用 channel\Channel 不存在]
    }

    public function merchant(): \think\model\relation\HasOneThrough
    {
        // [2026-10-02 opcode 对照修复] 原版行 96-97: hasOneThrough(Merchant, Account, 'id','id','channel_id','mch_id')
        // + 闭包 where('account.mch_id','>',0)。还原版为 return null 断链空壳。
        return $this->hasOneThrough(
            \app\admin\model\merchant\Merchant::class,
            \app\admin\model\channel\Account::class,
            'id',
            'id',
            'channel_id',
            'mch_id'
        )->where('account.mch_id', '>', 0);
    }

    public function getmoneyattr($value): float
    {
        // [opcode 真值] (float)fenToYuan —— 分(库)→元(展示)
        return (float)fenToYuan($value);
    }

    public function setmoneyattr($value): int
    {
        // [opcode 真值] yuanToFen
        return yuanToFen($value);
    }

    public function getpaymoneyattr($value): string
    {
        return (float)fenToYuan($value);
    }

    public function setpaymoneyattr($value): int
    {
        return yuanToFen($value);
    }

    public function getpayinfoattr($value): array
    {
        return is_array($value) ? $value : ($value ? (array) json_decode($value, true) : []);
    }

    public function getextendattr($value): array
    {
        return $value ? (array) json_decode($value, true) : [];
    }

    public function getstatustextattr($value, $data): string
    {
        // opcode 真值含 未支付/已支付/已退款 三个字面量，中间两个槽位原文丢失，未知状态回退'未知'
        $map = [0 => '未支付', 2 => '已支付', 4 => '已退款'];
        return $map[(int) ($data['status'] ?? -1)] ?? '未知';
    }

    public function getpaytypeattr($value, $data): string
    {
        $map = ['alipay' => '支付宝', 'wxpay' => '微信支付', 'qqpay' => 'QQ支付'];
        return $map[$data['type'] ?? ''] ?? '未知';
    }

}
