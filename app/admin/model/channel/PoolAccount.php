<?php

// 还原自 Swoole Compiler 加密文件: PoolAccount.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/channel/PoolAccount.php

namespace app\admin\model\channel;

class PoolAccount extends \think\Model {
    protected $name = 'channel_pool_account';
    protected $autoWriteTimestamp = false;
    protected static function onafterinsert($model): void
    {
        if (is_null($model->weigh)) {
            $pk = $model->getPk();
            // [opcode 对照] strlen($model[$pk]) >= 19（雪花长ID）→ weigh=表行数；否则 weigh=主键值（同 UserRule 模式）
            if (19 <= strlen($model[$pk])) {
                $model->where($pk, $model[$pk])->update(['weigh' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
            }
        }
    }

    public function pool(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(channel\Pool, 'pool_id', 'id')
        return $this->belongsTo(\app\admin\model\channel\Pool::class, 'pool_id', 'id');
    }

    public function account(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(Account, 'account_id', 'id')
        return $this->belongsTo(\app\admin\model\channel\Account::class, 'account_id', 'id');
    }

}
