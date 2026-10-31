<?php

// 还原自 Swoole Compiler 加密文件: Config.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/user/Config.php

namespace app\admin\model\user;

class Config extends \think\Model {
    protected $name = 'user_config';
    protected $autoWriteTimestamp = false;
    protected array $arrayFields = ['real_name', 'notify_config', 'google_auth', 'channel_config'];
    protected $type = ['real_name' => 'array', 'google_auth' => 'array', 'notify_config' => 'array', 'channel_config' => 'array', 'basic' => 'array'];
    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 还原] belongsTo(admin\model\User, 'user_id', 'id') —— 类名为 opcode 字面量真值
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function getrealnameattr($value): array
    {
        // [opcode 还原] empty($value) ? [] : json_decode($value, true)
        return empty($value) ? [] : json_decode($value, true);
    }

    public function getgoogleauthattr($value): array
    {
        // [opcode 还原] empty($value) ? [] : json_decode($value, true)
        return empty($value) ? [] : json_decode($value, true);
    }

    public function getnotifyconfigattr($value): array
    {
        // [opcode 还原] empty($value) ? [] : json_decode($value, true)
        return empty($value) ? [] : json_decode($value, true);
    }

    public function getchannelconfigattr($value): array
    {
        // [opcode 还原] empty($value) ? [] : json_decode($value, true)
        return empty($value) ? [] : json_decode($value, true);
    }

    public function getbasicattr($value): array
    {
        // [opcode 还原] empty($value) ? [] : json_decode($value, true)
        return empty($value) ? [] : json_decode($value, true);
    }

    public function userinitconfig(int|null $uid = null): self
    {
        // [opcode 还原] 无配置则写入全空字段初始化
        $config = self::where('user_id', $uid)->find();
        if ($config) {
            return $config;
        }
        $this->save([
            'user_id' => $uid,
            'real_name' => [],
            'google_auth' => [],
            'notify_config' => [],
            'channel_config' => [],
            'basic' => [],
        ]);
        return $this;
    }

}
