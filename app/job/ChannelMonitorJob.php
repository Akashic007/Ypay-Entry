<?php

namespace app\job;

use app\admin\model\channel\Account;
use app\common\service\channel\ChannelFactory;
use think\console\Output;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\queue\Job;

class ChannelMonitorJob
{
    /**
     * 通道监控任务
     * @param Job $job
     * @param $data
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function fire(Job $job, $data): void
    {
        $output = new Output();
        $account = Account::find($data['id']);
        if ($account) {
            try {
                $class = ChannelFactory::create($account);
                if (method_exists($class, 'monitor')) {
                    $class->monitor();
                }
            } catch (\Exception $e) {
                $output->writeln("[" . date('Y-m-d H:i:s') . "] 通道[$account->id]处理异常：" . $e->getMessage());
            }
            // 解锁处理
            if (Cache::has('channel_monitor:lock:' . $data['id'])) {
                Cache::delete('channel_monitor:lock:' . $data['id']);
            }
        }
        $job->delete();
    }
}