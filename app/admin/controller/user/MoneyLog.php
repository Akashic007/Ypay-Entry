<?php

// 还原自 Swoole Compiler 加密文件: MoneyLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/MoneyLog.php

namespace app\admin\controller\user;

class MoneyLog extends \app\common\controller\Backend {
    protected object $model;
    protected array $withJoinTable = ['user'];
    protected string|array $preExcludeFields = ['create_time'];
    protected string|array $quickSearchField = ['user.username', 'user.nickname'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\UserMoneyLog();
    }

    /**
     * 添加页（opcode 真值：POST 时 parent::add()；GET 时 User::where(id, userId)->find()
     * 未命中 error(__("The user can't find it"))，命中 success('', ['user' => $user])）
     */
    public function add(int $userId = 0): void
    {
        if ($this->request->isPost()) {
            parent::add();
        }

        $user = \app\admin\model\User::where('id', $userId)->find();
        if (!$user) {
            $this->error(__("The user can't find it"));
        }

        $this->success('', [
            'user' => $user,
        ]);
    }

    /**
     * 清理资金日志（opcode 真值：post(['time','type'])；in_array(type, ['0'..'5']) 否则 error('日志类型错误')；
     * time 为空→time()；is_numeric 时 strlen==13 → intval(time/1000)，否则 intval(time)；非数字→strtotime(time)；
     * model->where('create_time','<',$time)，type != '0' 时追加 where('type', $data['type'])，delete()）
     */
    public function cleardata(): void
    {
        $data = $this->request->post(['time', 'type']);
        if (!in_array($data['type'], ['0', '1', '2', '3', '4', '5'])) {
            $this->error('日志类型错误');
        }
        if (empty($data['time'])) {
            $time = time();
        } elseif (is_numeric($data['time'])) {
            if (strlen($data['time']) == 13) {
                $time = intval($data['time'] / 1000);
            } else {
                $time = intval($data['time']);
            }
        } else {
            $time = strtotime($data['time']);
        }

        $query = $this->model->where('create_time', '<', $time);
        if ($data['type'] != '0') {
            $query->where('type', $data['type']);
        }
        $query->delete();

        $this->success('清理成功');
    }

}
