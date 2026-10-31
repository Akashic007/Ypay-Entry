<?php

// 还原自 Swoole Compiler 加密文件: UserLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/UserLog.php

namespace app\admin\model;

class UserLog extends \think\Model {
    protected $name = 'user_log';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['data' => 'array'];
    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\common\model\User, 'user_id', 'id')
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function getdataattr($value): array
    {
        // [opcode 语义还原] JSON 数组安全解码
        return is_array($value) ? $value : ($value ? (array) json_decode($value, true) : []);
    }

}
