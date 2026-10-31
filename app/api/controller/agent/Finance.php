<?php

// 还原自 Swoole Compiler 加密文件: Finance.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/Finance.php

namespace app\api\controller\agent;

class Finance extends \app\common\controller\Frontend {
    private \app\admin\model\agent\Agent $agent;
    public function initialize(): void
    {
        parent::initialize();
        $agent = \app\admin\model\agent\Agent::where('user_id', $this->auth->id)
            ->with(['subscribe' => function ($query) {
                $query->withoutField('price');
            }])
            ->find();
        if (empty($agent)) {
            $this->error('暂未开通代理');
        }
        if ($agent->status != 1) {
            $this->error('代理状态异常');
        }
        $this->agent = $agent;
    }

    public function getmoneylog(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $list  = \app\admin\model\user\WalletMoneyLog::where('user_id', $this->auth->id)
            ->where('source', \app\common\service\WalletService::SOURCE_AGENT_REBATE)
            ->order('id', 'desc')
            ->paginate($limit);
        $this->success('ok', $list);
    }

    public function applywithdrawal(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $post = $this->request->post(['money', 'verify_account', 'verify_type', 'captcha']);
        if ($this->agent->withdrawal_money < $post['money']) {
            $this->error('提现余额不足');
        }
        $payAccount = $this->agent->pay_account;
        $user = $this->auth->getUser();
        if ($post['verify_account'] != $user[$post['verify_type']]) {
            $this->error('验证账户信息错误');
        }
        if (empty($payAccount['account']) && empty($payAccount['qrcode'])) {
            $this->error('请先设置提现打款信息');
        }
        if (empty($post['verify_type']) || !$post['verify_account'] && !empty($post['captcha'])) {
            $this->error('验证账户参数错误');
        }
        $captcha = new \ba\Captcha();
        try {
            if (!$captcha->check($post['captcha'], $post['verify_account'] . 'user_' . $post['verify_type'] . '_verify')) {
                throw new \ba\Exception('验证码错误');
            }
            $agentService = new \app\common\service\AgentService();
            $agentService->applyWithdrawal($this->agent, $post['money']);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('申请成功');
    }

    public function withdrawalaccount(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $post = $this->request->post(['account_type', 'qrcode_type', 'account', 'qrcode']);
        $accountTypes = ['alipay'];
        $qrcodeTypes  = ['alipay', 'wxpay'];
        if (!in_array($post['account_type'], $accountTypes)) {
            $this->error('收款账户类型错误');
        }
        if (!in_array($post['qrcode_type'], $qrcodeTypes)) {
            $this->error('收款二维码类型错误');
        }
        if (empty($post['account']) && empty($post['qrcode'])) {
            $this->error('收款账户和收款二维码不能同时为空');
        }
        $verify = $this->request->post(['verify_type', 'captcha', 'verify_account']);
        if (empty($verify['verify_type']) || !$verify['verify_account'] && !empty($verify['captcha'])) {
            $this->error('验证账户参数错误');
        }
        $captcha = new \ba\Captcha();
        try {
            // [opcode 对照] 验证码键为 $verify[$verify['verify_account']] 嵌套取值，
            // 与已还原 Account.php 解绑处的 $post[$post['type']] 同构，按 opcode 字面量保留。
            if (!$captcha->check($verify['captcha'], $verify[$verify['verify_account']] . 'user_' . $verify['verify_type'] . '_verify')) {
                throw new \ba\Exception('验证码错误');
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $payAccount = $this->agent->pay_account;
        $payAccount = array_merge($payAccount, $post);
        $this->agent->pay_account = $payAccount;
        $this->agent->save();
        $this->success('设置成功');
    }

}
