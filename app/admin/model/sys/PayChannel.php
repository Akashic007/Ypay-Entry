<?php

// 还原自 Swoole Compiler 加密文件: PayChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/sys/PayChannel.php

namespace app\admin\model\sys;

class PayChannel extends \think\Model {
    protected $name = 'sys_pay_channel';
    protected $autoWriteTimestamp = false;
    protected $type = ['config' => 'array'];
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

    public function getconfigattr($value)
    {
        // [opcode 还原] $value ? json_decode($value, true) : []
        return $value ? json_decode($value, true) : [];
    }

}
