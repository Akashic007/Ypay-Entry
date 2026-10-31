<?php

namespace app\command;

use app\admin\model\channel\Account;
use Exception;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;
use think\facade\Queue;

class ChannelMonitor extends Command
{
    protected function configure(): void
    {
        $this->setName('channel:monitor')
            ->setDescription('通道监控任务'); // 命令描述
    }

    protected function execute(Input $input, Output $output): void
    {
        $output->writeln("[" . date('Y-m-d H:i:s') . "] 进入通道循环");

        while (true) {
            $channels = Account::where('status', 1)
                ->where('online_status', 1) // 在线通道
                ->whereIn('channel_type', ['alipay_personal_not_hang'])
                ->withCount(['orderTable' => function ($query) {
                    $query->where('status', 0)
                        ->where('expire_time', '>', time());
                }])
                ->order('order_table_count', 'desc')
                ->select();

            // 如果没有通道，休眠10秒后继续
            if ($channels->isEmpty()) {
                $output->writeln("[" . date('Y-m-d H:i:s') . "] 没有可用的监控通道，10秒后再次尝试");
                sleep(10);
                continue;
            }

            // 通道推送队列

            $count = 0;
            try {
                foreach ($channels as $channel) {
                    // 加锁处理
                    if (Cache::has('channel_monitor:lock:' . $channel->id)) {
                        continue;
                    }
                    $count++;
                    Cache::set('channel_monitor:lock:' . $channel->id, time(), 10);
                    Queue::push('ChannelMonitorJob', ['id' => $channel->id]);
                }
            } catch (Exception $e) {
                $output->writeln("[" . date('Y-m-d H:i:s') . "] 通道推送队列异常：" . $e->getMessage());
                sleep(3);
                continue;
            }
            sleep(1);
            $output->writeln("[" . date('Y-m-d H:i:s') . "] 扫描通道：" . count($channels) . "个，推送通道：" . $count . "个");
        }
    }
}