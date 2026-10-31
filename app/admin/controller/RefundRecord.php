<?php

// 还原自 Swoole Compiler 加密文件: RefundRecord.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/RefundRecord.php

namespace app\admin\controller;

class RefundRecord extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'callback', null];
    protected array $withJoinTable = ['user', 'orderTable'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\RefundRecord();
    }

    protected function appendchanneltypename(array|null $list = null): array
    {
        $channelTypes = [];
        foreach ($list as $item) {
            $channelType = $item->channel->channel_type ?? null;
            if (empty($channelType)) {
                continue;
            }
            $channelTypes[] = $channelType;
        }
        if (empty($channelTypes)) {
            return $list;
        }
        $typeArr = \app\admin\model\channel\Type::whereIn('type', array_unique($channelTypes))->column('name', 'type');
        foreach ($list as $item) {
            $channel = $item->channel ?? null;
            $channelType = $channel->channel_type ?? null;
            if (empty($channelType)) {
                continue;
            }
            $channel->channel_type_name = $typeArr[$channelType] ?? null;
        }
        return $list;
    }

    public function queryrefund(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('退款记录ID为空');
        }
        $refundRecord = \app\admin\model\RefundRecord::find($id);
        if (empty($refundRecord)) {
            $this->error('退款记录不存在');
        }
        $order = \app\admin\model\Order::find($refundRecord->order_id);
        if (empty($order)) {
            $this->error('关联订单不存在');
        }
        try {
            $payService = new \app\common\service\PayService();
            $payService->queryRefund($order, $refundRecord);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('查询成功，退款状态已更新');
    }

    public function retryrefund(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('退款记录ID为空');
        }
        $refundRecord = \app\admin\model\RefundRecord::find($id);
        if (empty($refundRecord)) {
            $this->error('退款记录不存在');
        }
        if ($refundRecord->status == 1) {
            $this->error('该退款已成功，无需重试');
        }
        if ($refundRecord->status == 0) {
            $this->error('退款正在处理中，请稍后查询');
        }
        $order = \app\admin\model\Order::find($refundRecord->order_id);
        if (empty($order)) {
            $this->error('关联订单不存在');
        }
        try {
            $refundRecord->status = 0;
            $refundRecord->save();
            $payService = new \app\common\service\PayService();
            $payService->refund($order, $refundRecord);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('退款重试已发起');
    }

    public function cleardata(): void
    {
        $data = $this->request->param(['time', 'status']);
        if (empty($data['time'])) {
            $time = time();
        } else {
            if (is_numeric($data['time'])) {
                if (strlen($data['time']) === 13) {
                    $time = intval($data['time'] / 1000);
                } else {
                    $time = intval($data['time']);
                }
            } else {
                $time = strtotime($data['time']);
            }
        }
        $query = $this->model->where('create_time', '<', $time);
        if (isset($data['status'])) {
            if (!in_array($data['status'], ['0', '1', '2'])) {
                $this->error('状态参数错误');
            }
            $query = $query->where('status', $data['status']);
        }
        $query->delete();
        $this->success('清理成功');
    }

}
