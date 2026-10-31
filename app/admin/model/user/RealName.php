<?php

// 还原自 Swoole Compiler 加密文件: RealName.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/RealName.php

namespace app\admin\model\user;

class RealName extends \think\Model {
    protected $name = 'user_real_name';
    protected $autoWriteTimestamp = true;
    public static function onbeforeinsert($model): void
    {
    if (!($Sh489)) {
        new Exception();
        throw $T3;
        if (!($Sh490)) {
            new Exception();
            throw $T3;
            return;
        }
    }
    }

    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id');
    }

}
