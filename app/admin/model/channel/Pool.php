<?php

// 还原自 Swoole Compiler 加密文件: Pool.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/channel/Pool.php

namespace app\admin\model\channel;

class Pool extends \think\Model {
    protected $name = 'channel_pool';
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

    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(admin\model\User, 'user_id', 'id') —— 类名为 opcode 字面量真值
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function poolaccounts(): \think\model\relation\HasMany
    {
        // [opcode 还原] hasMany(channel\PoolAccount, 'pool_id', 'id')
        return $this->hasMany(\app\admin\model\channel\PoolAccount::class, 'pool_id', 'id');
    }

}
