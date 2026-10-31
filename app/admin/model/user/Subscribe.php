<?php

// 还原自 Swoole Compiler 加密文件: Subscribe.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/Subscribe.php

namespace app\admin\model\user;

class Subscribe extends \think\Model {
    protected $name = 'user_subscribe';
    protected $autoWriteTimestamp = true;
    protected $type = ['content' => 'array', 'style' => 'array'];
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

    public function getcontentattr($value)
    {
        // [opcode 还原] JSON 解码 + channel_number/order_rate 以 empty 判定补默认值
        $value = $value ? json_decode($value, true) : [];
        if (empty($value['channel_number'])) {
            $value['channel_number'] = 1;
        }
        if (empty($value['order_rate'])) {
            $value['order_rate'] = 0;
        }
        return $value;
    }

    public function getstyleattr($value)
    {
        // [opcode 还原] $value ? json_decode($value, true) : []
        return $value ? json_decode($value, true) : [];
    }

    public function subscribegroup(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(user\SubscribeGroup, 'group_id', 'id')
        return $this->belongsTo(\app\admin\model\user\SubscribeGroup::class, 'group_id', 'id');
    }

}
