<?php

// 还原自 Swoole Compiler 加密文件: PayChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/upstream/PayChannel.php

namespace app\common\library\upstream;

class PayChannel{
    protected \app\admin\model\supply\Upstream $upstream;
    public function __construct(\app\admin\model\supply\Upstream $upstream)
    {
        $this->upstream = $upstream;
    }


    public function apply(\app\admin\model\merchant\Merchant|null $merchant = null, \app\admin\model\channel\Account|null $account = null): void
    {
        $applyId = $merchant->merchant_info['apply_id'] ?? null;
        if (empty($applyId)) {
            throw new \Exception('商户进件申请ID不存在');
        }
        $ur = new UpRequest($this->upstream);
        $ur->setUrl('/supply/Channel/apply');
        $params = [
            'business_id'  => $applyId,
            'pay_type'     => $account->pay_type,
            'channel_type' => $account->channel_type,
            'cid'          => $account->id,
            'config'       => $account->config,
        ];
        $ur->setParams($params);
        $res = $ur->httpUrl();
        if ($res['code'] != 1) {
            throw new \Exception($res['msg']);
        }
        if (empty($res['data'])) {
            throw new \Exception('申请响应结果为空');
        }
        if (empty($res['data']['id'])) {
            throw new \Exception('上游返回的通道ID为空');
        }
        $account->source_cid = $res['data']['id'];
        $account->save();
    }


    public function delete(\app\admin\model\merchant\Merchant|null $merchant = null, \app\admin\model\channel\Account|null $account = null): void
    {
        $applyId = $merchant->merchant_info['apply_id'] ?? null;
        if (empty($applyId)) {
            throw new \Exception('商户进件申请ID不存在');
        }
        $ur = new UpRequest($this->upstream);
        $ur->setUrl('/supply/Channel/delete');
        $params = [
            'business_id' => $applyId,
            'cid'         => $account->source_cid,
        ];
        $ur->setParams($params);
        $res = $ur->httpUrl();
        if ($res['code'] != 1) {
            throw new \Exception($res['msg']);
        }
    }


}
