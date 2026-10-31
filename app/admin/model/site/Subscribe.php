<?php

// 还原自 Swoole Compiler 加密文件: Subscribe.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/site/Subscribe.php

namespace app\admin\model\site;

class Subscribe extends \think\Model {
    protected $name = 'site_subscribe';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['custom_domain' => 'integer'];
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

    public function getpriceattr($value): string
    {
        // [opcode 还原] 分→元（全局 helper fenToYuan）
        return fenToYuan($value);
    }

    public function setpriceattr($value): int
    {
        // [opcode 还原] 元→分（全局 helper yuanToFen）
        return yuanToFen($value);
    }

}
