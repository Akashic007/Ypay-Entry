<?php

// 还原自 Swoole Compiler 加密文件: Complaint.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Complaint.php

namespace app\api\controller;

class Complaint extends \app\common\controller\Frontend {
    protected array $noNeedPermission = ['getList'];

    public function getList(): void
    {
        $limit = (int)$this->request->param('limit', 10);
        if ($limit == 0) {
            $limit = 10;
        }

        $filter = $this->request->param(['status', 'wait_reply', 'is_refund', 'platform', 'complaint_no', 'order_no']);

        $query = \app\admin\model\Complaint::where('user_id', $this->auth->id)
            ->withoutField(['res_content'])
            ->with([
                'merchant' => function ($query) {
                    $query->field(['id', 'name']);
                },
                'ec'       => function ($query) {
                    $query->field(['id', 'name']);
                },
                'sp'       => function ($query) {
                    $query->field(['id']);
                },
            ])
            ->order('id', 'desc');

        foreach (['status', 'wait_reply'] as $v) {
            if (isset($filter[$v]) && $filter[$v] !== '') {
                $query->where($v, $filter[$v]);
            }
        }

        $platform = trim((string)($filter['platform'] ?? ''));
        if ($platform !== '') {
            $query->where('platform', $platform);
        }

        $complaintNo = trim((string)($filter['complaint_no'] ?? ''));
        if ($complaintNo !== '') {
            $query->whereLike('complaint_no', '%' . str_replace('%', '\%', $complaintNo) . '%');
        }

        $orderNo = trim((string)($filter['order_no'] ?? ''));
        if ($orderNo !== '') {
            $query->whereLike('order_no', '%' . str_replace('%', '\%', $orderNo) . '%');
        }

        $complaintTimeStart = trim((string)($filter['complaint_time_start'] ?? ''));
        if ($complaintTimeStart !== '') {
            $query->whereTime('complaint_time', '>=', $complaintTimeStart);
        }

        $complaintTimeEnd = trim((string)($filter['complaint_time_end'] ?? ''));
        if ($complaintTimeEnd !== '') {
            $query->whereTime('complaint_time', '<=', $complaintTimeEnd);
        }

        $createTimeStart = trim((string)($filter['create_time_start'] ?? ''));
        if ($createTimeStart !== '') {
            $query->whereTime('create_time', '>=', $createTimeStart);
        }

        $createTimeEnd = trim((string)($filter['create_time_end'] ?? ''));
        if ($createTimeEnd !== '') {
            $query->whereTime('create_time', '<=', $createTimeEnd);
        }

        $res = $query->paginate($limit);

        $this->success('ok', [
            'list'  => $res->items(),
            'total' => $res->total(),
        ]);
    }

}
