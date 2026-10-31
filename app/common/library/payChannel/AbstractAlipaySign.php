<?php

// 还原自 Swoole Compiler 加密文件: AbstractAlipaySign.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/AbstractAlipaySign.php

namespace app\common\library\payChannel;

abstract class AbstractAlipaySign extends \app\common\library\payChannel\BaseAlipay implements \app\common\service\channel\contract\AlipaySignInterface, \app\common\service\channel\Refundable {
    protected \app\admin\model\EntryChannel $entryChannel;
    protected \app\admin\model\merchant\Merchant $merchant;
    protected \app\admin\model\channel\Account $account;
    protected array $config;
    public function __construct(\app\admin\model\channel\Account $account)
    {
        $this->account = $account;
        $this->merchant = $account->merchant;
        $this->entryChannel = $account->merchant->ec;
        $this->config = $account->merchant->ec->config;
    }

    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
        return new \app\common\service\channel\PayInfo();
    }

    public function callback(\app\admin\model\Order|null $order = null): bool
    {
        try {
            $this->init($this->config);
            $data = request()->post('', null, null);
            return \Alipay\EasySDK\Kernel\Factory::payment()->common()->verifyNotify($data);
        } catch (Exception $e) {
            return false;
        }
    }

    // [opcode 对照] $scene 默认值按 RECV_INIT op2=LIT('add') 真值补回（原签名 null 为管线丢失）
    public function handleparams(array|null $params = null, string|null $scene = 'add'): void
    {
        if ($scene == 'add') {
            if (empty($params['mch_id'])) {
                throw new \ba\Exception('商户ID为空');
            }
            if (empty($params['config']['api_type'])) {
                throw new \ba\Exception('支付产品类型为空');
            }
            $merchant = \app\admin\model\merchant\Merchant::find($params['mch_id']);
            if (empty($merchant)) {
                throw new \ba\Exception('商户信息不存在');
            }
            if ($merchant->source == 'downstream') {
                $this->account->source_type = 'down';
                $downId = $merchant->merchant_info['down_id'] ?? null;
                if (empty($downId)) {
                    throw new \ba\Exception('下游商户ID不存在');
                }
                $this->account->source_id = $downId;
            }
            $this->account->save();
        }
    }

    public function handledelete(): void
    {
    return;
    }

    public function refund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        try {
            $this->init($this->config);
            $params = [
                'refund_amount' => $record->refund_amount ?? $order->pay_money,
                'trade_no' => $order->trade_no,
                'refund_reason' => $record->refund_reason ?? '',
                'out_request_no' => $record->refund_no,
                'query_options' => ['refund_detail_item_list'],
            ];
            $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.trade.refund', ['app_auth_token' => $this->merchant->merchant_info['app_auth_token']], $params);
            $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
            if (!$responseChecker->success($result)) {
                throw new \ba\Exception('退款失败：' . $result->subMsg);
            }
            $response = json_decode($result->httpBody, true);
            $response = $response['alipay_trade_refund_response'];
            if (isset($response['fund_change']) && $response['fund_change'] == 'Y') {
                $record->status = 1;
                if (empty($response['refund_detail_item_list']['fund_channel'])) {
                    $record->refund_channel = 'ALIPAY';
                } else {
                    $record->refund_channel = $response['refund_detail_item_list']['fund_channel'];
                }
                $record->refund_trade_no = $response['trade_no'];
                $record->refund_account = $response['buyer_logon_id'] ?? $response['buyer_user_id'] ?? $response['buyer_open_id'];
                $record->refund_time = time();
                $record->save();
            }
        } catch (\ba\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
    }

    public function queryrefund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
        try {
            $this->init($this->config);
            $params = [
                'out_request_no' => $record->refund_no,
                'trade_no' => $order->trade_no,
                'query_options' => ['refund_detail_item_list'],
            ];
            $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.trade.fastpay.refund.query', ['app_auth_token' => $this->merchant->merchant_info['app_auth_token']], $params);
            $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
            if (!$responseChecker->success($result)) {
                throw new \ba\Exception('退款失败：' . $result->subMsg);
            }
            $response = json_decode($result->httpBody, true);
            $response = $response['alipay_trade_fastpay_refund_query_response'];
            if (isset($response['refund_status']) && $response['refund_status'] == 'REFUND_SUCCESS') {
                $record->status = 1;
                if (empty($response['refund_detail_item_list']['fund_channel'])) {
                    $record->refund_channel = 'ALIPAY';
                } else {
                    $record->refund_channel = $response['refund_detail_item_list']['fund_channel'];
                }
                $record->refund_trade_no = $response['trade_no'];
                $record->refund_account = $response['buyer_logon_id'] ?? $response['buyer_user_id'] ?? $response['buyer_open_id'] ?? '原路退款';
                $record->refund_amount = $response['refund_amount'];
                $record->refund_time = time();
                $record->save();
            }
        } catch (\ba\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
    }

    public function getauthtoken(\app\admin\model\Order|null $order = null, string|null $type = null, string|null $data = null): void
    {
        $this->init($this->config);
        $extend = $order->extend ?? [];
        $request = ['grant_type' => $type];
        if ($type == 'authorization_code') {
            $request['code'] = $data;
        } else {
            $request['refresh_token'] = $data;
        }
        $result = \Alipay\EasySDK\Kernel\Factory::util()->generic()->execute('alipay.system.oauth.token', $request, []);
        $responseChecker = new \Alipay\EasySDK\Kernel\Util\ResponseChecker();
        if (!$responseChecker->success($result)) {
            throw new \ba\Exception($result->subMsg);
        }
        $response = json_decode($result->httpBody, true);
        $response = $response['alipay_system_oauth_token_response'];
        $order->extend = array_merge_deep($extend, $response);
        $order->save();
    }

    protected function getapi(): string
    {
    return 'alipay.trade.create';
    }

}
