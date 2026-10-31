<?php

// 还原自 Swoole Compiler 加密文件: EntryChannelService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/EntryChannelService.php

namespace app\common\service;

class EntryChannelService{

    public function create(array|null $data = null): \app\admin\model\EntryChannel
    {
        if (empty($data['sp_id'])) {
            throw new \think\exception\ValidateException('服务商ID不能为空');
        }
        if (empty($data['name'])) {
            throw new \think\exception\ValidateException('通道名称不能为空');
        }
        if (empty($data['platform_code'])) {
            throw new \think\exception\ValidateException('平台类型不能为空');
        }
        if (empty($data['source'])) {
            throw new \think\exception\ValidateException('通道来源不能为空');
        }
        // [opcode 对照] in_array($data['source'], ['local','up'])，JMPNZ 跳过异常
        if (!in_array($data['source'], ['local', 'up'])) {
            throw new \think\exception\ValidateException('通道来源类型错误');
        }
        $config = [];
        if ($data['source'] === 'up') {
            $upChannelId = $data['up_channel_id'] ?? 0;
            if ($upChannelId) {
                $this->checkUpChannelUnique((int)$upChannelId);
            }
            $config['up_id'] = $data['up_id'] ?? 0;
            $config['up_channel_id'] = $upChannelId;
            $config['service_name'] = $data['service_name'] ?? '';
            $config['sync_time'] = time();
        }
        $model = \app\admin\model\EntryChannel::create([
            'sp_id' => $data['sp_id'],
            'name' => $data['name'],
            'platform_code' => $data['platform_code'],
            'source' => $data['source'],
            'price' => $data['price'] ?? 0,
            'order_rate' => $data['order_rate'] ?? 0,
            'status' => $data['status'] ?? 1,
            'config' => $config,
        ]);
        if ($model->source === 'up') {
            $this->syncUpChannel($model->id);
        }
        return $model;
    }

    private function checkupchannelunique(int|null $upChannelId = null, int|null $excludeChannelId = null): void
    {
        $query = \app\admin\model\EntryChannel::where('source', 'up')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(config, '$.up_channel_id')) = ?", [(string)$upChannelId]);
        if (0 < $excludeChannelId) {
            $query->where('id', '<>', $excludeChannelId);
        }
        $count = $query->count();
        if (0 < $count) {
            throw new \think\exception\ValidateException('该上游通道已被使用，每条上游通道只能绑定一次');
        }
    }

    public function update(int|null $id = null, array|null $data = null): \app\admin\model\EntryChannel
    {
        $model = \app\admin\model\EntryChannel::find($id);
        if (empty($model)) {
            throw new \think\exception\ValidateException('进件通道不存在');
        }
        $updates = [];
        foreach (['name', 'platform_code', 'price', 'order_rate', 'status'] as $field) {
            if (isset($data[$field])) {
                $updates[$field] = $data[$field];
            }
        }
        $config = $model->config ?? [];
        $changed = false;
        foreach (['up_id', 'up_channel_id', 'service_name'] as $key) {
            if (isset($data[$key])) {
                if ($key === 'up_channel_id' && $data[$key] != ($config['up_channel_id'] ?? 0)) {
                    $this->checkUpChannelUnique((int)$data[$key], $id);
                }
                $config[$key] = $data[$key];
                $changed = true;
            }
        }
        if ($changed) {
            $updates['config'] = $config;
        }
        if ($updates) {
            $model->save($updates);
        }
        return $model;
    }

    public function delete(int|null $id = null): void
    {
        $model = \app\admin\model\EntryChannel::find($id);
        if (empty($model)) {
            throw new \think\exception\ValidateException('进件通道不存在');
        }
        $model->delete();
    }

    public function syncupchannel(int|null $channelId = null): array
    {
        $channel = \app\admin\model\EntryChannel::find($channelId);
        if (empty($channel)) {
            throw new \think\exception\ValidateException('进件通道不存在');
        }
        if ($channel->source !== 'up') {
            throw new \think\exception\ValidateException('该通道不是上游通道');
        }
        $config = $channel->config;
        if (empty($config['up_id'])) {
            throw new \think\exception\ValidateException('上游通道配置不完整：UP ID不存在');
        }
        if (empty($config['up_channel_id'])) {
            throw new \think\exception\ValidateException('上游通道配置不完整：上游通道ID不存在');
        }
        $upstream = \app\admin\model\supply\Upstream::find($config['up_id']);
        if (empty($upstream)) {
            throw new \think\exception\ValidateException('关联上游不存在');
        }
        try {
            $request = new \app\common\library\upstream\UpRequest($upstream);
            $service = new \app\common\library\upstream\EntryChannel($request);
            $result = $service->getChannel((string)$config['up_channel_id']);
            // [opcode 对照] file_put_contents('alipayEntry.txt', json_encode($result)) 为原样保留的调试落盘
            file_put_contents('alipayEntry.txt', json_encode($result));
            if (!empty($result['payCapabilities'])) {
                $config['payCapabilities'] = json_decode($result['payCapabilities']);
            }
            $config['sync_time'] = time();
            $channel->config = $config;
            $channel->save();
            return ['exists' => true, 'sync_time' => date('Y-m-d H:i:s', $config['sync_time'])];
        } catch (\Exception $e) {
            return ['exists' => false, 'message' => $e->getMessage()];
        }
    }

    public function setlocalconfig(int|null $channelId = null, array|null $config = null): \app\admin\model\EntryChannel
    {
        $channel = \app\admin\model\EntryChannel::find($channelId);
        if (empty($channel)) {
            throw new \think\exception\ValidateException('进件通道不存在');
        }
        $channel->config = $config;
        $channel->save();
        return $channel;
    }

    public function getlocalconfig(int|null $channelId = null): array
    {
        $channel = \app\admin\model\EntryChannel::find($channelId);
        if ($channel) {
            return $channel->config ?? [];
        }
        return [];
    }

}
