<?php

// 还原自 Swoole Compiler 加密文件: WxpayOfficialSpecial.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/payNotify/WxpayOfficialSpecial.php
//
// [2026-09-13 opcode 全量对照还原] 微信官方特约商户(V3)支付通道：
//   notify:   支付回调 —— order_no 定位订单 → 重复回调短路(status==1→200) → 渠道账户定位 →
//             WxpayOfficialSpecial 渠道实例 callback 验签 → OrderService::callback → 200
//   refund:   退款回调 —— refund_no 定位退款记录 → 渠道定位 → 渠道 refund 验签 →
//             status=1 + callback 参数 + refund_time → 保存 → 200；异常 FAIL 403
//   jsapiPay: JSAPI 支付发起 —— 订单/账户/商户/通道/服务商定位 → JsApiTool code 换 openid →
//             渠道 jsapipay 下单 → WeChatPay\Rsa 商户私钥签名 → jump/wxpay-special-jsapi 视图

namespace app\api\controller\payNotify;

use app\admin\model\Order;
use app\common\service\channel\ChannelFactory;
use app\common\service\OrderService;
use think\exception\HttpResponseException;
use think\facade\View;

class WxpayOfficialSpecial extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['*'];

    public function notify(): \think\Response
    {
        $orderNo = request()->param('order_no') ?? '';

        if (empty($orderNo)) {
            return json(['code' => 'FAIL', 'message' => '订单号为空'])->code(401);
        }

        $order = Order::where('order_no', $orderNo)->find() ?? null;
        if (empty($order)) {
            return json(['code' => 'FAIL', 'message' => '订单不存在'])->code(402);
        }

        $account = \app\admin\model\channel\Account::where('id', $order->channel_id)->find() ?? null;

        $channel = new \app\common\library\payChannel\WxpayOfficialSpecial($account);
        $res = $channel->callback($order);
        if (!$res) {
            return json(['code' => 'FAIL', 'message' => '回调验证失败'])->code(403);
        }

        // 重复回调短路
        if ($order->status == 1) {
            return \think\Response::create()->code(200);
        }

        (new OrderService())->callback($order);

        return \think\Response::create()->code(200);
    }

    public function refund(): \think\Response
    {
        $refundNo = request()->param('refund_no') ?? '';

        if (empty($refundNo)) {
            return json(['code' => 'FAIL', 'message' => '退款记录单号为空'])->code(401);
        }

        $refund = \app\admin\model\RefundRecord::where('refund_no', $refundNo)->find() ?? null;
        if (empty($refund)) {
            return json(['code' => 'FAIL', 'message' => '退款记录不存在'])->code(402);
        }

        $account = \app\admin\model\channel\Account::where('id', $refund->channel_id)->find() ?? null;
        if (empty($account)) {
            return json(['code' => 'FAIL', 'message' => '支付通道不存在'])->code(403);
        }

        $channel = new \app\common\library\payChannel\WxpayOfficialSpecial($account);
        try {
            $res = $channel->refund($refund);

            if ($res['refund_status'] == 'SUCCESS' && $res['amount']['refund'] == $refund->refund_amount) {
                $refund->status = 1;
                $refund->callback = json_encode($res);
                $refund->refund_time = time();
                $refund->save();
            }
        } catch (\Exception $e) {
            return json(['code' => 'FAIL', 'message' => $e->getMessage()])->code(403);
        }

        return \think\Response::create()->code(200);
    }

    public function jsapipay(): string
    {
        $params = request()->param(['order_no']);

        $order = Order::where('order_no', $params['order_no'] ?? '')->find() ?? null;
        if (empty($order)) {
            return errorView('订单号不存在');
        }

        $account = \app\admin\model\channel\Account::where('id', $order->channel_id)->find() ?? null;
        if (empty($account)) {
            return errorView('通道不存在');
        }

        if (empty($account->mch_id)) {
            return errorView('商户ID为空');
        }

        $merchant = \app\admin\model\merchant\Merchant::where('mch_id', $account->mch_id)->find() ?? null;
        if (empty($merchant)) {
            return errorView('商户不存在');
        }

        $entryChannel = \app\admin\model\EntryChannel::where('ec_id', $merchant->ec_id)->find() ?? null;
        if (empty($entryChannel)) {
            return errorView('服务商不存在');
        }

        $serviceProvider = \app\admin\model\ServiceProvider::where(['sp_id' => $entryChannel->sp_id, 'platform_code' => $merchant->platform_code])->find() ?? null;
        if (empty($serviceProvider)) {
            return errorView('服务商不存在');
        }

        $jsApiTool = new \app\common\library\payChannel\wxpayOfficialSpecial\JsApiTool(
            $serviceProvider->config['appId'],
            $serviceProvider->config['appSecret']
        );
        try {
            $spOpenid = $jsApiTool->openid($params['code'] ?? '');
            $merchant->extend = json_encode(['sp_openid' => $spOpenid]);
            $merchant->save();
        } catch (\Exception $e) {
            return errorView($e->getMessage());
        }

        $wxpay = new \app\common\library\payChannel\WxpayOfficialSpecial($merchant);
        try {
            $payParams = $wxpay->jsapipay($order);

            // 证书目录与商户私钥（WeChatPay V3 签名）
            $dir = runtime_path() . 'upload' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR;
            $keyPath = $dir . $account->config['apiKeyPem'];

            $config = $account->config;
            $rsa = \WeChatPay\Crypto\Rsa::from(file_get_contents($keyPath));

            $payData = [
                'appId'     => $config['appId'],
                'timeStamp' => (string) \WeChatPay\Formatter::timestamp(),
                'nonceStr'  => \WeChatPay\Formatter::nonce(),
                'package'   => 'prepay_id=' . ($payParams['prepay_id'] ?? ''),
            ];
            $payData['paySign'] = \WeChatPay\Crypto\Rsa::sign(
                \WeChatPay\Formatter::rsaSignQuery(...),
                $rsa
            );
            $payData['signType'] = 'RSA';

            return View::fetch('jump/wxpay-special-jsapi', $payData);
        } catch (\Exception $e) {
            return errorView($e->getMessage());
        }
    }
}
