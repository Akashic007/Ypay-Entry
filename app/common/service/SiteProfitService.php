<?php

// 还原自 Swoole Compiler 加密文件: SiteProfitService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/SiteProfitService.php

namespace app\common\service;

class SiteProfitService{
    public function handlerecharge(\app\admin\model\User|null $user = null, string|null $money = null): array
    {
        $site  = $this->getusersite($user);
        $agent = $this->getuseragent($user);
        if (!$site && !$agent) {
            return ['winner' => 'none', 'rebate' => '0.00'];
        }
        $ratio      = $site ? $this->getsiterebateratio($site) : 0;
        $agentRatio = $agent ? $this->getagentrebateratio($agent) : 0;
        if ($agent && (!$site || $ratio < $agentRatio)) {
            $agentService = new \app\common\service\AgentService();
            $rebate       = $agentService->agentrecharge($user, $money);
            return ['winner' => 'agent', 'rebate' => $rebate];
        }
        if ($site) {
            $rebate = $this->dositerebate($site, $user, $money, $ratio);
            return ['winner' => 'site', 'rebate' => $rebate];
        }
        return ['winner' => 'none', 'rebate' => '0.00'];
    }

    public function addprofit(\app\admin\model\site\Site|null $site = null, string|null $money = null, string|null $remark = null, array|null $content = null): void
    {
        if (bccomp($money, '0', 2) <= 0) {
            throw new \Exception('收益金额必须大于0');
        }
        $content       = array_merge($content, ['site_id' => $site->id]);
        $walletService = new \app\common\service\WalletService();
        $walletService->income(
            $site->user_id,
            $money,
            $remark,
            $content,
            \app\common\service\WalletService::SOURCE_SITE_REBATE
        );
        return;
    }

    public function getprofitlog(int|null $userId = null, array|null $filter = null, int|null $limit = 15): \think\Paginator
    {
        $query = \app\admin\model\user\WalletMoneyLog::where('user_id', $userId)
            ->where('source', \app\common\service\WalletService::SOURCE_SITE_REBATE);
        if (!empty($filter['start_date'])) {
            $query->where('create_time', '>=', $filter['start_date']);
        }
        if (!empty($filter['end_date'])) {
            $query->where('create_time', '<=', $filter['end_date']);
        }
        $query->order('id', 'desc');
        return $query->paginate($limit);
    }

    public function getstatistics(int|null $userId = null): array
    {
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd   = date('Y-m-t 23:59:59');
        $query      = \app\admin\model\user\WalletMoneyLog::where('user_id', $userId)
            ->where('source', \app\common\service\WalletService::SOURCE_SITE_REBATE);
        $totalProfit = fentoyuan(max((clone $query)->sum('money'), 0));
        $monthProfit = fentoyuan(max((clone $query)
            ->where('create_time', 'between', [$monthStart, $monthEnd])
            ->sum('money'), 0));
        $logCount = (clone $query)->count();
        return [
            'total_profit' => $totalProfit,
            'month_profit' => $monthProfit,
            'log_count'    => $logCount,
        ];
    }

    private function getusersite(\app\admin\model\User|null $user = null): \app\admin\model\site\Site|null // [opcode 对照] opcode 有多处 return null(VERIFY_RETURN_TYPE 前)，原签名为可空 ?Site
    {
        if (empty($user->site_id)) {
            return null;
        }
        if (!\app\common\service\SiteService::isplugininstalled()) {
            return null;
        }
        $site = \app\admin\model\site\Site::where('id', $user->site_id)
            ->where('status', 1)
            ->find();
        if ($site && $site->user_id == $user->id) {
            return null;
        }
        return $site ?: null;
    }

    private function getuseragent(\app\admin\model\User|null $user = null): \app\admin\model\agent\Agent|null // [opcode 对照] opcode 有 return null(VERIFY_RETURN_TYPE 前)，原签名为可空 ?Agent
    {
        if (empty($user->agent_id)) {
            return null;
        }
        $agent = \app\admin\model\agent\Agent::where('id', $user->agent_id)
            ->where('user_id', '<>', $user->id)
            ->where('status', 1)
            ->find();
        return $agent ?: null;
    }

    private function getsiterebateratio(\app\admin\model\site\Site|null $site = null): int
    {
        if (!\app\common\service\SiteService::isplugininstalled()) {
            return 0;
        }
        $subscribe = \app\admin\model\site\Subscribe::find($site->subscribe_id);
        if (!$subscribe) {
            return 0;
        }
        return min(max((int) $subscribe->rebate_ratio, 0), 100);
    }

    private function getagentrebateratio(\app\admin\model\agent\Agent|null $agent = null): int
    {
        $subscribe = \app\admin\model\agent\Subscribe::find($agent->subscribe_id);
        if (!$subscribe) {
            return 0;
        }
        return min(max((int) $subscribe->rebate_ratio, 0), 100);
    }

    private function dositerebate(\app\admin\model\site\Site|null $site = null, \app\admin\model\User|null $user = null, string|null $money = null, int|null $ratio = null): string
    {
        $moneyFen  = yuantofen($money);
        $rebateFen = intdiv($moneyFen * $ratio, 100);
        $rebate    = fentoyuan($rebateFen);
        if (bccomp($rebate, '0', 2) <= 0) {
            return '0.00';
        }
        $walletService = new \app\common\service\WalletService();
        $walletService->income(
            $site->user_id,
            $rebate,
            '分站返佣：用户[' . $user->username . ']充值[' . $money . ']元，返佣[' . $rebate . ']元',
            [
                'source_type'    => 'site_recharge',
                'source_user_id' => $user->id,
            ]
        );
        return $rebate;
    }

}
