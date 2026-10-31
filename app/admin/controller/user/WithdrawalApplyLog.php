<?php

// 还原自 Swoole Compiler 加密文件: WithdrawalApplyLog.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/WithdrawalApplyLog.php

namespace app\admin\controller\user;

class WithdrawalApplyLog extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null];
    protected array $withJoinTable = ['user'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\user\WithdrawalApplyLog();
    }

    public function getpayaccount(): void
    {
        // [opcode 对照] 原方法 line 74-104，字面量真值：param(id,0)/参数错误/WithdrawalApplyLog::find/提现记录不存在/
        // WalletService::getWallet(user_id)/pay_account(?:[])/支付宝·微信标签映射/getMessage/error/success('ok')
        $id = (int)($this->request->param('id', 0));
        if ($id <= 0) {
            $this->error('参数错误');
        }
        $log = \app\admin\model\user\WithdrawalApplyLog::find($id);
        if (!$log) {
            throw new \Exception('提现记录不存在');
        }
        try {
            $walletService = new \app\common\service\WalletService();
            $wallet        = $walletService->getwallet($log->user_id);
            $payAccount    = $wallet->pay_account ?: [];
            $accountTypeMap = ['alipay' => '支付宝'];
            $qrcodeTypeMap  = ['wxpay' => '微信', 'alipay' => '支付宝'];
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('ok', [
            'pay_account' => [
                'account_type'       => $payAccount['account_type'] ?? '',
                'account_type_label' => $accountTypeMap[$payAccount['account_type'] ?? ''] ?? '',
                'account'            => $payAccount['account'] ?? '',
                'qrcode_type'        => $payAccount['qrcode_type'] ?? '',
                'qrcode_type_label'  => $qrcodeTypeMap[$payAccount['qrcode_type'] ?? ''] ?? '',
                'qrcode'             => $payAccount['qrcode'] ?? '',
            ],
            'apply_amount' => $log->apply_amount,
            'final_amount' => $log->final_amount,
            'fee'          => $log->fee,
            'user_id'      => $log->user_id,
        ]);
    }

    public function approve(): void
    {
        // [opcode 对照] 原方法 line 112-123，字面量真值：post(id,0)/post(remark,'')/参数错误/WithdrawService->approve(id,remark)/getMessage/审核通过
        $id     = (int)($this->request->post('id', 0));
        $remark = (string)($this->request->post('remark', ''));
        if ($id <= 0) {
            $this->error('参数错误');
        }
        try {
            $service = new \app\common\service\WithdrawService();
            $service->approve($id, $remark);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('审核通过');
    }

    public function reject(): void
    {
        // [opcode 对照] 原方法 line 131-143，字面量真值：post(id,0)/post(reject_msg,'')/参数错误/驳回原因不能为空/WithdrawService->reject(id,reject_msg)/getMessage/已驳回
        $id        = (int)($this->request->post('id', 0));
        $rejectMsg = (string)($this->request->post('reject_msg', ''));
        if ($id <= 0) {
            $this->error('参数错误');
        }
        if (empty($rejectMsg)) {
            $this->error('驳回原因不能为空');
        }
        try {
            $service = new \app\common\service\WithdrawService();
            $service->reject($id, $rejectMsg);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('已驳回');
    }

    public function paid(): void
    {
        // [opcode 对照] 原方法 line 151-162，字面量真值：post(id,0)/post(remark,'')/参数错误/WithdrawService->paid(id,remark)/getMessage/打款完成
        $id     = (int)($this->request->post('id', 0));
        $remark = (string)($this->request->post('remark', ''));
        if ($id <= 0) {
            $this->error('参数错误');
        }
        try {
            $service = new \app\common\service\WithdrawService();
            $service->paid($id, $remark);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('打款完成');
    }

}
