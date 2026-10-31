<?php

// 还原自 Swoole Compiler 加密文件: EntryMerchant.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/merchant/EntryMerchant.php

namespace app\admin\model\merchant;

class EntryMerchant extends \think\Model {
    protected $name = 'entry_merchant';
    protected $autoWriteTimestamp = true;
    public function merchantentry(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(merchant\MerchantEntry, 'me_id', 'id')
        return $this->belongsTo(\app\admin\model\merchant\MerchantEntry::class, 'me_id', 'id');
    }

    public function merchant(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(merchant\Merchant, 'mch_id', 'id')
        return $this->belongsTo(\app\admin\model\merchant\Merchant::class, 'mch_id', 'id');
    }

    public static function bindmerchant(int|null $meId = null, int|null $mchId = null): self
    {
        // [opcode 还原] static::where(['me_id'=>..,'mch_id'=>..])->find() 无则 static::create
        $self = static::where(['me_id' => $meId, 'mch_id' => $mchId])->find();
        if (!$self) {
            return static::create(['me_id' => $meId, 'mch_id' => $mchId]);
        }
        return $self;
    }

    public static function unbindentry(int|null $meId = null): void
    {
        // [opcode 还原] 遍历删除关联，引用计数为 0 时回收商户
        $entries = static::where('me_id', $meId)->select();
        foreach ($entries as $entry) {
            $mchId = $entry['mch_id'];
            $entry->delete();
            if (static::where('mch_id', $mchId)->count() == 0) {
                \app\admin\model\merchant\Merchant::destroy($mchId);
            }
        }
    }

}
