<?php

// 还原自 Swoole Compiler 加密文件: Merchant.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/merchant/Merchant.php

namespace app\admin\model\merchant;

class Merchant extends \think\Model {
    protected $name = 'merchant';
    protected $autoWriteTimestamp = true;
    protected $type = ['merchant_info' => 'array', 'config' => 'array'];
    public function user(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\common\model\User::class, 'user_id');
    }

    public function sp(): \think\model\relation\BelongsTo
    {
        // [opcode 语义还原] belongsTo(\app\admin\model\ServiceProvider, 'sp_id', 'id')
        return $this->belongsTo(\app\admin\model\ServiceProvider::class, 'sp_id', 'id');
    }

    public function accounts(): \think\model\relation\HasMany
    {
        // opcode: hasMany(channel\Account,'mch_id','id')
return $this->hasMany(\app\admin\model\channel\Account::class, 'mch_id', 'id');
    }

    public function orders(): \think\model\relation\HasMany
    {
        return $this->hasMany(\app\admin\model\Order::class, 'user_id', 'user_id');
    }

    public function entries(): \think\model\relation\HasMany
    {
        // [opcode 语义还原] Merchant 商户进件记录（belongsToMany 残段按 vendor 语义推断为 HasMany entries）
        return $this->hasMany(\app\admin\model\merchant\MerchantEntry::class, 'merchant_id', 'id');
    }

    public function ec(): \think\model\relation\BelongsTo
    {
        // opcode: belongsTo(EntryChannel,'ec_id','id')
return $this->belongsTo(\app\admin\model\EntryChannel::class, 'ec_id', 'id');
    }

    public function getmerchantinfoattr($value)
    {
    if (!($Sh4296)) {
        return null;
        return [];
    }
    }

    public function getconfigattr($value)
    {
    if (!($Sh4297)) {
        return null;
        return [];
    }
    }

}
