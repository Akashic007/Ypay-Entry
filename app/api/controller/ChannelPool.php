<?php

// 还原自 Swoole Compiler 加密文件: ChannelPool.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/ChannelPool.php

namespace app\api\controller;

class ChannelPool extends \app\common\controller\Frontend {
    protected array $middleware = ['app\\common\\middleware\\YAuthCheck' => ['only' => ['add', 'edit', 'del'], ], 'app\\common\\middleware\\UserCheck'];
    public function initialize(): void
    {
        parent::initialize();
    }
    public function getpoollist(): void
    {
        $limit   = (int) $this->request->param('limit', 10);
        $payType = $this->request->param('pay_type', null);

        $model = \app\admin\model\channel\Pool::where('user_id', $this->auth->id);
        if ($payType !== null && $payType !== '') {
            $model = $model->where('pay_type', $payType);
        }
        $list = $model->order('weigh', 'desc')
            ->order('id', 'desc')
            ->paginate($limit);

        $this->success('ok', $list);
    }
    public function getpool(): void
    {
        $id = (int) $this->request->param('id', 0);
        if ($id <= 0) {
            $this->error('ID 错误');
        }
        $pool = \app\admin\model\channel\Pool::where('user_id', $this->auth->id)
            ->where('id', $id)
            ->find();
        if (!$pool) {
            $this->error('轮询池不存在');
        }
        $lines = \app\admin\model\channel\PoolAccount::where('pool_id', $pool->id)
            ->with(['account'])
            ->order('weigh', 'desc')
            ->order('id', 'asc')
            ->select();
        $this->success('ok', [
            'pool'  => $pool,
            'lines' => $lines,
        ]);
    }
    public function add(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误');
        }
        $data = $this->request->post(['name', 'pay_type', 'polling_mode']);
        $user = \app\admin\model\User::find($this->auth->id);
        if (!$user) {
            $this->error('用户不存在');
        }
        $service = new \app\common\service\ChannelPoolService();
        try {
            $pool = $service->createPool($user, $data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('新增成功', $pool);
    }
    public function edit(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误');
        }
        $id   = (int) $this->request->param('id', 0);
        $data = $this->request->post(['name', 'pay_type', 'polling_mode']);
        if ($id <= 0) {
            $this->error('ID 错误');
        }
        $user = \app\admin\model\User::find($this->auth->id);
        if (!$user) {
            $this->error('用户不存在');
        }
        $service = new \app\common\service\ChannelPoolService();
        try {
            $pool = $service->updatePool($user, $id, $data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('更新成功', $pool);
    }
    public function del(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误');
        }
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $this->error('ID 为空');
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $user = \app\admin\model\User::find($this->auth->id);
        if (!$user) {
            $this->error('用户不存在');
        }
        $service   = new \app\common\service\ChannelPoolService();
        $ok        = 0;
        $lastError = '';
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }
            try {
                $service->deletePool($user, $id);
                $ok++;
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }
        if ($ok <= 0) {
            $this->error('删除失败：' . ($lastError ?: '无有效 ID'));
        }
        $this->success('已删除 ' . $ok . ' 条', ['deleted' => $ok]);
    }
    public function bindaccount(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误');
        }
        $poolId = (int) $this->request->param('pool_id', 0);
        if ($poolId <= 0) {
            $this->error('轮询池ID 错误');
        }
        $accountIds = $this->request->param('account_ids', []);
        if (is_string($accountIds)) {
            if ($accountIds === '') {
                $accountIds = [];
            } elseif (str_contains($accountIds, ',')) {
                $accountIds = array_map('intval', explode(',', $accountIds));
            } else {
                $accountIds = [(int) $accountIds];
            }
        } elseif (is_array($accountIds)) {
            $accountIds = array_map('intval', $accountIds);
        } else {
            $accountIds = [];
        }
        $service = new \app\common\service\ChannelPoolService();
        try {
            $pool = $service->findUserPool((int) $this->auth->id, $poolId);
            if (!$pool) {
                $this->error('轮询池不存在');
            }
            $count = $service->bindAccounts($pool, $accountIds);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('已绑定 ' . $count . ' 条', ['count' => $count]);
    }
    public function unbindaccount(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误');
        }
        $poolId = (int) $this->request->param('pool_id', 0);
        if ($poolId <= 0) {
            $this->error('pool_id 错误');
        }
        $poolAccountIds = $this->request->param('pool_account_ids', []);
        if (is_string($poolAccountIds)) {
            if ($poolAccountIds === '') {
                $poolAccountIds = [];
            } elseif (str_contains($poolAccountIds, ',')) {
                $poolAccountIds = array_map('intval', explode(',', $poolAccountIds));
            } else {
                $poolAccountIds = [(int) $poolAccountIds];
            }
        } elseif (is_array($poolAccountIds)) {
            $poolAccountIds = array_map('intval', $poolAccountIds);
        } else {
            $poolAccountIds = [];
        }
        if (count($poolAccountIds) === 0) {
            $this->error('请指定要移除的池内记录');
        }
        $service = new \app\common\service\ChannelPoolService();
        try {
            $pool = $service->findUserPool((int) $this->auth->id, $poolId);
            if (!$pool) {
                $this->error('轮询池不存在');
            }
            $deleted = $service->unbindByIds($pool, $poolAccountIds);
            if ($deleted <= 0) {
                $this->error('未删除任何记录');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('已移除 ' . $deleted . ' 条', ['deleted' => $deleted]);
    }
    public function setaccountweigh(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方法错误');
        }
        $poolId = (int) $this->request->param('pool_id', 0);
        $id     = (int) $this->request->param('id', 0);
        $weigh  = (int) $this->request->param('weigh', 0);
        if ($poolId <= 0 || $id <= 0) {
            $this->error('参数错误');
        }
        $service = new \app\common\service\ChannelPoolService();
        try {
            $pool = $service->findUserPool($this->auth->id, $poolId);
            if (!$pool) {
                $this->error('轮询池不存在');
            }
            $service->setPoolAccountWeigh($pool, $id, $weigh);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('已更新');
    }
}
