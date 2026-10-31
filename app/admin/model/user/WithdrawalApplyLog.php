<?php

// 还原自 Swoole Compiler 加密文件: WithdrawalApplyLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/WithdrawalApplyLog.php

namespace app\admin\model\user;

class WithdrawalApplyLog extends \think\Model {
    protected $name = 'user_withdrawal_apply_log';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['pay_time' => 'timestamp:Y-m-d H:i:s'];
    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\common\model\User, 'user_id', 'id')
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }
    // [opcode 语义还原] 分→元 + null 安全（事件系统可能以 null 调用）
    public static function onafterread(\app\admin\model\user\WithdrawalApplyLog|null $model = null): void
    {
        if (!$model) return;
        foreach (['apply_amount', 'final_amount', 'fee'] as $f) {
            $model->$f = number_format(($model->$f ?? 0) / 100, 2, '.', '');
        }
    }
    // [opcode 语义还原] 元→分 + null 安全
    public static function onbeforewrite(\app\admin\model\user\WithdrawalApplyLog|null $model = null): void
    {
        if (!$model) return;
        foreach (['apply_amount', 'final_amount', 'fee'] as $f) {
            $v = $model->getdata($f) ?? 0;
            $model->$f = (int) round(((float) $v) * 100);
        }
    }

}
