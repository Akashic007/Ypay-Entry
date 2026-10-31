<?php

// 还原自 Swoole Compiler 加密文件: UpPayChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/up/UpPayChannel.php

namespace app\common\library\payChannel\up;

abstract class UpPayChannel implements \app\common\service\channel\ChannelInterface, \app\common\service\channel\Refundable {
    protected \app\admin\model\channel\Account $account;
    protected \app\admin\model\supply\Upstream $upstream;
    public function __construct(\app\admin\model\channel\Account $account)
    {
        if ($account->source_type != 'up') {
            throw new \Exception('非上游通道');
        }
        $merchant = \app\admin\model\merchant\Merchant::find($account->mch_id);
        if (empty($merchant)) {
            throw new \Exception('商户不存在');
        }
        if (empty($account->source_id)) {
            throw new \Exception('商户上游ID不存在');
        }
        $upstream = \app\admin\model\supply\Upstream::find($account->source_id);
        if (empty($upstream)) {
            throw new \Exception('上游不存在');
        }
        if ($upstream->status != 1) {
            throw new \Exception('上游通道未启用');
        }
        $this->upstream = $upstream;
        $this->account = $account;
    }

    public function unifiedpay(\app\admin\model\Order|null $order = null): array
    {
        $data = [
            'type' => $order->type,
            'out_trade_no' => $order->order_no,
            'name' => $order->name,
            'money' => $order->pay_money,
            'channel_id' => $this->account->source_cid,
            'client_ip' => request()->ip(),
        ];
        // [opcode 对照] 调用点只发送 1 个构造参数（new UpRequest($this->upstream)），按 opcode 真值还原
        $request = new \app\common\library\upstream\UpRequest($this->upstream);
        $request->setUrl('/supply/Payment/pay');
        $request->setParams($data);
        $response = $request->httpUrl();
        if ($response['code'] != 1) {
            throw new \Exception($response['msg']);
        }
        if (empty($response['data'])) {
            throw new \Exception('申请响应结果为空-U');
        }
        if (empty($response['data']['trade_no'])) {
            throw new \Exception('订单号为空-U');
        }
        if (empty($response['data']['pay_info'])) {
            throw new \Exception('支付结果为空-U');
        }
        return $response['data'];
    }

    public function callback(\app\admin\model\Order|null $order = null): bool
    {
    return true;
    }

    // [opcode 对照] $scene 默认值按 RECV_INIT op2=LIT('add') 真值补回（原签名 null 为管线丢失）
    public function handleparams(array|null $params = null, string|null $scene = 'add'): void
    {
        if ($scene == 'add') {
            if (empty($params['mch_id'])) {
                throw new \Exception('商户ID为空');
            }
            if (empty($params['config']['api_type'])) {
                throw new \Exception('支付产品类型为空');
            }
            $merchant = \app\admin\model\merchant\Merchant::find($params['mch_id']);
            if (empty($merchant)) {
                throw new \Exception('商户信息不存在');
            }
            if (empty($params['source_id'])) {
                throw new \Exception('上游ID为空');
            }
            $upstream = \app\admin\model\supply\Upstream::find($params['source_id']);
            $payChannel = new \app\common\library\upstream\PayChannel($upstream);
            $payChannel->apply($merchant, $this->account);
            $this->account->save();
        }
    }

    public function handledelete(): void
    {
        $merchant = \app\admin\model\merchant\Merchant::find($this->account->mch_id);
        if (empty($merchant)) {
            throw new \ba\Exception('商户不存在，请重试');
        }
        if (empty($this->account->source_id)) {
            throw new \Exception('商户上游ID不存在');
        }
        $upstream = \app\admin\model\supply\Upstream::find($this->account->source_id);
        if (empty($upstream)) {
            throw new \Exception('上游不存在，请重试');
        }
        $payChannel = new \app\common\library\upstream\PayChannel($upstream);
        $payChannel->delete($merchant, $this->account);
    }

    public function refund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        $data = [
            'order_no' => $order->order_no,
            'amount' => $record->refund_amount,
            'reason' => $record->refund_reason ?? '用户申请退款',
        ];
        // [opcode 对照] 调用点只发送 1 个构造参数（new UpRequest($this->upstream)），按 opcode 真值还原
        $request = new \app\common\library\upstream\UpRequest($this->upstream);
        $request->setUrl('/supply/Payment/refund');
        $request->setParams($data);
        $response = $request->httpUrl();
        if ($response['code'] != 1) {
            throw new \Exception($response['msg']);
        }
        $response = $response['data'];
        if (empty($response['refund_no'])) {
            throw new \Exception('退款单号为空-U');
        }
        $record->refund_trade_no = $response['refund_no'];
        $record->refund_channel = $response['refund_channel'];
        $record->refund_account = $response['refund_account'];
        $record->refund_amount = $response['refund_amount'];
        $record->status = $response['status'];
        $record->order_amount = $response['order_amount'];
        if ($response['status'] == 1) {
            $record->refund_time = time();
        }
        $record->save();
    }

    public function queryrefund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        $data = [
            'order_no' => $order->order_no,
            'refund_no' => $record->refund_trade_no ?: $record->refund_no,
        ];
        // [opcode 对照] 调用点只发送 1 个构造参数（new UpRequest($this->upstream)），按 opcode 真值还原
        $request = new \app\common\library\upstream\UpRequest($this->upstream);
        $request->setUrl('/supply/Payment/queryRefund');
        $request->setParams($data);
        $response = $request->httpUrl();
        if ($response['code'] != 1) {
            throw new \Exception($response['msg']);
        }
        $response = $response['data'];
        $record->status = $response['status'];
        if (!empty($response['refund_no'])) {
            $record->refund_trade_no = $response['refund_no'];
        }
        if (!empty($response['refund_channel'])) {
            $record->refund_channel = $response['refund_channel'];
        }
        if (!empty($response['refund_account'])) {
            $record->refund_account = $response['refund_account'];
        }
        if (!empty($response['refund_amount'])) {
            $record->refund_amount = $response['refund_amount'];
        }
        if ($response['status'] == 1) {
            $record->refund_time = time();
        }
        $record->save();
    }

}
