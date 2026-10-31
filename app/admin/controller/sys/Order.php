<?php

// 还原自 Swoole Compiler 加密文件: Order.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/sys/Order.php

namespace app\admin\controller\sys;

class Order extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', 'create_time'];
    protected array $withJoinTable = ['user', 'channel'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\sys\Order();
    }

    public function manual(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('订单ID为空');
        }
        $order = \app\admin\model\sys\Order::find($id);
        if (empty($order)) {
            $this->error('订单不存在');
        }
        try {
            $service = new \app\common\service\sys\OrderService();
            $service->callbackSuccess($order);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('补单成功');
    }

    public function cleardata(): void
    {
        $data = $this->request->param(['time', 'status']);
        if (empty($data['time'])) {
            $time = time();
        } elseif (is_numeric($data['time'])) {
            if (strlen($data['time']) === 13) {
                $time = intval($data['time'] / 1000);
            } else {
                $time = intval($data['time']);
            }
        } else {
            $time = strtotime($data['time']);
        }
        $query = $this->model->where('create_time', '<', $time);
        if (isset($data['status'])) {
            if (!in_array($data['status'], ['0', '1'])) {
                $this->error('状态参数错误');
            }
            $query = $query->where('status', $data['status']);
        }
        $query->delete();
        $this->success('清理成功');
    }

}
