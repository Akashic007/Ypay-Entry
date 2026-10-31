<?php

// 还原自 Swoole Compiler 加密文件: AlipaySign.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/payNotify/AlipaySign.php
//
// [2026-09-13 opcode 全量对照还原] 支付宝签约(当面付授权)支付回调：
//   res($msg) = echo $msg; exit;  —— 回调协议响应文本
//   payCallback: post 取参 + alipayEntry.txt 入站日志 → out_trade_no 判空 → 订单定位 →
//                重复回调短路(status==1→success) → trade_status 校验 → bccomp 金额校验 →
//                渠道账户定位 → 渠道 callback 验签处理 → OrderService::callback → success
//   mchAuthCode: 支付宝授权回跳 —— state 换 AppAuthToken → 商户/通道定位 →
//                Alipay::handletoken(authorization_code) → successView/errorView

namespace app\api\controller\payNotify;

use app\admin\model\Order;
use app\common\service\channel\ChannelFactory;
use app\common\service\OrderService;

class AlipaySign extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['*'];

    public function payCallback(): void
    {
        $params = request()->post('', null, null);

        // 入站日志（原始 opcode: file_put_contents(runtime_path().'alipayEntry.txt', json_encode($params))）
        file_put_contents(runtime_path() . 'alipayEntry.txt', json_encode($params));

        if (empty($params['out_trade_no'])) {
            $this->res('fail');
        }

        $order = Order::where('order_no', $params['out_trade_no'] ?? '')->find() ?? null;
        if (empty($order)) {
            $this->res('fail');
        }

        // 重复回调短路
        if ($order->status == 1) {
            $this->res('success');
        }

        if (($params['trade_status'] ?? '') !== 'TRADE_SUCCESS' && ($params['trade_status'] ?? '') !== 'TRADE_FINISHED') {
            $this->res('fail');
        }

        // 金额一致性校验（2 位小数精度）
        if (bccomp((string) ($order->money ?? 0), (string) ($params['total_amount'] ?? 0), 2) !== 0) {
            $this->res('fail');
        }

        $account = \app\admin\model\channel\Account::where('id', $order->channel_id)->find() ?? null;
        if (empty($account)) {
            $this->res('fail');
        }

        $channel = ChannelFactory::create($account) ?? null;
        $res = $channel->callback($order);
        if (!$res) {
            $this->res('fail');
        }

        (new OrderService())->callback($order);

        $this->res('success');
    }

    /**
     * 支付宝授权回跳（服务商代商户签约授权）
     */
    public function mchAuthCode()
    {
        $params = request()->param();

        if (empty($params['state']) || empty($params['app_auth_code'])) {
            return errorView('缺少必要的回调参数');
        }

        $token = \app\common\library\alipay\AppAuthToken::verifystate($params['state']);
        $me = \app\admin\model\merchant\MerchantEntry::where('meId', $token['meId'])->find();
        if (empty($me)) {
            return errorView('商户不存在');
        }

        $entryChannel = \app\admin\model\EntryChannel::where('ec_id', $me->ec_id)->find();
        if (empty($entryChannel)) {
            return errorView('商户进件通道不存在');
        }

        $alipay = new \app\common\library\entryChannel\Alipay($entryChannel);
        try {
            $alipay->handletoken($me, 'authorization_code', $params['app_auth_code']);
        } catch (\ba\Exception $e) {
            return $this->res($e->getMessage());
        }

        return successView('授权成功');
    }

    public function openid(): mixed
    {
        return res('fail');
    }

    private function res($msg)
    {
        echo $msg;
        exit;
    }
}
