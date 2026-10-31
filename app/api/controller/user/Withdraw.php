<?php

// 还原自 Swoole Compiler 加密文件: Withdraw.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/user/Withdraw.php
//
// [2026-09-21 opcode 全量对照还原] 依据 dump_m.py 指令序重建全部方法：
//   info:      钱包概览（getwallet + getstatistics → withdrawal/freeze/total/month/pending 五项）
//   list:      提现记录分页（post filter + limit → withdrawService->getlist）
//   apply:     发起提现（isPost→金额空/格式校验→verify_type+captcha 双因素（验证码键
//              = $user['verify_type'].'user_{type}_verify'）→ service->apply→成功回执四字段）
//   reapply:   驳回重提（isPost→id 校验→service->reapply）
//   statusdict:状态字典（遍历 WithdrawService::$statusLabels → [{value,label}]）

namespace app\api\controller\user;

class Withdraw extends \app\common\controller\Frontend {
    private \app\common\service\WithdrawService $withdrawService;
    private \app\common\service\WalletService $walletService;
    public function initialize(): void
    {
        parent::initialize();
        $this->withdrawService = new \app\common\service\WithdrawService();
        $this->walletService = new \app\common\service\WalletService();
    }

    public function info(): void
    {
        try {
            $wallet = $this->walletService->getwallet($this->auth->id);
            $stats  = $this->withdrawService->getstatistics($this->auth->id);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', [
            'withdrawal_money'   => $wallet->withdrawal_money,
            'freeze_money'       => $wallet->freeze_money,
            'total_withdrawal'   => $stats['total_withdrawal'],
            'month_withdrawal'   => $stats['month_withdrawal'],
            'pending_withdrawal' => $stats['pending_withdrawal'],
        ]);
    }

    public function list(): void
    {
        $filter = $this->request->post('filter/a', []);
        $limit  = (int) $this->request->post('limit', 15);
        try {
            $list = $this->withdrawService->getlist($this->auth->id, $filter, $limit);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', $list);
    }

    public function apply(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $params = $this->request->post();
        if (empty($params['money'])) {
            $this->error('提现金额不能为空');
        }
        if (!is_numeric($params['money']) || bccomp((string) $params['money'], '0', 2) <= 0) {
            $this->error('提现金额格式错误');
        }
        if (empty($params['verify_type']) || empty($params['captcha'])) {
            $this->error('验证参数不完整');
        }
        $user    = $this->auth->getUser();
        $captcha = new \ba\Captcha();
        $key     = $user['verify_type'] . 'user_' . $params['verify_type'] . '_verify';
        if (!$captcha->check($params['captcha'], $key)) {
            throw new \Exception('验证码错误');
        }
        try {
            $log = $this->withdrawService->apply($this->auth->id, (string) $params['money']);
            $log = \app\admin\model\user\WithdrawalApplyLog::find($log->id); // 重取：走 onafterread 分→元
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('提现申请成功', [
            'withdraw_no'  => $log->withdraw_no,
            'apply_amount' => $log->apply_amount,
            'final_amount' => $log->final_amount,
            'status'       => $log->status,
        ]);
    }

    public function reapply(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $id = (int) $this->request->post('id');
        if ($id <= 0) {
            $this->error('提现记录ID错误');
        }
        try {
            $this->withdrawService->reapply($id, $this->auth->id);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('重新申请成功');
    }

    public function statusdict(): void
    {
        $list = [];
        foreach (\app\common\service\WithdrawService::$statusLabels as $value => $label) {
            $list[] = ['value' => $value, 'label' => $label];
        }
        $this->success('ok', $list);
    }

}
