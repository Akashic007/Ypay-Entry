<?php

// 还原自 Swoole Compiler 加密文件: UpEntry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/entryChannel/up/UpEntry.php

namespace app\common\library\entryChannel\up;

abstract class UpEntry extends \app\common\library\entryChannel\AbstractEntry {
    protected \app\admin\model\supply\Upstream $upstream;
    protected int $upChannelId;
    public function __construct(\app\admin\model\EntryChannel $channel)
    {
        parent::__construct($channel);
        $config = $channel->config;
        if (empty($config['up_id'])) {
            throw new \ba\Exception('通道配置错误：UP ID不存在');
        }
        if (empty($config['up_channel_id'])) {
            throw new \ba\Exception('通道配置错误：上游通道ID不存在');
        }
        $this->upstream = \app\admin\model\supply\Upstream::find($config['up_id']);
        if (empty($this->upstream)) {
            throw new \ba\Exception('通道初始化错误：UP不存在');
        }
        $this->upChannelId = (int)$config['up_channel_id'];
    }

    public function apply(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null, bool|null $draft = null): void
    {
        $params = ['channel_id' => $this->upChannelId, 'draft' => $draft ? 1 : 0];
        if (!empty($mchEntry->apply_data)) {
            $params['merchant_data'] = $mchEntry->apply_data;
        }
        if ($mchEntry->apply_id) {
            $params['business_id'] = $mchEntry->apply_id;
        }
        try {
            $ur = new \app\common\library\upstream\UpRequest($this->upstream);
            $ur->setParams($params);
            $ur->setUrl('/supply/entry/apply');
            $res = $ur->httpUrl();
        } catch (\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
        if ($res['code'] != 1) {
            throw new \ba\Exception($res['msg']);
        }
        if (empty($res['data'])) {
            throw new \ba\Exception('申请响应结果为空');
        }
        $mchEntry->status = $res['data']['status'];
        $mchEntry->apply_id = $res['data']['business_id'];
        $mchEntry->save();
    }

    public function getapplyinfo(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null): array
    {
        if (empty($mchEntry->apply_id)) {
            throw new \ba\Exception('无法找到业务编号的记录');
        }
        $params = ['business_id' => $mchEntry->apply_id];
        try {
            $ur = new \app\common\library\upstream\UpRequest($this->upstream);
            $ur->setParams($params);
            $ur->setUrl('/supply/Entry/getStatus');
            $res = $ur->httpUrl('GET');
        } catch (\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
        if ($res['code'] != 1) {
            throw new \ba\Exception($res['msg']);
        }
        if (empty($res['data'])) {
            throw new \ba\Exception('申请响应结果为空');
        }
        $mchEntry->status = $res['data']['status'];
        if (!empty($res['data']['audit_result'])) {
            $mchEntry->audit_result = $res['data']['audit_result'];
        }
        if (!empty($res['data']['apply_result'])) {
            $mchEntry->apply_result = $res['data']['apply_result'];
        }
        $mchEntry->save();
        if (!empty($res['data']['merchant'])) {
            if (!empty($res['data']['merchant']['merchant_id'])) {
                $this->syncMerchant($mchEntry, $res['data']['merchant']);
            }
        }
        return $res['data'];
    }

    public function uploadmedia(\app\common\model\Attachment|null $attachment = null): array
    {
        $path = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'upload' . $attachment->url;
        if (!is_file($path)) {
            throw new \ba\Exception('文件不存在：' . $path);
        }
        $mimetype = $attachment->mimetype ?? 'application/octet-stream';
        $name = $attachment->name ?? basename($path);
        $file = new \CURLFile($path, $mimetype, $name);
        $params = [
            'file'   => $file,
            'ec_id'  => $this->upChannelId,
            'supply' => 'local',
        ];
        $ur = new \app\common\library\upstream\UpRequest($this->upstream);
        $ur->setParams($params);
        $ur->setUrl('/supply/Entry/mediaUpload');
        $res = $ur->httpUrl();
        if ($res['code'] != 1) {
            throw new \ba\Exception($res['msg']);
        }
        if (empty($res['data'])) {
            throw new \ba\Exception('申请响应结果为空');
        }
        if (empty($res['data']['media_id'])) {
            throw new \ba\Exception('媒体ID返回不存在');
        }
        $attachment->media_id = $res['data']['media_id'];
        if (!empty($res['data']['url'])) {
            $extend = $attachment->extend ?? [];
            $extend['up_url'] = $res['data']['url'];
            $attachment->extend = $extend;
        }
        $attachment->save();
        return $attachment->toArray();
    }

    protected function syncmerchant(\app\admin\model\merchant\MerchantEntry|null $mchEntry = null, array|null $mchRes = null): void
    {
        $merchant = \app\admin\model\merchant\Merchant::where([
            'user_id'     => $mchEntry->user_id,
            'merchant_id' => $mchRes['merchant_id'],
            'ec_id'       => $mchEntry->ec_id,
        ])->find();
        if (empty($merchant)) {
            $mchInfo = $mchRes['merchant_info'] ?? [];
            if (is_string($mchInfo)) {
                $mchInfo = json_decode($mchInfo, true) ?: [];
            }
            if ($mchEntry->up_id) {
                $mchInfo['up_id'] = $mchEntry->up_id;
            }
            if ($mchEntry->apply_id) {
                $mchInfo['apply_id'] = $mchEntry->apply_id;
            }
            $merchant = \app\admin\model\merchant\Merchant::create([
                'user_id'       => $mchEntry->user_id,
                'ec_id'         => $mchEntry->ec_id,
                'sp_id'         => $mchEntry->sp_id,
                'name'          => $mchInfo['name'],
                'merchant_id'   => $mchRes['merchant_id'],
                'platform_code' => $mchRes['platform_code'],
                'merchant_info' => $mchInfo,
                'config'        => $mchRes['config'],
                'source'        => 'local',
                'status'        => $mchRes['status'] ?? 1,
            ]);
        }
        $merchant->name = $mchRes['name'];
        $merchant->merchant_id = $mchRes['merchant_id'];
        $merchant->platform_code = $mchRes['platform_code'];
        $mchInfo = $merchant->merchant_info ?: [];
        $resInfo = $mchRes['merchant_info'] ?? [];
        if (is_string($resInfo)) {
            $resInfo = json_decode($resInfo, true) ?: [];
        }
        $merchant->merchant_info = array_merge($mchInfo, $resInfo);
        $merchant->config = $mchRes['config'];
        $merchant->status = $mchRes['status'] ?? 1;
        $merchant->save();
        \app\admin\model\merchant\EntryMerchant::bindMerchant($mchEntry->id, $merchant->id);
    }

    public function getconfig(): array
    {
    return [];
    }

}
