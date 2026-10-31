<?php

// 还原自 Swoole Compiler 加密文件: Wxpay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/payNotify/Wxpay.php
//
// [2026-09-13 opcode 全量对照还原] 微信支付(V2 XML / V3 JSON 双协议)回调：
//   payCallback: 取参数 → 订单定位(order_no) → 重复回调短路(status==1→200) →
//                渠道账户定位(channel_id) → ChannelFactory 渠道实例 → 渠道验签/处理(callback) →
//                OrderService::callback 订单完成 → 200；失败分支 FAIL + 402/404
//   getNotifyParams: 请求原始体, xml 关键字或 '<' 开头走 parseXml, 否则 parseJson
//   parseJson/parseXml: json_decode / simplexml+json 双转数组

namespace app\api\controller\payNotify;

use app\admin\model\Order;
use app\common\service\channel\ChannelFactory;
use app\common\service\OrderService;
use think\exception\HttpResponseException;
use think\Request;
use Throwable;

class Wxpay extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['*'];

    public function payCallback(): \think\Response
    {
        $params = $this->getNotifyParams();

        $order = Order::where('order_no', $params['out_trade_no'] ?? '')->find();
        if (empty($order)) {
            return json(['code' => 'FAIL', 'message' => '订单不存在'])->code(402);
        }

        // 已支付订单的重复回调 → 直接 200
        if ($order->status == 1) {
            return \think\Response::create()->code(200);
        }

        $account = \app\admin\model\channel\Account::where('id', $order->channel_id)->find();
        $channel = ChannelFactory::create($account);

        try {
            $res = $channel->callback($order);
            if ($res) {
                (new OrderService())->callback($order);
                return \think\Response::create()->code(200);
            }
            return json(['code' => 'FAIL', 'message' => '支付失败'])->code(402);
        } catch (\Exception $e) {
            return json(['code' => 'FAIL', 'message' => $e->getMessage()])->code(404);
        }
    }

    private function getNotifyParams(): array
    {
        $input = $this->request->getInput();
        if ($input == '') {
            return [];
        }
        // V2 回调为 XML 报文, V3 回调为 JSON 报文
        if (str_contains(strtolower($input), 'xml') || str_starts_with($input, '<')) {
            return $this->parseXml($input);
        }
        return $this->parseJson($input);
    }

    private function parseJson(string|null $content = null): array
    {
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data ?? [];
        }
        return [];
    }

    private function parseXml(string|null $content = null): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content, \SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        libxml_clear_errors();
        if ($xml === false) {
            return [];
        }
        $json = json_encode($xml);
        $data = json_decode($json, true);
        if (is_string($data)) {
            return [$data];
        }
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data ?? [];
        }
        return [];
    }
}
