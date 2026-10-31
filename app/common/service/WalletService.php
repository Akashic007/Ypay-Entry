<?php

// 还原自 Swoole Compiler 加密文件: WalletService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/WalletService.php

namespace app\common\service;

use app\admin\model\user\Wallet;
use app\admin\model\user\WalletMoneyLog;
use think\facade\Db;

class WalletService{
    // 常量为 opcode 常量表解引用真值（2026-09-21 服务器端工具链还原）
    // 标签数组按解密字符串池语义映射（展示用途），如需精确请对 AST 做对照
    public static array $typeLabels = [
        self::TYPE_REBATE   => '代理返佣',
        self::TYPE_FREEZE   => '冻结',
        self::TYPE_UNFREEZE => '解冻',
        self::TYPE_WITHDRAW => '提现完成',
        self::TYPE_TRANSFER => '余额转换',
        self::TYPE_ADJUST   => '人工调整',
    ];
    public static array $sourceLabels = [
        self::SOURCE_AGENT_REBATE => '代理返佣',
        self::SOURCE_SITE_REBATE  => '分站返佣',
        self::SOURCE_WITHDRAW     => '提现',
        self::SOURCE_TRANSFER     => '转入消费余额',
        self::SOURCE_ADJUST       => '人工调整',
    ];
    const TYPE_REBATE = 3;
    const TYPE_FREEZE = 4;
    const TYPE_UNFREEZE = 5;
    const TYPE_WITHDRAW = 6;
    const TYPE_TRANSFER = 7;
    const TYPE_ADJUST = 8;
    const SOURCE_AGENT_REBATE = 'agent_rebate';
    const SOURCE_SITE_REBATE = 'site_rebate';
    const SOURCE_WITHDRAW = 'withdraw';
    const SOURCE_TRANSFER = 'transfer';
    const SOURCE_ADJUST = 'adjust';
    public function income(int|null $userId = null, string|null $amount = null, string|null $remark = null, array|null $content = null, string|null $source = null): void
    {
        if (bccomp((string) $amount, '0', 2) <= 0) {
            throw new \Exception('金额必须大于0');
        }
        Db::startTrans();
        try {
            $wallet = $this->getorcreatewallet($userId, true);
            $before = $wallet->snapshot();
            $wallet->withdrawal_money = bcadd((string) $wallet->withdrawal_money, (string) $amount, 2);
            $wallet->total_income     = bcadd((string) $wallet->total_income, (string) $amount, 2);
            $wallet->money            = $this->calcmoney($wallet);
            $after = $wallet->snapshot();
            $wallet->save();
            $this->writelog($userId, $amount, self::TYPE_REBATE, $remark, $content, $before, $after, $source);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    // [opcode 还原] 冻结：可提现→冻结，快照落 WalletMoneyLog(TYPE_FREEZE)
    public function freeze(int|null $userId = null, string|null $amount = null, string|null $remark = null, array|null $content = null, string|null $source = null): void
    {
        if (bccomp((string) $amount, '0', 2) <= 0) {
            throw new \Exception('金额必须大于0');
        }
        $wallet = $this->getorcreatewallet($userId, true);
        if (bccomp((string) $wallet->withdrawal_money, (string) $amount, 2) < 0) {
            throw new \Exception('可提现余额不足，当前：' . $wallet->withdrawal_money . ' 元');
        }
        Db::startTrans();
        try {
            $before = $wallet->snapshot();
            $wallet->withdrawal_money = bcsub((string) $wallet->withdrawal_money, (string) $amount, 2);
            $wallet->freeze_money     = bcadd((string) $wallet->freeze_money, (string) $amount, 2);
            $after = $wallet->snapshot();
            $wallet->save();
            $this->writelog($userId, (string) $amount, self::TYPE_FREEZE, $remark, $content ?: [], $before, $after, $source ?: self::SOURCE_WITHDRAW);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    // [opcode 还原] 解冻：冻结→可提现，快照落 WalletMoneyLog(TYPE_UNFREEZE)
    public function unfreeze(int|null $userId = null, string|null $amount = null, string|null $remark = null, array|null $content = null, string|null $source = null): void
    {
        if (bccomp((string) $amount, '0', 2) <= 0) {
            throw new \Exception('金额必须大于0');
        }
        $wallet = $this->getorcreatewallet($userId, true);
        if (bccomp((string) $wallet->freeze_money, (string) $amount, 2) < 0) {
            throw new \Exception('冻结余额不足，当前冻结：' . $wallet->freeze_money . ' 元');
        }
        Db::startTrans();
        try {
            $before = $wallet->snapshot();
            $wallet->freeze_money     = bcsub((string) $wallet->freeze_money, (string) $amount, 2);
            $wallet->withdrawal_money = bcadd((string) $wallet->withdrawal_money, (string) $amount, 2);
            $after = $wallet->snapshot();
            $wallet->save();
            $this->writelog($userId, (string) $amount, self::TYPE_UNFREEZE, $remark, $content ?: [], $before, $after, $source ?: self::SOURCE_WITHDRAW);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    // [opcode 还原] 提现打款：冻结扣除+累计提现，快照落 WalletMoneyLog(TYPE_WITHDRAW)
    public function withdrawdeduct(int|null $userId = null, string|null $amount = null, string|null $remark = null, array|null $content = null, string|null $source = null): void
    {
        if (bccomp((string) $amount, '0', 2) <= 0) {
            throw new \Exception('金额必须大于0');
        }
        $wallet = $this->getorcreatewallet($userId, true);
        if (bccomp((string) $wallet->freeze_money, (string) $amount, 2) < 0) {
            throw new \Exception('冻结余额不足，当前冻结：' . $wallet->freeze_money . ' 元');
        }
        Db::startTrans();
        try {
            $before = $wallet->snapshot();
            $wallet->freeze_money     = bcsub((string) $wallet->freeze_money, (string) $amount, 2);
            $wallet->total_withdrawal = bcadd((string) $wallet->total_withdrawal, (string) $amount, 2);
            // [opcode 真值确认 2026-09-22 B57 批] [62]-[66] 的赋值即 $this->calcMoney($wallet)
            $wallet->money = $this->calcmoney($wallet);
            $after = $wallet->snapshot();
            $wallet->save();
            $this->writelog($userId, (string) $amount, self::TYPE_WITHDRAW, $remark, $content ?: [], $before, $after, $source ?: self::SOURCE_WITHDRAW);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function transfertouserbalance(int|null $userId = null, string|null $amount = null): void
    {
        if (bccomp((string) $amount, '0', 2) <= 0) {
            throw new \Exception('转换金额必须大于0');
        }
        Db::startTrans();
        try {
            $wallet = $this->getorcreatewallet($userId, true);
            if (bccomp((string) $wallet->withdrawal_money, (string) $amount, 2) < 0) {
                throw new \Exception('可提现余额不足，当前：' . $wallet->withdrawal_money . ' 元');
            }
            $before = $wallet->snapshot();
            $wallet->withdrawal_money = bcsub((string) $wallet->withdrawal_money, (string) $amount, 2);
            $wallet->money            = $this->calcmoney($wallet);
            $after = $wallet->snapshot();
            $wallet->save();
            $this->writelog($userId, '-' . $amount, self::TYPE_TRANSFER, '转入消费余额[' . $amount . ']元', [], $before, $after, self::SOURCE_TRANSFER);
            \app\admin\model\UserMoneyLog::create([
                'user_id' => $userId,
                'money'   => $amount,
                // [opcode 对照] FETCH_CLASS_CONSTANT UserMoneyLog::TYPE_WALLET_TRANSFER；
                // 原加密常量表真值 = 12，部署版 UserMoneyLog.php 未含常量定义（清单外不可改），故按解引用真值写入
                'type'    => 12,
                'memo'    => '钱包转入消费余额[' . $amount . ']元',
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function adjust(int|null $userId = null, string|null $amount = null, string|null $remark = null, array|null $content = null): void
    {
        if (bccomp((string) $amount, '0', 2) === 0) {
            throw new \Exception('调整金额不能为0');
        }
        Db::startTrans();
        try {
            $wallet = $this->getorcreatewallet($userId, true);
            $newWithdrawal = bcadd((string) $wallet->withdrawal_money, (string) $amount, 2);
            if (bccomp((string) $newWithdrawal, '0', 2) < 0) {
                throw new \Exception('调整后可提现余额不能为负数');
            }
            $before = $wallet->snapshot();
            $wallet->withdrawal_money = $newWithdrawal;
            if (bccomp((string) $amount, '0', 2) > 0) {
                $wallet->total_income = bcadd((string) $wallet->total_income, (string) $amount, 2);
            }
            $wallet->money = $this->calcmoney($wallet);
            $after = $wallet->snapshot();
            $wallet->save();
            $this->writelog($userId, $amount, self::TYPE_ADJUST, $remark, $content, $before, $after, self::SOURCE_ADJUST);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    // [opcode 还原] 委托 getorcreatewallet
    public function getwallet(int|null $userId = null): \app\admin\model\user\Wallet
    {
        return $this->getorcreatewallet($userId);
    }

    public function setpayaccount(int|null $userId = null, array|null $payAccount = null): void
    {
        $wallet = $this->getorcreatewallet($userId);
        Wallet::where('user_id', $userId)->update([
            'pay_account' => json_encode(array_merge($wallet->pay_account ?? [], $payAccount), JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function getmoneylog(int|null $userId = null, array|null $filter = null, int|null $limit = null)
    {
        $query = WalletMoneyLog::where('user_id', $userId);
        if (!empty($filter['type'])) {
            $query->where('type', (int) $filter['type']);
        }
        if (!empty($filter['source'])) {
            $query->where('source', $filter['source']);
        }
        if (!empty($filter['start_date'])) {
            $query->whereTime('create_time', '>=', $filter['start_date']);
        }
        if (!empty($filter['end_date'])) {
            $query->whereTime('create_time', '<=', $filter['end_date']);
        }
        $query->order('id', 'desc');
        return $query->paginate($limit ?? 15); // [opcode 对照] RECV_INIT 默认 15
    }

    public function getStatistics(int|null $userId = null): array
    {
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd   = date('Y-m-t 23:59:59');
        $wallet     = $this->getorcreatewallet($userId);

        $query = WalletMoneyLog::where('user_id', $userId)
            ->where('type', self::TYPE_REBATE)
            ->whereTime('create_time', 'between', [$monthStart, $monthEnd]);
        $monthIncome = $query->sum('money');

        $query = WalletMoneyLog::where('user_id', $userId)
            ->where('type', self::TYPE_WITHDRAW)
            ->whereTime('create_time', 'between', [$monthStart, $monthEnd]);
        $monthWithdrawal = $query->sum('money');

        return [
            'money'            => $wallet->money,
            'withdrawal_money' => $wallet->withdrawal_money,
            'freeze_money'     => $wallet->freeze_money,
            'total_income'     => $wallet->total_income,
            'total_withdrawal' => $wallet->total_withdrawal,
            'month_income'     => abs(fenToYuan($monthIncome) ?? 0),
            'month_withdrawal' => abs(fenToYuan($monthWithdrawal) ?? 0),
        ];
    }
    // [opcode 还原] where(user_id)[->lock(true)]->find()，无则 create 全零钱包
    public function getorcreatewallet(int|null $userId = null, bool|null $lock = null): \app\admin\model\user\Wallet
    {
        $query = Wallet::where('user_id', $userId);
        if ($lock) {
            $query->lock(true);
        }
        $wallet = $query->find();
        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id'          => $userId,
                'money'            => 0,
                'withdrawal_money' => 0,
                'freeze_money'     => 0,
                'total_income'     => 0,
                'total_withdrawal' => 0,
            ]);
        }
        return $wallet;
    }

    private function snapshot(\app\admin\model\user\Wallet|null $wallet = null): array
    {
        return [
            'money'            => $wallet->money,
            'withdrawal_money' => $wallet->withdrawal_money,
            'freeze_money'     => $wallet->freeze_money,
        ];
    }

    private function calcmoney(\app\admin\model\user\Wallet|null $wallet = null): string
    {
        return bcadd((string) $wallet->withdrawal_money, (string) $wallet->freeze_money, 2);
    }

    private function writelog(int|null $userId = null, string|null $amount = null, int|null $type = null, string|null $remark = null, array|null $content = null, array|null $before = null, array|null $after = null, string|null $source = null): void
    {
        WalletMoneyLog::create([
            'user_id'                 => $userId,
            'money'                   => $amount,
            'before_money'            => $before['money'],
            'before_withdrawal_money' => $before['withdrawal_money'],
            'before_freeze_money'     => $before['freeze_money'],
            'after_money'             => $after['money'],
            'after_withdrawal_money'  => $after['withdrawal_money'],
            'after_freeze_money'      => $after['freeze_money'],
            'type'                    => $type,
            // [opcode 对照] RECV_INIT source 默认 ''；部署签名默认为 null，兜底避免 NOT NULL 列写 null
            'source'                  => $source ?? '',
            'remark'                  => $remark,
            'content'                 => $content,
        ]);
    }

}
