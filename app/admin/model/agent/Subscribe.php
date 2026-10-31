<?php

// 还原自 Swoole Compiler 加密文件: Subscribe.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/agent/Subscribe.php

namespace app\admin\model\agent;

class Subscribe extends \think\Model {
    protected $name = 'agent_subscribe';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
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

    public function getpriceattr($value): float
    {
        return (float)fenToYuan($value);
    }

    public function setpriceattr($value): int
    {
        return yuanToFen($value);
    }

    public function getentrydiscountattr($value): float
    {
        return (float)fenToYuan($value);
    }

    public function setentrydiscountattr($value): int
    {
        return yuanToFen($value);
    }

    public function getorderrateattr($value): float
    {
        return (float)fenToYuan($value);
    }

    public function setorderrateattr($value): int
    {
        return yuanToFen($value);
    }

}
