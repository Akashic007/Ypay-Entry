<?php

// 还原自 Swoole Compiler 加密文件: EntryChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/EntryChannel.php

namespace app\admin\model;

class EntryChannel extends \think\Model {
    protected $name = 'entry_channel';
    protected $autoWriteTimestamp = true;
    protected $type = ['config' => 'array'];
    public function sp(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\supply\Upstream::class, 'sp_id');
    }

    public function getconfigattr($value)
    {
        return $value ? (array) json_decode($value, true) : [];
    }

    public function agentdiscount(): \think\model\relation\HasMany
    {
        // opcode: hasMany(agent\UserEntryDiscount,'ec_id','id')
return $this->hasMany(\app\admin\model\agent\UserEntryDiscount::class, 'ec_id', 'id');
    }

    public function getorderrateattr($value): float
    {
        // opcode 真值: (float)fenToYuan
return (float)fenToYuan($value);
    }

    public function setorderrateattr($value): int
    {
        // opcode 真值: yuanToFen
return yuanToFen($value);
    }

    public function agentsubscribediscount(): \think\model\relation\BelongsTo
    {
        // opcode: belongsTo(agent\SubscribeEntryDiscount,'id','ec_id')
return $this->belongsTo(\app\admin\model\agent\SubscribeEntryDiscount::class, 'id', 'ec_id');
    }

    public function agentsubscriberate(): \think\model\relation\BelongsTo
    {
        // opcode: belongsTo(agent\SubscribeEntryRate,'id','ec_id')
return $this->belongsTo(\app\admin\model\agent\SubscribeEntryRate::class, 'id', 'ec_id');
    }

    public function agentuserdiscount(): \think\model\relation\BelongsTo
    {
        // opcode: belongsTo(agent\UserEntryDiscount,'id','ec_id')
return $this->belongsTo(\app\admin\model\agent\UserEntryDiscount::class, 'id', 'ec_id');
    }

    public function usersubscriberate(): \think\model\relation\BelongsTo
    {
        // opcode: belongsTo(user\SubscribeEntryRate,'id','ec_id')
return $this->belongsTo(\app\admin\model\user\SubscribeEntryRate::class, 'id', 'ec_id');
    }

    public function userrate(): \think\model\relation\BelongsTo
    {
        // opcode: belongsTo(user\Rate,'id','channel_id')
return $this->belongsTo(\app\admin\model\user\Rate::class, 'id', 'channel_id');
    }

    public function getupidattr(): int
    {
    return (int)(($this->config['up_id'] ?? null));
    }

    public function getupchannelidattr(): int
    {
    return (int)(($this->config['up_channel_id'] ?? null));
    }

    public function getservicenameattr(): string
    {
    return (string)(($this->config['service_name'] ?? null));
    }

    public function getsynctimeattr(): string
    {
    if (!($Sh496)) {
        return null;
    }
    }

}
