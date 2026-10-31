<?php

// 还原自 Swoole Compiler 加密文件: Cloud.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/Cloud.php

namespace app\admin\model;

class Cloud extends \think\Model {
    protected $name = 'cloud';
    protected $autoWriteTimestamp = false;
    protected $type = ['proxy_config' => 'array'];
    protected static function onafterinsert($model): void
    {
        if (is_null($model->weigh)) {
            $pk = $model->getPk();
            // [opcode 对照] strlen($model[$pk]) >= 19（雪花长ID）→ weigh=表行数；否则 weigh=主键值（同 PoolAccount/UserRule 模式）
            if (19 <= strlen($model[$pk])) {
                $model->where($pk, $model[$pk])->update(['weigh' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
            }
        }
    }

    public function getproxyconfigattr($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

}
