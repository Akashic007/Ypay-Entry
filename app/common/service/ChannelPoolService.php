<?php

// 还原自 Swoole Compiler 加密文件: ChannelPoolService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/ChannelPoolService.php

namespace app\common\service;

class ChannelPoolService{
    // [opcode 对照] 常量值取自 payload 常量表解引用：packed 数组 3 元素，中间元素 zval 指针被抹零，
    // 但常量字符串区第三个 interned 串 'weigh_priority'(len=14) 紧邻 random/weigh_polling 且全文件无其它引用，据此还原
    const POLLING_MODES = ['random', 'weigh_priority', 'weigh_polling'];
    public function resolveaccountsforpay(\app\admin\model\Order|null $order = null): array
    {
        $userId  = (int) $order->user_id;
        $payType = $order->type; // [opcode 对照] 受保护 FETCH_OBJ 字面量 4 字符 'type'，对应 as_order.type 列（getpaytypeattr 同源），非 pay_type
        $pools = \app\admin\model\channel\Pool::where('user_id', $userId)
            ->where('pay_type', $payType)
            ->where('status', 1)
            ->order('weigh', 'desc')
            ->order('id', 'asc')
            ->select();
        $result = [];
        foreach ($pools as $pool) {
            $rows = $this->getenabledaccountrowsinpool($pool);
            if (count($rows) > 0) {
                $result[] = ['pool' => $pool, 'accounts' => $rows];
            }
        }
        if (count($result) > 0) {
            $chosen = $result[array_rand($result)];
            $mode   = $chosen['pool']->polling_mode;
            if (!in_array($mode, self::POLLING_MODES, true)) {
                $mode = 'weigh_polling';
            }
            return [
                'accounts'  => $chosen['accounts'],
                'mode'      => $mode,
                'from_pool' => true,
                'pool_id'   => (int) $chosen['pool']->id,
            ];
        }
        $accounts = \app\admin\model\channel\Account::where('user_id', $userId)
            ->where('pay_type', $payType)
            ->where('status', 1)
            ->where('online_status', 1)
            ->where('source_type', '<>', 'down')
            ->select()
            ->toArray();
        $callMode = $order->userConfig->channel_config['call_mode'] ?? 'weigh_polling';
        $mode     = $this->normalizeusercallmode($callMode);
        return [
            'accounts'  => $accounts,
            'mode'      => $mode,
            'from_pool' => false,
        ];
    }

    private function normalizeusercallmode(mixed $mode = null): string
    {
        if (is_string($mode) && in_array($mode, self::POLLING_MODES, true)) {
            return $mode;
        }
        return 'weigh_polling';
    }

    public function getenabledaccountrowsinpool(\app\admin\model\channel\Pool|null $pool = null): array
    {
        $rows = \app\admin\model\channel\PoolAccount::where('pool_id', $pool->id)->with(['account'])->order('weigh', 'desc')->order('id', 'asc')->select();
        $return = [];
        foreach ($rows as $row) {
            $account = $row['account'];
            if (!$account) {
                continue;
            }
            if ((int) $account['user_id'] != (int) $pool->user_id) {
                continue;
            }
            if ($account['pay_type'] !== $pool->pay_type) {
                continue;
            }
            if ((int) $account['status'] !== 1) {
                continue;
            }
            if ((int) $account['online_status'] !== 1) {
                continue;
            }
            if ($account['source_type'] === 'down') {
                continue;
            }
            $item           = $account->toArray();
            $item['weigh']  = (int) $row['weigh'];
            $return[]       = $item;
        }
        return $return;
    }

    public function finduserpool(int|null $userId = null, int|null $poolId = null): ?\app\admin\model\channel\Pool
    {
        $pool = \app\admin\model\channel\Pool::where('user_id', $userId)->where('id', $poolId)->find();
        return $pool ?: null; // [opcode 对照] JMP_SET + null 字面量 → find() ?: null，VERIFY_RETURN_TYPE 前可返 null，原返回类型为可空
    }

    public function createpool(\app\admin\model\User|null $user = null, array|null $data = null): \app\admin\model\channel\Pool
    {
        $this->assertpoolinput($data, true);
        $pool            = new \app\admin\model\channel\Pool();
        $pool->user_id   = $user->id;
        $pool->name      = (string) $data['name'];
        $pool->pay_type  = (string) $data['pay_type'];
        $pool->polling_mode = (string) $data['polling_mode'];
        $pool->status    = isset($data['status']) ? (int) $data['status'] : 1;
        $pool->remark    = (string) ($data['remark'] ?? '');
        $pool->weigh     = (isset($data['weigh']) && $data['weigh'] !== '') ? (int) $data['weigh'] : 0;
        $pool->save();
        return $pool;
    }

    public function updatepool(\app\admin\model\User|null $user = null, int|null $id = null, array|null $data = null): \app\admin\model\channel\Pool
    {
        $pool = \app\admin\model\channel\Pool::where('user_id', $user->id)->where('id', $id)->find();
        if (!$pool) {
            throw new \Exception('轮询池不存在');
        }
        if (array_key_exists('name', $data) && (string) $data['name'] === '') {
            throw new \Exception('池名称不能为空');
        }
        if (array_key_exists('name', $data)) {
            $pool->name = (string) $data['name'];
        }
        if (array_key_exists('pay_type', $data)) {
            if ((string) $data['pay_type'] === '' || $data['pay_type'] === false) {
                throw new \Exception('支付类型不能为空');
            }
            $this->assertpaytype((string) $data['pay_type']);
            $pool->pay_type = (string) $data['pay_type'];
        }
        if (array_key_exists('polling_mode', $data)) {
            $this->assertpollingmode((string) $data['polling_mode']);
            $pool->polling_mode = (string) $data['polling_mode'];
        }
        if (array_key_exists('status', $data)) {
            $pool->status = (int) $data['status'];
        }
        if (array_key_exists('remark', $data)) {
            $pool->remark = (string) $data['remark'];
        }
        if (array_key_exists('weigh', $data) && $data['weigh'] !== '' && $data['weigh'] !== false) {
            // [opcode 对照] TYPE_CHECK ext=1020 无法映射到 PHP 类型，按本方法 pay_type 分支同款 falsy 哨兵 '!== false' 保守还原
            $pool->weigh = (int) $data['weigh'];
        }
        $pool->save();
        return $pool;
    }

    public function deletepool(\app\admin\model\User|null $user = null, int|null $id = null): void
    {
        $pool = \app\admin\model\channel\Pool::where('user_id', $user->id)->where('id', $id)->find();
        if (!$pool) {
            throw new \Exception('轮询池不存在');
        }
        \think\facade\Db::startTrans();
        try {
            \app\admin\model\channel\PoolAccount::where('pool_id', $pool->id)->delete();
            $pool->delete();
            \think\facade\Db::commit();
        } catch (\Throwable $e) {
            \think\facade\Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function bindaccounts(\app\admin\model\channel\Pool|null $pool = null, array|null $accountIds = null): int
    {
        if (count($accountIds) === 0) {
            throw new \Exception('请选择通道');
        }
        $count = 0;
        foreach ($accountIds as $accountId) {
            $accountId = (int) $accountId;
            if ($accountId <= 0) {
                continue;
            }
            $account = \app\admin\model\channel\Account::where('id', $accountId)
                ->where('user_id', $pool->user_id)
                ->where('pay_type', $pool->pay_type)
                ->where('source_type', '<>', 'down')
                ->find();
            if (!$account) {
                throw new \Exception('通道不可绑定：ID ' . $accountId . ' 不存在、支付方式不符或为下游通道');
            }
            $exists = \app\admin\model\channel\PoolAccount::where('account_id', $accountId)
                ->where('pool_id', '<>', $pool->id)
                ->find();
            if ($exists) {
                throw new \Exception('通道 ID ' . $accountId . ' 已加入其他轮询池，不能重复入池');
            }
            $has = \app\admin\model\channel\PoolAccount::where('pool_id', $pool->id)
                ->where('account_id', $accountId)
                ->find();
            if ($has) { // [opcode 对照] 已在本池则跳过该通道（JMPNZ 回跳 FE_FETCH）
                continue;
            }
            $poolAccount            = new \app\admin\model\channel\PoolAccount();
            $poolAccount->pool_id   = $pool->id;
            $poolAccount->account_id = $accountId;
            $poolAccount->weigh     = 0;
            $poolAccount->save();
            ++$count;
        }
        if ($count === 0) {
            throw new \Exception('没有新增绑定（可能已存在或通道无效）');
        }
        return $count;
    }

    public function unbindbyids(\app\admin\model\channel\Pool|null $pool = null, array|null $poolAccountIds = null): int
    {
        if (count($poolAccountIds) === 0) {
            return 0;
        }
        $poolAccountIds = array_map('intval', $poolAccountIds);
        $poolAccountIds = array_filter($poolAccountIds, function ($id) { // [opcode 对照] 内联闭包指令未导出，按 intval 后滤正整数保守还原
            return $id > 0;
        });
        if (count($poolAccountIds) === 0) {
            return 0;
        }
        return (int) \app\admin\model\channel\PoolAccount::where('pool_id', $pool->id)
            ->where('id', 'in', $poolAccountIds)
            ->delete();
    }

    public function setpoolaccountweigh(\app\admin\model\channel\Pool|null $pool = null, int|null $poolAccountId = null, int|null $weigh = null): void
    {
        $poolAccount = \app\admin\model\channel\PoolAccount::where('pool_id', $pool->id)
            ->where('id', $poolAccountId)
            ->find();
        if (!$poolAccount) {
            throw new \Exception('池内记录不存在');
        }
        $poolAccount->weigh = $weigh;
        $poolAccount->save();
    }

    private function assertpoolinput(array|null $data = null, bool|null $isCreate = null): void
    {
        if ($isCreate) {
            if (empty($data['name'])) {
                throw new \Exception('池名称为空');
            }
            if (empty($data['pay_type'])) {
                throw new \Exception('支付类型为空');
            }
            $this->assertpaytype((string) $data['pay_type']);
            if (empty($data['polling_mode'])) {
                throw new \Exception('轮询模式为空');
            }
            $this->assertpollingmode((string) $data['polling_mode']);
            return;
        }
        if (isset($data['polling_mode'])) {
            $this->assertpollingmode((string) $data['polling_mode']);
        }
        if (isset($data['pay_type']) && $data['pay_type'] !== '') {
            $this->assertpaytype((string) $data['pay_type']);
        }
    }

    private function assertpaytype(string|null $code = null): void
    {
        $id = \app\admin\model\channel\PayType::where('code', $code)->where('status', 1)->value('id');
        if (empty($id)) {
            throw new \Exception('无效的支付类型');
        }
    }

    public function assertpollingmode(string|null $mode = null): void
    {
        if (!in_array($mode, self::POLLING_MODES, true)) {
            throw new \Exception('轮询模式仅支持：' . implode('、', self::POLLING_MODES));
        }
    }

}
