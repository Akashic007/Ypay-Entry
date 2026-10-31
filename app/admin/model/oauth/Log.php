<?php

// 还原自 Swoole Compiler 加密文件: Log.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/oauth/Log.php

namespace app\admin\model\oauth;

class Log extends \think\Model {
    protected $name = 'oauth_log';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $type = ['content' => 'array'];
    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\common\model\User, 'user_id', 'id')
        return $this->belongsTo(\app\common\model\User::class, 'user_id', 'id');
    }

    public function getContentAttr($value)
    {
        // [2026-10-01 opcode 对照还原] 行 37-39: $value 空返回 [], 否则 json_decode($value, true)
        return $value ? json_decode($value, true) : [];
    }

}
