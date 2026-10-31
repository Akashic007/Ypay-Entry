<?php

// 还原自 Swoole Compiler 加密文件: WithdrawService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/WithdrawService.php
//
// [2026-09-21 opcode 全量对照还原] 依据本地工具链 dump_m.py 逐方法对照指令序重建：
//   - 类常量 STATUS_*：解密层常量表 zval 解引用真值（PENDING=0/APPROVED=1/REJECTED=2/PAID=3）
//   - $statusLabels：字符串池真值（待审核/审核通过/已驳回/打款完成），与方法内报错文本互证
//   - apply: bccomp 校验→钱包可提现校验→费率钳制 max(0.0,min(1.0))→bcmul/bcsub 手续费
//     →OrderService::generateOrderNo→事务(freeze+创建 PENDING 单)
//   - approve: findPendingOrFail→status=APPROVED→条件写备注→save
//   - reject: 驳回原因必填→find→状态须 PENDING/APPROVED→事务(unfreeze+REJECTED+reject_msg)
//   - paid: find→状态须 APPROVED→事务(withdrawdeduct+PAID)
//   - reapply: 归属校验→状态须 REJECTED→余额校验→事务(freeze+PENDING+清空 reject_msg)
//   - getList: order(id desc)+user_id 条件+status/start_date/end_date 过滤+limit
//   - getStatistics: total/month(本月 between)/pending(含待审+已审) 三项金额合计(分→元)

namespace app\common\service;

use app\admin\model\user\WithdrawalApplyLog;
use app\common\service\sys\OrderService;
use think\facade\Db;

class WithdrawService{
    public static array $statusLabels = [
        self::STATUS_PENDING  => '待审核',
        self::STATUS_APPROVED => '审核通过',
        self::STATUS_REJECTED => '已驳回',
        self::STATUS_PAID     => '打款完成',
    ];
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_PAID = 3;

    public function apply(int|null $userId = null, string|null $money = null, float|null $feeRate = null, array|null $extra = null): \app\admin\model\user\WithdrawalApplyLog
    {
        if (bccomp($money, '0', 2) <= 0) {
            throw new \Exception('提现金额必须大于0');
        }
        $walletService = new WalletService();
        $wallet        = $walletService->getorcreatewallet($userId, true);
        if ($wallet->withdrawal_money < $money) {
            throw new \Exception('可提现余额不足，当前可提现：' . $wallet->withdrawal_money . ' 元');
        }
        $feeRate     = max(0.0, min(1.0, $feeRate));
        $fee         = bcmul($money, (string) $feeRate, 2);
        $finalAmount = bcsub($money, $fee, 2);
        if (bccomp($finalAmount, '0', 2) <= 0) {
            throw new \Exception('扣除手续费后金额为0，无法提现');
        }
        $withdrawNo = OrderService::generateorderno();
        if (!$withdrawNo) {
            throw new \Exception('提现单号生成失败，请重试');
        }
        Db::startTrans();
        try {
            $walletService->freeze($userId, $money, '申请提现冻结[' . $money . ']元，单号：' . $withdrawNo, ['withdraw_no' => $withdrawNo]);
            $log = WithdrawalApplyLog::create(array_merge([
                'user_id'       => $userId,
                'withdraw_no'   => $withdrawNo,
                'apply_amount'  => $money,
                'fee'           => $fee,
                'final_amount'  => $finalAmount,
                'status'        => self::STATUS_PENDING,
            ], (array) $extra));
            Db::commit();
            return $log;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function approve(int|null $logId = null, string|null $remark = null): void
    {
        $log = $this->findpendingorfail($logId);
        $log->status = self::STATUS_APPROVED;
        if ($remark) {
            $log->reject_msg = $remark;
        }
        $log->save();
    }

    public function reject(int|null $logId = null, string|null $rejectMsg = null): void
    {
        if (empty($rejectMsg)) {
            throw new \Exception('驳回原因不能为空');
        }
        $log = WithdrawalApplyLog::find($logId);
        if (!$log) {
            throw new \Exception('提现记录不存在');
        }
        if (!in_array($log->status, [self::STATUS_PENDING, self::STATUS_APPROVED])) {
            throw new \Exception('当前状态不可驳回');
        }
        Db::startTrans();
        try {
            $walletService = new WalletService();
            $walletService->unfreeze($log->user_id, $log->apply_amount, '提现驳回，解冻[' . $log->apply_amount . ']元，单号：' . $log->withdraw_no, ['withdraw_no' => $log->withdraw_no]);
            $log->status      = self::STATUS_REJECTED;
            $log->reject_msg  = $rejectMsg;
            $log->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function paid(int|null $logId = null, string|null $remark = null): void
    {
        $log = WithdrawalApplyLog::find($logId);
        if (!$log) {
            throw new \Exception('提现记录不存在');
        }
        if ($log->status !== self::STATUS_APPROVED) {
            throw new \Exception('只有审核通过状态才可完成打款');
        }
        Db::startTrans();
        try {
            $walletService = new WalletService();
            $walletService->withdrawdeduct($log->user_id, $log->apply_amount,
                '提现打款完成，扣除[' . $log->apply_amount . ']元，单号：' . $log->withdraw_no . ($remark ? '，备注：' . $remark : ''),
                ['withdraw_no' => $log->withdraw_no]);
            $log->status = self::STATUS_PAID;
            if ($remark) {
                $log->remark = $remark;
            }
            $log->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function reapply(int|null $logId = null, int|null $userId = null): void
    {
        $log = WithdrawalApplyLog::find($logId);
        if (!$log) {
            throw new \Exception('提现记录不存在');
        }
        if ($log->user_id != $userId) {
            throw new \Exception('无权操作此提现记录');
        }
        if ($log->status !== self::STATUS_REJECTED) {
            throw new \Exception('只有驳回状态才可重新申请');
        }
        $walletService = new WalletService();
        $wallet        = $walletService->getorcreatewallet($userId, true);
        if ($wallet->withdrawal_money < $log->apply_amount) {
            throw new \Exception('可提现余额不足，无法重新提交');
        }
        Db::startTrans();
        try {
            $walletService->freeze($userId, $log->apply_amount, '重新申请提现冻结[' . $log->apply_amount . ']元，单号：' . $log->withdraw_no, ['withdraw_no' => $log->withdraw_no]);
            $log->status     = self::STATUS_PENDING;
            $log->reject_msg = null;
            $log->save();
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function getlist(int|null $userId = null, array|null $filter = null, int|null $limit = null)
    {
        $query = WithdrawalApplyLog::order('id', 'desc');
        if ($userId != 0) {
            $query->where('user_id', $userId);
        }
        if (isset($filter['status']) && $filter['status'] !== '') {
            $query->where('status', (int) $filter['status']);
        }
        if (!empty($filter['start_date'])) {
            $query->wheretime('create_time', '>=', $filter['start_date']);
        }
        if (!empty($filter['end_date'])) {
            $query->wheretime('create_time', '<=', $filter['end_date']);
        }
        return $query->limit($limit)->select();
    }

    public function getstatistics(int|null $userId = null): array
    {
        $totalWithdrawal = WithdrawalApplyLog::where('user_id', $userId)
            ->where('status', self::STATUS_PAID)
            ->sum('final_amount');
        $monthWithdrawal = WithdrawalApplyLog::where('user_id', $userId)
            ->where('status', self::STATUS_PAID)
            ->wheretime('create_time', 'between', [date('Y-m-01'), date('Y-m-t 23:59:59')])
            ->sum('final_amount');
        $pendingWithdrawal = WithdrawalApplyLog::where('user_id', $userId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED])
            ->sum('apply_amount');
        return [
            'total_withdrawal'   => bcdiv((string) $totalWithdrawal, '100', 2),
            'month_withdrawal'   => bcdiv((string) $monthWithdrawal, '100', 2),
            'pending_withdrawal' => bcdiv((string) $pendingWithdrawal, '100', 2),
        ];
    }

    private function findpendingorfail(int|null $logId = null): \app\admin\model\user\WithdrawalApplyLog
    {
        $log = WithdrawalApplyLog::find($logId);
        if (!$log) {
            throw new \Exception('提现记录不存在');
        }
        if ($log->status !== self::STATUS_PENDING) {
            throw new \Exception('当前状态不是待审核');
        }
        return $log;
    }

}
