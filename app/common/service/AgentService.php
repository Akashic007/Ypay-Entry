<?php

// 还原自 Swoole Compiler 加密文件: AgentService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/AgentService.php

namespace app\common\service;

class AgentService{
    public function agentrecharge(\app\admin\model\User|null $user = null, string|null $money = null): string
    {
        $agent = \app\admin\model\agent\Agent::where('id', $user->agent_id)
            ->where('user_id', '<>', $user->id)
            ->find();
        if (!$agent || $agent->status != 1) {
            return '0.00';
        }
        $subscribe = \app\admin\model\agent\Subscribe::find($agent->subscribe_id);
        if (!$subscribe) {
            return '0.00';
        }
        $ratio     = min(max((int) $subscribe->rebate_ratio, 0), 100);
        $moneyFen  = yuantofen($money);
        $rebateFen = intdiv($moneyFen * $ratio, 100);
        $rebate    = fentoyuan($rebateFen);
        if (bccomp($rebate, '0', 2) <= 0 || $subscribe->rebate_ratio <= 0) {
            return '0.00';
        }
        $walletService = new \app\common\service\WalletService();
        $walletService->income(
            $agent->user_id,
            $rebate,
            '代理返佣：用户[' . $user->username . ']充值[' . $money . ']元，返佣[' . $rebate . ']元',
            [
                'source_type'    => 'agent_recharge',
                'source_user_id' => $user->id,
                'agent_id'       => $agent->id,
            ],
            \app\common\service\WalletService::SOURCE_AGENT_REBATE
        );
        return $rebate;
    }

    public function applywithdrawal(\app\admin\model\agent\Agent|null $agent = null, string|null $money = null): void
    {
        $withdrawService = new \app\common\service\WithdrawService();
        try {
            $withdrawService->apply($agent->user_id, $money);
            return;
        } catch (\Exception $e) {
            throw new \ba\Exception($e->getMessage());
        }
    }

    public function calculateentrydiscountamount(\app\admin\model\agent\Agent|null $agent = null, \app\admin\model\EntryChannel|null $channel = null): string
    {
        $price    = $channel->price;
        $discount = $this->findentrydiscount($agent, $channel);
        $priceFen = yuantofen($price);
        $rate     = bcdiv($discount, '10', 4);
        return fentoyuan((int) bcmul((string) $priceFen, $rate, 0));
    }

    public function findentrydiscount(\app\admin\model\agent\Agent|null $agent = null, \app\admin\model\EntryChannel|null $channel = null): string
    {
        $discount = \app\admin\model\agent\UserEntryDiscount::where('agent_id', $agent->id)
            ->where('ec_id', $channel->id)
            ->where('status', 1)
            ->find();
        if ($discount) {
            return $this->normalizediscount((string) $discount->discount);
        }
        $subscribeDiscount = \app\admin\model\agent\SubscribeEntryDiscount::where('subscribe_id', $agent->subscribe_id)
            ->where('ec_id', $channel->id)
            ->where('status', 1)
            ->find();
        if ($subscribeDiscount) {
            return $this->normalizediscount((string) $subscribeDiscount->discount);
        }
        return $this->normalizediscount((string) $agent->subscribe->entry_discount);
    }

    private function normalizediscount(string|null $discount = null): string
    {
        if (bccomp($discount, '0', 2) < 0) {
            return '0';
        }
        if (0 < bccomp($discount, '10', 2)) {
            return '10';
        }
        return $discount;
    }

}
