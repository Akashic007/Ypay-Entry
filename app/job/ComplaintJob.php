<?php

namespace app\job;

use app\admin\model\EntryChannel;
use app\common\service\complaint\ComplaintFactory;
use Exception;
use think\console\Output;
use think\queue\Job;

/**
 * 投诉内容处理
 */
class ComplaintJob
{
    /**
     * 获取投诉详情
     */
    public function getDetail(Job $job, $data): void
    {
        $output = new Output();
        $output->writeln("[" . date('Y-m-d H:i:s') . "] 收到投诉[" . $data['complaint_id'] . ']正在录入投诉信息');
        $entryChannel = EntryChannel::find($data['ec_id']);
        if (empty($entryChannel)) {
            $output->writeln("[" . date('Y-m-d H:i:s') . "] 投诉单号[" . $data['complaint_id'] . ']对应的通道不存在');
            $job->delete();
            return;
        }
        try {
            $class = ComplaintFactory::create($entryChannel);
            $class->getComplaintDetail($data);
        } catch (Exception $e) {
            $output->writeln("[" . date('Y-m-d H:i:s') . "] 投诉单号[" . $data['complaint_id'] . ']处理失败：' . $e->getMessage());
            $job->delete();
        }
        $job->delete();
    }

}