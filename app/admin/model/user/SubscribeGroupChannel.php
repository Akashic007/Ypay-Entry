<?php

// 还原自 Swoole Compiler 加密文件: SubscribeGroupChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/SubscribeGroupChannel.php

namespace app\admin\model\user;

class SubscribeGroupChannel extends \think\model\Pivot {
    protected $name = 'user_subscribe_group_channel';
    protected $autoWriteTimestamp = false;
    public function subscribegroup(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(user\SubscribeGroup, 'group_id', 'id')
        return $this->belongsTo(\app\admin\model\user\SubscribeGroup::class, 'group_id', 'id');
    }

    public function channeltypetable(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(channel\Type, 'channel_type', 'type')
        return $this->belongsTo(\app\admin\model\channel\Type::class, 'channel_type', 'type');
    }

}
