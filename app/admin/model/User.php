<?php

// 还原自 Swoole Compiler 加密文件: User.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/User.php

namespace app\admin\model;

class User extends \think\Model {
    protected $autoWriteTimestamp = true;
    protected $type = ['subscribe_expire_time' => 'timestamp:Y-m-d H:i:s'];
    protected $append = ['subscribe_expire_status'];
    public static function onafterinsert($model): void
    {
        // 原始逻辑断链（残留 $Sh4222 片段），暂为空实现，opcode 还原后必须覆盖
    }

    public function getavatarattr($value): string
    {
        return full_url($value, false, config('buildadmin.default_avatar'));
    }

    public function setavatarattr($value): string
    {
        return $value == full_url('', false, config('buildadmin.default_avatar')) ? '' : $value;
    }

    public function getmoneyattr($value): string
    {
        return (string) ($value ?? 0);
    }

    public function setmoneyattr($value): string
    {
        return (string) ($value ?? 0);
    }

    public function usergroup(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\UserGroup::class, 'group_id');
    }

    public function subscribe(): \think\model\relation\BelongsTo
    {
        // [2026-10-02 opcode 对照修正] 原版行 112: belongsTo(user\Subscribe, 'subscribe_id', 'id')——
        // 指向 as_user_subscribe 会员套餐；还原版误写 hasOne(site\Subscribe)（site 插件表不存在，
        // 导致订单创建/测试支付全链 "Table as_site_subscribe doesn't exist"）
        return $this->belongsTo(\app\admin\model\user\Subscribe::class, 'subscribe_id', 'id');
    }

    public function agent(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\admin\model\agent\Agent::class, 'agent_id');
    }

    public function site(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\site\Site::class, 'site_id');
    }

    public function realname(): \think\model\relation\HasOne
    {
        // opcode: hasOne(user\RealName,'user_id')
return $this->hasOne(\app\admin\model\user\RealName::class, 'user_id');
    }

    public function getsubscribeexpirestatusattr($value, $data): bool
    {
        // 语义重建：订阅到期时间晚于当前时间 = 未过期
        return (int) ($data['subscribe_expire_time'] ?? 0) > time();
    }

    public function userconfig(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\admin\model\user\Config::class, 'user_id');
    }

    public function resetpassword(int|string|null $uid = null, string|null $newPassword = null): \app\admin\model\User
    {
        $this->where(['id' => $uid])->update(['password' => hash_password($newPassword)]);
        return $this->find((int) $uid);
    }

    public function resetkey($uid): string
    {
        // 语义重建：重置用户 API key（16字节 hex，与库中现存格式一致）
        $key = bin2hex(random_bytes(16));
        $this->where(['id' => $uid])->update(['key' => $key]);
        return $key;
    }

    public function getsubexpiretimeattr($value, $data): int
    {
        return (int) ($data['subscribe_expire_time'] ?? 0);
    }

}
