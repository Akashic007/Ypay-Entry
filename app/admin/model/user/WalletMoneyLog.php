<?php

// 还原自 Swoole Compiler 加密文件: WalletMoneyLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/WalletMoneyLog.php

namespace app\admin\model\user;

class WalletMoneyLog extends \think\Model {
    protected $name = 'user_wallet_money_log';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['content' => 'array'];
    /**
     * opcode 还原（8 参数静态记录入口，freeze/unfreeze/withdrawdeduct 调用）：
     * 写入钱包变动日志（before_/after_ 三余额快照）
     */
    public static function record(int $userId, string $money, int $type, string $remark = '', array $content = [], array $before = [], array $after = [], string $source = ''): self
    {
        return self::create([
            'user_id'                   => $userId,
            'money'                     => $money,
            'type'                      => $type,
            'source'                    => $source,
            'remark'                    => $remark,
            'content'                   => $content ?: [],
            'before_money'              => $before['money'] ?? 0,
            'before_withdrawal_money'   => $before['withdrawal_money'] ?? 0,
            'before_freeze_money'       => $before['freeze_money'] ?? 0,
            'after_money'               => $after['money'] ?? 0,
            'after_withdrawal_money'    => $after['withdrawal_money'] ?? 0,
            'after_freeze_money'        => $after['freeze_money'] ?? 0,
        ]);
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id');
    }
    // [opcode 还原] 分→元（DB int 分，模型层换算为元字符串），null 安全（事件系统可能以 null 调用）
    public static function onafterread(\app\admin\model\user\WalletMoneyLog|null $model = null): void
    {
        if (!$model) return;
        foreach (['money', 'before_money', 'before_withdrawal_money', 'before_freeze_money', 'after_money', 'after_withdrawal_money', 'after_freeze_money'] as $f) {
            $model->$f = number_format(($model->$f ?? 0) / 100, 2, '.', '');
        }
    }
    // [opcode 还原] 元→分（写入），null 安全（事件系统可能以 null 调用）
    public static function onbeforewrite(\app\admin\model\user\WalletMoneyLog|null $model = null): void
    {
        if (!$model) return;
        foreach (['money', 'before_money', 'before_withdrawal_money', 'before_freeze_money', 'after_money', 'after_withdrawal_money', 'after_freeze_money'] as $f) {
            $v = $model->getdata($f) ?? 0;
            $model->$f = (int) round(((float) $v) * 100);
        }
    }

}
