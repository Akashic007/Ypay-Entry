<?php

// 还原自 Swoole Compiler 加密文件: Account.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/channel/Account.php
// [2026-09-22 opcode 全量对照还原] 关联/访问器/统计方法按加密原件字面量真值重建

namespace app\admin\model\channel;

class Account extends \think\Model {
    protected $name = 'channel_account';
    protected $autoWriteTimestamp = true;
    protected $type = ['config' => 'array', 'extend' => 'array', 'pay_config' => 'array'];
    public static function onbeforeinsert(\app\admin\model\channel\Account|null $model = null): void
    {
        // [opcode 还原] source_type 必须 in local/up
        if (!in_array(($model->source_type ?? ''), ['local', 'up'])) {
            throw new \ba\Exception(__('Please select the channel account source type'));
        }
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function paytypetable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\channel\PayType::class, 'pay_type', 'code');
    }

    public function channeltypetable(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\channel\Type::class, 'channel_type', 'type');
    }

    public function downstream(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\supply\Downstream::class, 'source_id', 'id');
    }

    public function merchant(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\merchant\Merchant::class, 'mch_id', 'id'); // [opcode] 加密 FK 7 字符对照 mch_id 列
    }

    public function ordertable(): \think\model\relation\HasMany
    {
        return $this->hasMany(\app\admin\model\Order::class, 'channel_id', 'id');
    }

    public function getconfigattr($value): array
    {
        return is_array($value) ? $value : ($value ? (array) json_decode($value, true) : []);
    }

    public function getextendattr($value): array
    {
        return is_array($value) ? $value : ($value ? (array) json_decode($value, true) : []);
    }

    public function getpayconfigattr($value): array
    {
        return is_array($value) ? $value : ($value ? (array) json_decode($value, true) : []);
    }

    public function todayorder(): \think\model\relation\HasMany
    {
        return $this->ordertable()->whereDay('create_time');
    }

    public function yesterdayorder(): \think\model\relation\HasMany
    {
        return $this->ordertable()->whereDay('create_time', 'yesterday');
    }

    public function weekorder(): \think\model\relation\HasMany
    {
        return $this->ordertable()->whereWeek('create_time');
    }

    public function monthorder(): \think\model\relation\HasMany
    {
        return $this->ordertable()->whereMonth('create_time');
    }

}
