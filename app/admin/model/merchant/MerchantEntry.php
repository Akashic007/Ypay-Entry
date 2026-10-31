<?php

// 还原自 Swoole Compiler 加密文件: MerchantEntry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/merchant/MerchantEntry.php

namespace app\admin\model\merchant;

class MerchantEntry extends \think\Model {
    protected $append = ['platform_name'];
    protected $name = 'merchant_entry';
    protected $autoWriteTimestamp = true;
    protected $type = ['apply_data' => 'array', 'apply_result' => 'array'];
    protected $readonly = ['supply', 'source'];
    protected static array $sourceType = ['local', 'downstream'];
    public static function onbeforeinsert(\app\admin\model\merchant\MerchantEntry|null $model = null): void
    {
        // [opcode 还原] 来源合法性 + UP 通道不能下游进件
        if (!in_array($model->source, static::$sourceType)) {
            throw new \Exception('进件来源类型错误');
        }
        if ($model->channel->source == 'up' && $model->source == 'downstream') {
            throw new \Exception('UP通道无法实现下游进件');
        }
    }

    public static function onbeforeupdate($model): void
    {
        // [opcode 还原] 同步进件商户归属用户
        $entryMerchant = EntryMerchant::where('me_id', $model->id)->find();
        if (!$entryMerchant) {
            return;
        }
        $merchant = Merchant::find($entryMerchant->mch_id);
        if (!$merchant) {
            return;
        }
        $merchant->user_id = $model->user_id;
        $merchant->save();
    }

    public static function onafterdelete(\think\Model|null $model = null): void
    {
        // [opcode 还原] 删除进件时解绑关联商户
        EntryMerchant::unbindEntry($model->id);
    }

    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(admin\model\User, 'user_id', 'id') —— 类名为 opcode 字面量真值
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function sp(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(ServiceProvider, sp_id, id)——FK 为 7 字符加密字面量，按 as_merchant_entry 列 sp_id 定案
        return $this->belongsTo(\app\admin\model\ServiceProvider::class, 'sp_id', 'id');
    }

    public function channel(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(admin\model\EntryChannel, 'ec_id', 'id')
        return $this->belongsTo(\app\admin\model\EntryChannel::class, 'ec_id', 'id');
    }

    public function merchant(): \think\model\relation\HasOneThrough
    {
        // [opcode 还原] hasOneThrough(Merchant, EntryMerchant, 'me_id', 'id', 'id', 'mch_id') —— join: entry_merchant.mch_id=merchant.id / entry_merchant.me_id=merchant_entry.id
        return $this->hasOneThrough(\app\admin\model\merchant\Merchant::class, \app\admin\model\merchant\EntryMerchant::class, 'me_id', 'id', 'id', 'mch_id');
    }

    public function downstream(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(supply\Downstream, 'down_id', 'id')
        return $this->belongsTo(\app\admin\model\supply\Downstream::class, 'down_id', 'id');
    }

    public function getapplydataattr($value)
    {
        // [opcode 还原] $value ? json_decode($value, true) : []
        return $value ? json_decode($value, true) : [];
    }

    public function getapplyresultattr($value)
    {
        // [opcode 还原] $value ? json_decode($value, true) : []
        return $value ? json_decode($value, true) : [];
    }

    public function getstatustextattr($value, $data): string
    {
        // [opcode 还原] 数组 9 元素，奇数位为被保护字面量，按字符串池序还原（16B stride 扫 raw arData）
        return ['待提交', '待签约', '已提交', '开通权限中', '审核中', '已作废', '已驳回', '已完成', '待验证'][$data['status']];
    }

    public function getplatformnameattr($value, $data): string
    {
        // [opcode 还原] config('entry.channel_type') 按 platform_code 匹配取 name
        $platforms = config('entry.channel_type');
        $found = [];
        foreach ($platforms as $platform) {
            if ($platform['code'] == $data['platform_code']) {
                $found = $platform;
            }
        }
        return $found['name'] ?? '未知';
    }

}
