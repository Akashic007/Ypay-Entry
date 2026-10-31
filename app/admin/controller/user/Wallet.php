<?php

// 还原自 Swoole Compiler 加密文件: Wallet.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/Wallet.php

namespace app\admin\controller\user;

class Wallet extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id'];
    protected array $withJoinTable = ['user'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\user\Wallet();
    }


    public function add(): void
    {
        if (!$this->request->isPost()) {
            $this->error(__('Request method error'));
        }

        $userId = (int)$this->request->post('user_id', 0);
        $remark = (string)$this->request->post('remark', '');

        if ($userId <= 0) {
            $this->error('请选择用户');
        }

        $service = new \app\common\service\WalletService();
        $wallet  = \app\admin\model\user\Wallet::where('user_id', $userId)->find();
        if ($wallet) {
            $this->error('该用户钱包已存在');
        }

        try {
            $service->getorcreatewallet($userId);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('钱包创建成功');
    }


    public function freeze(): void
    {
        $id     = (int)$this->request->post('id', 0);
        $amount = (string)$this->request->post('amount', '');
        $remark = (string)$this->request->post('remark', '');

        if ($id <= 0) {
            $this->error('参数错误');
        }

        if (!is_numeric($amount) || bccomp($amount, '0', 2) <= 0) {
            $this->error('请输入有效金额');
        }

        if (empty($remark)) {
            $this->error('请输入备注');
        }

        $wallet = \app\admin\model\user\Wallet::find($id);
        if (!$wallet) {
            $this->error('钱包不存在');
        }

        try {
            $service = new \app\common\service\WalletService();
            $service->freeze($wallet->user_id, $amount, $remark, ['operator' => 'admin']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('冻结成功');
    }


    public function unfreeze(): void
    {
        $id     = (int)$this->request->post('id', 0);
        $amount = (string)$this->request->post('amount', '');
        $remark = (string)$this->request->post('remark', '');

        if ($id <= 0) {
            $this->error('参数错误');
        }

        if (!is_numeric($amount) || bccomp($amount, '0', 2) <= 0) {
            $this->error('请输入有效金额');
        }

        if (empty($remark)) {
            $this->error('请输入备注');
        }

        $wallet = \app\admin\model\user\Wallet::find($id);
        if (!$wallet) {
            $this->error('钱包不存在');
        }

        try {
            $service = new \app\common\service\WalletService();
            $service->unfreeze($wallet->user_id, $amount, $remark, ['operator' => 'admin']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('解冻成功');
    }


    public function transfer(): void
    {
        $id     = (int)$this->request->post('id', 0);
        $amount = (string)$this->request->post('amount', '');

        if ($id <= 0) {
            $this->error('参数错误');
        }

        if (!is_numeric($amount) || bccomp($amount, '0', 2) <= 0) {
            $this->error('请输入有效金额');
        }

        $wallet = \app\admin\model\user\Wallet::find($id);
        if (!$wallet) {
            $this->error('钱包不存在');
        }

        try {
            $service = new \app\common\service\WalletService();
            $service->transfertouserbalance($wallet->user_id, $amount);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('转入消费余额成功');
    }


    public function adjust(): void
    {
        $id     = (int)$this->request->post('id', 0);
        $amount = (string)$this->request->post('amount', '');
        $remark = (string)$this->request->post('remark', '');

        if ($id <= 0) {
            $this->error('参数错误');
        }

        if (!is_numeric($amount) || bccomp($amount, '0', 2) === 0) {
            $this->error('请输入有效金额（不能为0）');
        }

        if (empty($remark)) {
            $this->error('请输入调整原因');
        }

        $wallet = \app\admin\model\user\Wallet::find($id);
        if (!$wallet) {
            $this->error('钱包不存在');
        }

        try {
            $service = new \app\common\service\WalletService();
            $service->adjust($wallet->user_id, $amount, $remark, ['operator' => 'admin']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('余额调整成功');
    }

    // [opcode 真值 2026-09-23 B19-r2 移交] 资金安全闸门：禁止后台直改钱包行（绕过 UserMoneyLog 语义）
    public function edit(): void
    {
        $this->error('金额调整请使用专用操作功能');
    }


}
