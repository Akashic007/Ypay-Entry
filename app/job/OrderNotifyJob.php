<?php

namespace app\job;

use app\admin\model\Order;
use app\common\event\OrderNotify;
use app\common\service\OrderService;
use think\console\Output;
use think\facade\Event;
use think\facade\Log;
use think\queue\Job;

/**
 * 订单商户回调通知队列
 */
class OrderNotifyJob
{
    public function fire(Job $job, array $data): void
    {
        $output = new Output();
        $orderId = (int)($data['order_id'] ?? 0);

        if ($orderId <= 0) {
            Log::warning('[OrderNotify] 订单通知任务缺少订单ID：' . json_encode($data, JSON_UNESCAPED_UNICODE));
            $job->delete();
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::warning('[OrderNotify] 订单不存在：' . json_encode($data, JSON_UNESCAPED_UNICODE));
            $job->delete();
            return;
        }

        if ((int)$order->notify_status === 1) {
            $job->delete();
            return;
        }

        try {
            $url = (new OrderService())->getNotifyUrl($order);
            $res = httpCurl($url['notify']);

            $order->callback = $res['body'] ?? '';
            if (($res['status'] ?? 0) == 200 || ($res['status'] ?? 0) == 204) {
                $order->notify_status = 1;
            } else {
                $order->notify_status = 2;
            }
            $order->save();

            if ((int)$order->notify_status === 1) {
                // 回调成功通知
                Event::trigger(new OrderNotify($order, OrderNotify::CALLBACK, [
                    'notify_time' => date('Y-m-d H:i:s'),
                ]));
            }

            $job->delete();
        } catch (\Throwable $e) {
            if ($job->attempts() < 3) {
                $job->release(60);
                return;
            }

            $order->callback = $e->getMessage();
            $order->notify_status = 2;
            $order->save();

            $message = '[' . date('Y-m-d H:i:s') . '] [OrderNotify] 订单[' . $orderId . ']通知任务失败：' . $e->getMessage();
            $output->writeln($message);
            Log::error($message . ' data=' . json_encode($data, JSON_UNESCAPED_UNICODE));
            $job->delete();
        }
    }
}
