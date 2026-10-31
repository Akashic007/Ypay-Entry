<?php

// 还原自 Swoole Compiler 加密文件: Wallet.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/Wallet.php

namespace app\admin\model\user;

class Wallet extends \think\Model {
    protected $name = 'user_wallet';
    protected $autoWriteTimestamp = false;
    protected $type = ['pay_account' => 'array'];
    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id');
    }

    /**
     * opcode 还原（8 字符方法名对照，freeze/unfreeze/withdrawdeduct 的前后快照）：
     * 返回三余额快照，供 as_user_wallet_money_log 的 before_/after_ 列
     */
    public function snapshot(): array
    {
        return [
            'money'            => $this->money ?? 0,
            'withdrawal_money' => $this->withdrawal_money ?? 0,
            'freeze_money'     => $this->freeze_money ?? 0,
        ];
    }

    public static function onafterread(\app\admin\model\user\Wallet|null $model = null): void
    {
        // 原始逻辑丢失形参绑定（$Sh477→$model），字段为 int 分单位，按 分→元 换算语义重建
        if ($model) {
            foreach (['money', 'withdrawal_money', 'freeze_money', 'total_income', 'total_withdrawal'] as $f) {
                $model->$f = number_format(($model->$f ?? 0) / 100, 2, '.', '');
            }
        }
    }

    public static function onbeforewrite(\app\admin\model\user\Wallet|null $model = null): void
    {
        // [opcode 语义还原] 元→分（写入时无条件换算；service 层 bcmath 产出元字符串）
        if ($model) {
            foreach (['money', 'withdrawal_money', 'freeze_money', 'total_income', 'total_withdrawal'] as $f) {
                $v = $model->getdata($f) ?? 0;
                $model->$f = (int) round(((float) $v) * 100);
            }
        }
    }

}
