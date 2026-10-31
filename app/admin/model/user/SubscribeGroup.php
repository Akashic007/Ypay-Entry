<?php

// 还原自 Swoole Compiler 加密文件: SubscribeGroup.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/SubscribeGroup.php

namespace app\admin\model\user;

class SubscribeGroup extends \think\Model {
    protected $name = 'user_subscribe_group';
    protected $autoWriteTimestamp = false;
    public function channeltypes(): \think\model\relation\HasManyThrough
    {
        // [opcode 还原] 六参数真值（dump_m 逐字面量对照）
        return $this->hasManyThrough(\app\admin\model\channel\Type::class, \app\admin\model\user\SubscribeGroupChannel::class, 'group_id', 'type', 'id', 'channel_type');
    }

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

}
