<?php

// 还原自 Swoole Compiler 加密文件: User.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/model/User.php

namespace app\common\model;

class User extends \think\Model {
    protected $autoWriteTimestamp = true;
    protected $append = ['subscribe_info', 'bind_list'];
    public static function onafterinsert($model): void
    {
        \app\admin\model\user\Config::create([
            'user_id' => $model->id,
            'real_name' => [],
            'google_auth' => [],
            'notify_config' => [],
            'channel_config' => [
                'floating_amount' => '0.01,0.02,0.03,0.04,0.05,0.06,0.07,0.08,0.09,0.10',
                'timeout_jump_type' => 'source',
                'timeout_url' => '',
                'timeout' => 300,
                'alipay_jump_type' => '4',
                'order_prefix' => 'Y',
            ],
            'basic' => [
                'pay_template' => 'console',
            ],
        ]);
    }

    public function getavatarattr($value): string
    {
        return full_url($value, false, config('buildadmin.default_avatar'));
    }

    public function setavatarattr($value): string
    {
        return $value == full_url('', false, config('buildadmin.default_avatar')) ? '' : $value;
    }

    public function resetpassword($uid, $newPassword): \app\common\model\User
    {
        return self::where(['id' => $uid])->update(['password' => hash_password($newPassword)]);
    }

    public function resetkey($uid): string
    {
        do {
            $newKey = generate_random_string();
            $user = self::where('key', $newKey)->column('id');
        } while ($user);
        self::where(['id' => $uid])->update(['key' => $newKey]);
        return $newKey;
    }

    public function getmoneyattr($value): string
    {
        return (string) ($value ?? 0);
    }

    public function setmoneyattr($value): string
    {
        return (string) ($value ?? 0);
    }
    public function agent(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\admin\model\agent\Agent::class, 'agent_id');
    }

    public function getsubscribeinfoattr($value, $data): array
    {
        if (!isset($data['subscribe_id'])) {
            return [];
        }
        $subscribe = \app\admin\model\user\Subscribe::field(['id', 'name', 'desc', 'price'])->find($data['subscribe_id']);
        $subscribe = $subscribe ? $subscribe->toArray() : ['id' => 0, 'name' => '暂未开通'];
        return ['expire_time' => $data['subscribe_expire_time'] ?? '', ...$subscribe];
    }

    public function getbindlistattr($value, $data): array
    {
        $oauthConfig = new \app\admin\model\oauth\Config();
        $methods = $oauthConfig->getLoginMethod();
        $logs = \app\admin\model\oauth\Log::where(['user_id' => $data['id'], 'status' => 1])
            ->field(['type', 'status'])
            ->select()->toArray();
        $list = [];
        foreach ($methods as $key => $method) {
            $data = $method;
            $data['type'] = $key;
            $data['status'] = 0;
            foreach ($logs as $log) {
                if ($data['type'] == $log['type']) {
                    $data['status'] = 1;
                }
            }
            $list[] = $data;
        }
        return $list;
    }

    public function gettotpauthstatusattr($value, $data): array
    {
        $config = \app\admin\model\user\Config::where('user_id', $data['id'])->find();
        $bindStatus = empty($config->google_auth['secret']) ? 0 : 1;
        $isOpen = isset($config->google_auth['status']) && $config->google_auth['status'] == '1' ? 1 : 0;
        return ['bind_status' => $bindStatus, 'is_open' => $isOpen];
    }

    public function getgoogleauthattr($value, $data): array
    {
        $config = \app\admin\model\user\Config::where('user_id', $data['id'])->find();
        if ($config) {
            return $config->google_auth;
        }
        return [];
    }

    public function realname(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\admin\model\user\RealName::class, 'user_id');
    }

    public function userconfig(): \think\model\relation\HasOne
    {
        return $this->hasOne(\app\admin\model\user\Config::class, 'user_id', 'id');
    }

}
