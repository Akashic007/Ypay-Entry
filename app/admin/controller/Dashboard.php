<?php

// 还原自 Swoole Compiler 加密文件: Dashboard.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/Dashboard.php

namespace app\admin\controller;

class Dashboard extends \app\common\controller\Backend {
    const PERIODS = ['today', 'yesterday', 'week', 'month', 'year'];
    const PAY_TYPES = ['wxpay' => '微信支付', 'alipay' => '支付宝', 'qqpay' => 'QQ支付'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): void
    {
        $user = [
            'total_users'      => \app\common\model\User::count(),
            'today_new'        => \app\common\model\User::whereDay('join_time')->count(),
            'yesterday_new'    => \app\common\model\User::whereDay('join_time', 'yesterday')->count(),
            'week_new'         => \app\common\model\User::whereWeek('join_time')->count(),
            'month_new'        => \app\common\model\User::whereMonth('join_time')->count(),
            'year_new'         => \app\common\model\User::whereYear('join_time')->count(),
            'subscribed_users' => \app\common\model\User::where('subscribe_id', '>', 0)->where('subscribe_expire_time', '>', time())->count(),
            'disabled_users'   => \app\common\model\User::where('status', 'disable')->count(),
        ];

        $order = [
            'total_orders'     => \app\admin\model\Order::where('is_test', 0)->count(),
            'today_orders'     => $this->countOrders('today'),
            'yesterday_orders' => $this->countOrders('yesterday'),
            'week_orders'      => $this->countOrders('week'),
            'month_orders'     => $this->countOrders('month'),
            'year_orders'      => $this->countOrders('year'),
        ];

        $sales = [
            'total_sales'     => $this->sumOrderSales(),
            'today_sales'     => $this->sumOrderSales('today'),
            'yesterday_sales' => $this->sumOrderSales('yesterday'),
            'week_sales'      => $this->sumOrderSales('week'),
            'month_sales'     => $this->sumOrderSales('month'),
            'year_sales'      => $this->sumOrderSales('year'),
        ];

        $recharge = [
            'total_recharge'     => $this->fenToYuan(\app\admin\model\sys\Order::where('status', 1)->sum('payment_amount')),
            'today_recharge'     => $this->sumRecharge('today'),
            'yesterday_recharge' => $this->sumRecharge('yesterday'),
            'week_recharge'      => $this->sumRecharge('week'),
            'month_recharge'     => $this->sumRecharge('month'),
            'year_recharge'      => $this->sumRecharge('year'),
        ];

        $channel = [
            'total_channels'  => \app\admin\model\channel\Account::count(),
            'online_channels' => \app\admin\model\channel\Account::where('online_status', 1)->count(),
        ];

        $withdrawal = [
            'total_withdrawal'     => $this->sumWithdrawal(),
            'today_withdrawal'     => $this->sumWithdrawal('today'),
            'yesterday_withdrawal' => $this->sumWithdrawal('yesterday'),
            'pending_withdrawal'   => $this->fenToYuan(\app\admin\model\user\WithdrawalApplyLog::whereIn('status', [0])->sum('apply_amount')),
        ];

        $complaint = [
            'pending_complaints' => \app\admin\model\Complaint::where('status', 0)->count(),
        ];

        $channelSales       = $this->buildChannelSales();
        $merchantSalesRank  = $this->buildMerchantSalesRank();

        $last7 = [];
        for ($i = 0; $i < 7; $i++) {
            $day  = date('Y-m-d', strtotime('-' . $i . ' days'));
            $start = strtotime($day . ' 00:00:00');
            $end   = strtotime($day . ' 23:59:59');
            $last7[] = [
                'date'        => $day,
                'label'       => $i == 0 ? '今天' : ($i == 1 ? '昨天' : date('m/d', strtotime($day))),
                'order_count' => \app\admin\model\Order::where('is_test', 0)->whereBetween('create_time', [$start, $end])->count(),
                'order_sales' => $this->fenToYuan(\app\admin\model\Order::where('status', 1)->where('is_test', 0)->whereBetween('create_time', [$start, $end])->sum('pay_money')),
                'recharge'    => $this->fenToYuan(\app\admin\model\sys\Order::where('status', 1)->whereBetween('create_time', [$start, $end])->sum('payment_amount')),
            ];
        }
        $last7 = array_reverse($last7);

        $sysInfo = [
            'version'          => get_sys_config('version'),
            'os'               => PHP_OS,
            'base_url'         => request()->domain(),
            'php_version'      => PHP_VERSION,
            'thinkphp_version' => app()->version(),
            'server_software'  => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        ];

        $this->success('', [
            'user'                => $user,
            'order'               => $order,
            'order_sales'         => $sales,
            'recharge'            => $recharge,
            'channel'             => $channel,
            'withdrawal'          => $withdrawal,
            'complaint'           => $complaint,
            'channel_sales'       => $channelSales,
            'merchant_sales_rank' => $merchantSalesRank,
            'last_7_days'         => $last7,
            'sys_info'            => $sysInfo,
        ]);
    }

    private function countOrders(string|null $period = null): int
    {
        $query = \app\admin\model\Order::where('is_test', 0);
        $this->applyPeriod($query, 'create_time', $period);
        return $query->count();
    }

    private function sumOrderSales(null|string $period = null, null|string $type = null): float
    {
        $query = \app\admin\model\Order::where('status', 1)->where('is_test', 0);
        if ($type !== null) {
            $query->where('type', $type);
        }
        if ($period !== null) {
            $this->applyPeriod($query, 'create_time', $period);
        }
        return $this->fenToYuan($query->sum('pay_money'));
    }

    private function sumRecharge(string|null $period = null): float
    {
        $query = \app\admin\model\sys\Order::where('status', 1);
        $this->applyPeriod($query, 'create_time', $period);
        return $this->fenToYuan($query->sum('payment_amount'));
    }

    private function sumWithdrawal(null|string $period = null): float
    {
        $query = \app\admin\model\user\WithdrawalApplyLog::where('status', 3);
        if ($period !== null) {
            $this->applyPeriod($query, 'create_time', $period);
        }
        return $this->fenToYuan($query->sum('final_amount'));
    }

    private function buildChannelSales(): array
    {
        $sales = ['total' => $this->salesByPayType()];
        foreach (self::PERIODS as $period) {
            $sales[$period] = $this->salesByPayType($period);
        }

        $arr = [];
        foreach (self::PAY_TYPES as $type => $label) {
            $item = [
                'type'        => $type,
                'label'       => $label,
                'total_sales' => $sales['total'][$type],
            ];
            foreach (self::PERIODS as $period) {
                $item[$period . '_sales'] = $sales[$period][$type];
            }
            $arr[] = $item;
        }
        return $arr;
    }

    private function salesByPayType(null|string $period = null): array
    {
        $types = array_keys(self::PAY_TYPES);
        $arr   = array_fill_keys($types, 0.0);

        $query = \think\facade\Db::name('order')
            ->where('status', 1)
            ->where('is_test', 0)
            ->whereIn('type', $types)
            ->field('type, SUM(pay_money) as sales_amount')
            ->group('type');
        if ($period !== null) {
            $this->applyPeriod($query, 'create_time', $period);
        }
        $rows = $query->select()->toArray();
        foreach ($rows as $row) {
            $arr[$row['type']] = $this->fenToYuan($row['sales_amount']);
        }
        return $arr;
    }

    private function buildMerchantSalesRank(): array
    {
        $arr = [];
        foreach (self::PERIODS as $period) {
            $arr[$period] = $this->merchantSalesRankByPeriod($period);
        }
        return $arr;
    }

    private function merchantSalesRankByPeriod(string|null $period = null): array
    {
        $query = \think\facade\Db::name('order')
            ->where('status', 1)
            ->where('is_test', 0)
            ->field('user_id, COUNT(*) as order_count, SUM(pay_money) as sales_amount')
            ->group('user_id')
            ->order('sales_amount', 'desc')
            ->limit(10);
        $this->applyPeriod($query, 'create_time', $period);
        $rows = $query->select()->toArray();

        $userIds = array_values(array_unique(array_filter(array_column($rows, 'user_id'))));
        $usernames = $userIds ? \app\common\model\User::whereIn('id', $userIds)->column('username', 'id') : [];

        foreach ($rows as $index => &$row) {
            $rank = $index + 1;
            $uid  = (int)$row['user_id'];
            $row  = [
                'rank'         => $rank,
                'user_id'      => $uid,
                'username'     => $usernames[$uid] ?? '商户#' . $uid,
                'order_count'  => (int)$row['order_count'],
                'sales_amount' => $this->fenToYuan($row['sales_amount']),
            ];
        }
        unset($row);

        return $rows;
    }

    private function applyPeriod($query, string|null $field = null, string|null $period = null): void
    {
        switch ($period) {
            case 'today':
                $query->whereDay($field);
                break;
            case 'yesterday':
                $query->whereDay($field, 'yesterday');
                break;
            case 'week':
                $query->whereWeek($field);
                break;
            case 'month':
                $query->whereMonth($field);
                break;
            case 'year':
                $query->whereYear($field);
                break;
        }
    }

    private function fenToYuan($fen): float
    {
        return round(floatval($fen) / 100, 2);
    }
}
