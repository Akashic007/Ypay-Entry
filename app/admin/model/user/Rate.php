<?php

// 还原自 Swoole Compiler 加密文件: Rate.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/Rate.php

namespace app\admin\model\user;

class Rate extends \think\Model {
    protected $name = 'user_rate';
    protected $autoWriteTimestamp = true;
    protected static array $rateTypes = ['order'];
    public static function onbeforeinsert(\app\admin\model\user\Rate|null $model = null): void
    {
        // [opcode 还原] 校验费率类型并拦截同用户同类型重复费率；channel_id 未给则归 0
        if (!in_array($model->rate_type, static::$rateTypes)) {
            throw new \ba\Exception('费率类型错误');
        }
        $query = static::where('user_id', $model->user_id)->where('rate_type', $model->rate_type);
        if (!empty($model->channel_id) && 0 < $model->channel_id) {
            $query->where('channel_id', $model->channel_id);
        } else {
            $query->where('channel_id', 0);
            $model->channel_id = 0;
        }
        if ($query->find()) {
            throw new \ba\Exception('当前用户费率已存在');
        }
    }

    public static function onbeforeupdate(\app\admin\model\user\Rate|null $model = null): void
    {
        // [opcode 还原] 更新时 channel_id 一律归 0（费率不再挂通道）
        if (!isset($model->channel_id)) {
            return;
        }
        $model->channel_id = 0;
    }

    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(admin\model\User, 'user_id', 'id') —— 类名为 opcode 字面量真值
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function channel(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\EntryChannel::class, 'channel_id', 'id'); // [opcode 真值 2026-09-22 B38 批]
    }

    public function getrateattr($value): float
    {
        // [opcode 还原] 分→元后转 float
        return (float) fenToYuan($value);
    }

    public function setrateattr($value): int
    {
        // [opcode 还原] 元→分
        return yuanToFen($value);
    }

}
