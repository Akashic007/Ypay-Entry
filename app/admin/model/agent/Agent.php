<?php

// 还原自 Swoole Compiler 加密文件: Agent.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/agent/Agent.php

namespace app\admin\model\agent;

class Agent extends \think\Model {
    protected $name = 'agent';
    protected $autoWriteTimestamp = true;
    public static function onafterinsert(\think\Model|null $model = null): void
    {
        (new \app\admin\model\agent\Agent())->resetinvitecode($model->id);
        (new \app\admin\model\agent\Agent())->resetapiid($model->id);
        (new \app\admin\model\agent\Agent())->resetapikey($model->id);
    }

    public function user(): \think\model\relation\BelongsTo
    {
        // [opcode 真值] belongsTo(app\admin\model\User, 'user_id', 'id')
        return $this->belongsTo(\app\admin\model\User::class, 'user_id', 'id');
    }

    public function subscribe(): \think\model\relation\BelongsTo
    {
        // [opcode 真值] belongsTo(agent\Subscribe, 'subscribe_id', 'id')
        return $this->belongsTo(\app\admin\model\agent\Subscribe::class, 'subscribe_id', 'id');
    }

    public function getpayaccountattr($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setpayaccountattr($value): string
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?: [];
        }
        $value = is_array($value) ? $value : [];
        return json_encode([
            'account_type' => $value['account_type'] ?? 'alipay',
            'account'      => $value['account'] ?? '',
            'qrcode_type'  => $value['qrcode_type'] ?? 'alipay',
            'qrcode'       => $value['qrcode'] ?? '',
        ]);
    }

    public function resetinvitecode($id): void
    {
        $model  = static::find($id);
        $random = generate_random_string(12);
        while (static::where('invite_code', $random)->find()) {
            $random = generate_random_string(12);
        }
        $model->invite_code = $random;
        $model->save();
    }

    public function resetapiid($id): void
    {
        $model  = static::find($id);
        $random = generate_random_string(18);
        while (static::where('api_id', $random)->find()) {
            $random = generate_random_string(18);
        }
        $model->api_id = $random;
        $model->save();
    }

    public function resetapikey($id): void
    {
        $model  = static::find($id);
        $random = generate_random_string(32, 3);
        while (static::where('api_key', $random)->find()) {
            $random = generate_random_string(32, 3);
        }
        $model->api_key = $random;
        $model->save();
    }

}
