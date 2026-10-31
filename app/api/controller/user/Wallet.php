<?php

// 还原自 Swoole Compiler 加密文件: Wallet.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/user/Wallet.php

namespace app\api\controller\user;

class Wallet extends \app\common\controller\Frontend {
    private \app\common\service\WalletService $walletService;
    public function initialize(): void
    {
        parent::initialize();
        $this->walletService = new \app\common\service\WalletService();
    }

    public function info(): void
    {
        try {
            $info = $this->walletService->getStatistics($this->auth->id);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('ok', $info);
    }

    public function moneylog(): void
    {
        $only = request()->only(['type', 'start_date', 'end_date']);
        $limit = (int) request()->param('limit', 15);
        try {
            $list = $this->walletService->getMoneyLog($this->auth->id, $only, $limit);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('ok', $list);
    }

    public function transfer(): void
    {
        if (!request()->isPost()) {
            $this->error('请求方式错误');
        }

        $amount = request()->post('amount', '');
        if ($amount === '' || $amount === null) {
            $this->error('转换金额不能为空');
        }
        if (!is_numeric($amount) || bccomp((string) $amount, '0', 2) <= 0) {
            $this->error('转换金额格式错误');
        }
        if (round((float) $amount, 2) != (float) $amount) {
            $this->error('金额最多支持两位小数');
        }

        try {
            $this->walletService->transferToUserBalance($this->auth->id, (string) $amount);
            $wallet = $this->walletService->getWallet($this->auth->id);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('转换成功', [
            'withdrawal_money' => $wallet->withdrawal_money,
            'freeze_money'     => $wallet->freeze_money,
            'money'            => $wallet->money,
        ]);
    }

    public function typedict(): void
    {
        $dict = [];
        foreach (\app\common\service\WalletService::$typeLabels as $key => $value) {
            $dict[] = ['value' => $key, 'label' => $value];
        }

        $this->success('ok', $dict);
    }

    public function payaccount(): void
    {
        try {
            $wallet = $this->walletService->getWallet($this->auth->id);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('ok', ['pay_account' => $wallet->pay_account ?? []]);
    }

    public function setpayaccount(): void
    {
        if (!request()->isPost()) {
            $this->error('请求方式错误');
        }

        $data = request()->post(['account_type', 'account', 'qrcode_type', 'qrcode']);
        $payAccount = [];
        $accountType = trim($data['account_type'] ?? '');
        $account = trim($data['account'] ?? '');
        if ($accountType !== '' || $account !== '') {
            if ($accountType === '') {
                $this->error('账户类型不能为空');
            }
            if (!in_array($accountType, ['alipay'])) {
                $this->error('不支持的账户类型，目前仅支持 alipay');
            }
            if ($account === '') {
                $this->error('账号不能为空');
            }
            $payAccount['account_type'] = $accountType;
            $payAccount['account'] = $account;
        }

        $qrcodeType = trim($data['qrcode_type'] ?? '');
        $qrcode = trim($data['qrcode'] ?? '');
        if ($qrcodeType !== '' || $qrcode !== '') {
            if ($qrcodeType === '') {
                $this->error('收款码类型不能为空');
            }
            // [opcode 对照] 字面量池 {0:'wxpay',1:RAW0} 尾桶被清零；结合错误文案"仅支持 wxpay 或
            // alipay"（opcode 真值）及 agent/Finance.php $qrcodeTypes=['alipay','wxpay'] 还原为双类型
            if (!in_array($qrcodeType, ['wxpay', 'alipay'])) {
                $this->error('不支持的收款码类型，仅支持 wxpay 或 alipay');
            }
            if ($qrcode === '') {
                $this->error('收款二维码不能为空');
            }
            $payAccount['qrcode_type'] = $qrcodeType;
            $payAccount['qrcode'] = $qrcode;
        }

        if (empty($payAccount)) {
            $this->error('请至少填写一项打款账户或收款码信息');
        }

        try {
            $this->walletService->setPayAccount($this->auth->id, $payAccount);
            $wallet = $this->walletService->getWallet($this->auth->id);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('设置成功', ['pay_account' => $wallet->pay_account ?? []]);
    }
}
