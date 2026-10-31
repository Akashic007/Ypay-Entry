<?php

// 还原自 Swoole Compiler 加密文件: PushNotifyJob.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/listener/PushNotifyJob.php

namespace app\common\listener;

class PushNotifyJob{
    public function handle(\app\common\event\ShouldNotify|null $event = null): void
    {
        $payload = [
            'event' => $event->getNotifyEvent(),
            'user_id' => $event->getNotifyUserId(),
            'params' => $event->getNotifyParams(),
        ];
        try {
            \think\facade\Queue::push(\app\job\NotifyJob::class, $payload);
        } catch (\Throwable $e) {
            \think\facade\Log::error('[Notify] 推送通知队列失败：' . $e->getMessage() . ' payload=' . json_encode($payload, JSON_UNESCAPED_UNICODE));
        }
    }

}
