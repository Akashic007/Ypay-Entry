<?php

namespace app\job;

use app\admin\model\channel\Account;
use app\admin\model\Order;
use app\admin\model\User;
use app\common\service\notify\Notify;
use app\common\service\notify\TemplateParser;
use think\console\Output;
use think\facade\Log;
use think\queue\Job;

/**
 * 消息通知队列
 */
class NotifyJob
{
    public function fire(Job $job, array $data): void
    {
        $event = $data['event'] ?? '';
        $params = $data['params'] ?? [];

        $output = new Output();
        try {
            if ($event === '') {
                Log::warning('[Notify] 通知事件为空：' . json_encode($data, JSON_UNESCAPED_UNICODE));
                $job->delete();
                return;
            }

            $user = $this->resolveUser($data, $params);
            if (!$user) {
                Log::warning('[Notify] 通知用户不存在：' . json_encode($data, JSON_UNESCAPED_UNICODE));
                $job->delete();
                return;
            }

            (new Notify())->msgNotify($event, $user, $params);
            $job->delete();
        } catch (\Throwable $e) {

            if ($job->attempts() < 3) {
                $job->release(60);
                return;
            }

            $output->writeln("[" . date('Y-m-d H:i:s') . "] [Notify] 通知任务失败：" . $e->getMessage() . ' data=' . json_encode($data, JSON_UNESCAPED_UNICODE));

            Log::error('[Notify] 通知任务失败：' . $e->getMessage() . ' data=' . json_encode($data, JSON_UNESCAPED_UNICODE));
            $job->delete();
        }
    }

    protected function resolveUser(array $data, array $params): ?User
    {
        $userId = $data['user_id'] ?? $params['user_id'] ?? null;
        if ($userId) {
            return User::find((int)$userId);
        }

        $event = $data['event'] ?? '';
        $config = TemplateParser::getTpConfig($event);
        $eventType = $config['event_type'] ?? '';

        if ($eventType === 'order' && !empty($params['order_id'])) {
            $order = Order::find((int)$params['order_id']);
            return $order ? User::find((int)$order->user_id) : null;
        }

        if ($eventType === 'channel' && !empty($params['channel_id'])) {
            $channel = Account::find((int)$params['channel_id']);
            return $channel ? User::find((int)$channel->user_id) : null;
        }

        return null;
    }
}