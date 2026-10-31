<?php

// 还原自 Swoole Compiler 加密文件: OrderStatisticsService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/OrderStatisticsService.php
//
// [2026-10-02 opcode 全量对照还原] 20 方法整文件重写（此前 11 个趋势方法为断链空壳，
// 是商户端/管理端仪表盘趋势图的数据源）。全部字面量经 dec_literal 解密闭环。

namespace app\common\service;

class OrderStatisticsService
{
    protected \app\admin\model\Order $orderModel;

    public function __construct(\app\admin\model\Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    // 行 23-33: rangeType 分发 get{Ucfirst(rangeType)}Trend
    public function getsalestrend(int|null $userId = null, string|null $rangeType = null): array
    {
        $method = 'get' . ucfirst((string) $rangeType) . 'Trend';
        if (method_exists($this, $method)) {
            return $this->{$method}($userId);
        }
        throw new \InvalidArgumentException('不支持的统计范围: ' . $rangeType);
    }

    // 行 37-70: 按历史跨度选日/月/年粒度
    protected function gettotaltrend(int|null $userId = null): array
    {
        $orders = $this->orderModel->where('user_id', $userId)
            ->where('status', 1)
            ->order('create_time', 'asc')
            ->find();
        if (empty($orders)) {
            return [];
        }
        $firstTime = strtotime((string) $orders['create_time']);
        $days      = (int) ((time() - $firstTime) / 86400);
        if ($days < 30) {
            return $this->gettotaldailytrend($userId, $firstTime, time());
        }
        if ($days < 365) {
            return $this->gettotalmonthlytrend($userId, $firstTime, time());
        }
        return $this->gettotalyearlytrend($userId, $firstTime, time());
    }

    // 行 76-88
    protected function gettotaldailytrend(int|null $userId = null, int|null $startTime = null, int|null $endTime = null): array
    {
        $totalDays = (int) (($endTime - $startTime) / 86400) + 1;
        $orders    = $this->orderModel->where('user_id', $userId)
            ->where('status', 1)
            ->whereBetween('create_time', [$startTime, $endTime])
            ->field('create_time, pay_money')
            ->select();
        return $this->processtotaldailydata($orders, $startTime, $totalDays);
    }

    // 行 90-107
    protected function gettotalmonthlytrend(int|null $userId = null, int|null $startTime = null, int|null $endTime = null): array
    {
        $startYear   = (int) date('Y', $startTime);
        $startMonth  = (int) date('n', $startTime);
        $endYear     = (int) date('Y', $endTime);
        $endMonth    = (int) date('n', $endTime);
        $totalMonths = ($endYear - $startYear) * 12 + $endMonth - $startMonth + 1;
        $orders      = $this->orderModel->where('user_id', $userId)
            ->where('status', 1)
            ->whereBetween('create_time', [$startTime, $endTime])
            ->field('create_time, pay_money')
            ->select();
        return $this->processtotalmonthlydata($orders, $startYear, $startMonth, $totalMonths);
    }

    // 行 111-128
    protected function gettotalyearlytrend(int|null $userId = null, int|null $startTime = null, int|null $endTime = null): array
    {
        $startYear  = (int) date('Y', $startTime);
        $endYear    = (int) date('Y', $endTime);
        $totalYears = $endYear - $startYear + 1;
        $orders     = $this->orderModel->where('user_id', $userId)
            ->where('status', 1)
            ->whereBetween('create_time', [$startTime, $endTime])
            ->field('create_time, pay_money')
            ->select();
        return $this->processtotalyearlydata($orders, $startYear, $totalYears);
    }

    // 行 134-157
    protected function processtotaldailydata($orders, int|null $startTime = null, int|null $totalDays = null): array
    {
        $payStats = array_fill(0, (int) $totalDays, 0);
        foreach ($orders as $order) {
            $dayIndex = (int) (($order['create_time'] - $startTime) / 86400);
            if ($dayIndex >= 0 && $dayIndex < $totalDays) {
                $payStats[$dayIndex] += $order['pay_money'];
            }
        }
        $xAxis = [];
        $yAxis = [];
        for ($i = 0; $i < $totalDays; $i++) {
            $xAxis[] = date('m-d', $startTime + $i * 86400);
            $yAxis[] = $payStats[$i];
        }
        return ['xAxis' => $xAxis, 'yAxis' => $yAxis];
    }

    // 行 160-189
    protected function processtotalmonthlydata($orders, int|null $startYear = null, int|null $startMonth = null, int|null $totalMonths = null): array
    {
        $payStats  = array_fill(0, (int) $totalMonths, 0);
        $baseMonth = $startYear * 12 + $startMonth;
        foreach ($orders as $order) {
            $year  = (int) date('Y', $order['create_time']);
            $month = (int) date('n', $order['create_time']);
            $index = ($year - $startYear) * 12 + $month - $startMonth;
            if ($index >= 0 && $index < $totalMonths) {
                $payStats[$index] += $order['pay_money'];
            }
        }
        $xAxis = [];
        $yAxis = [];
        for ($i = 0; $i < $totalMonths; $i++) {
            $year    = (int) (($baseMonth + $i - 1) / 12);
            $month   = (($baseMonth + $i - 1) % 12) + 1;
            $xAxis[] = sprintf('%d-%02d', $year, $month);
            $yAxis[] = $payStats[$i];
        }
        return ['xAxis' => $xAxis, 'yAxis' => $yAxis];
    }

    // 行 194-217
    protected function processtotalyearlydata($orders, int|null $startYear = null, int|null $totalYears = null): array
    {
        $payStats = array_fill(0, (int) $totalYears, 0);
        foreach ($orders as $order) {
            $year  = (int) date('Y', $order['create_time']);
            $index = $year - $startYear;
            if ($index >= 0 && $index < $totalYears) {
                $payStats[$index] += $order['pay_money'];
            }
        }
        $xAxis = [];
        $yAxis = [];
        for ($i = 0; $i < $totalYears; $i++) {
            $xAxis[] = sprintf('%d年', $startYear + $i);
            $yAxis[] = $payStats[$i];
        }
        return ['xAxis' => $xAxis, 'yAxis' => $yAxis];
    }

    // 行 225-231
    protected function gettodaytrend(int|null $userId = null): array
    {
        $startTime = strtotime(date('Y-m-d 00:00:00'));
        $endTime   = strtotime(date('Y-m-d 23:59:59'));
        return $this->gethourlytrend($userId, $startTime, $endTime, 24);
    }

    // 行 235-241
    protected function getyesterdaytrend(int|null $userId = null): array
    {
        $startTime = strtotime(date('Y-m-d 00:00:00', strtotime('-1 day')));
        $endTime   = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        return $this->gethourlytrend($userId, $startTime, $endTime, 24);
    }

    // 行 245-252
    protected function getweektrend(int|null $userId = null): array
    {
        $startTime = strtotime(date('Y-m-d 00:00:00', strtotime('monday this week')));
        $endTime   = strtotime(date('Y-m-d 23:59:59', strtotime('sunday this week')));
        return $this->getdailytrend($userId, $startTime, $endTime, 7);
    }

    // 行 256-263
    protected function getmonthtrend(int|null $userId = null): array
    {
        $startTime = strtotime(date('Y-m-01 00:00:00'));
        $endTime   = strtotime(date('Y-m-t 23:59:59'));
        $totalDays = (int) date('t');
        return $this->getdailytrend($userId, $startTime, $endTime, $totalDays);
    }

    // 行 268-276
    protected function getyeartrend(int|null $userId = null): array
    {
        $startTime = strtotime(date('Y-01-01 00:00:00'));
        $endTime   = strtotime(date('Y-12-31 23:59:59'));
        return $this->getmonthlytrend($userId, $startTime, $endTime, 12);
    }

    // 行 281-293
    protected function gethourlytrend(int|null $userId = null, int|null $startTime = null, int|null $endTime = null, int|null $totalHours = null): array
    {
        $orders = $this->orderModel->where('user_id', $userId)
            ->where('status', 1)
            ->whereBetween('create_time', [$startTime, $endTime])
            ->field('create_time, pay_money')
            ->select();
        return $this->processhourlydata($orders, $startTime, $totalHours);
    }

    // 行 296-308
    protected function getdailytrend(int|null $userId = null, int|null $startTime = null, int|null $endTime = null, int|null $totalDays = null): array
    {
        $orders = $this->orderModel->where('user_id', $userId)
            ->where('status', 1)
            ->whereBetween('create_time', [$startTime, $endTime])
            ->field('create_time, pay_money')
            ->select();
        return $this->processdailydata($orders, $startTime, $totalDays);
    }

    // 行 311-323
    protected function getmonthlytrend(int|null $userId = null, int|null $startTime = null, int|null $endTime = null, int|null $totalMonths = null): array
    {
        $orders = $this->orderModel->where('user_id', $userId)
            ->where('status', 1)
            ->whereBetween('create_time', [$startTime, $endTime])
            ->field('create_time, pay_money')
            ->select();
        return $this->processmonthlydata($orders, $startTime, $totalMonths);
    }

    // 行 327-348
    protected function processhourlydata($orders, int|null $startTime = null, int|null $totalHours = null): array
    {
        $payStats = array_fill(0, (int) $totalHours, 0);
        foreach ($orders as $order) {
            $hour = (int) date('H', $order['create_time']);
            $payStats[$hour] += $order['pay_money'];
        }
        $xAxis = [];
        $yAxis = [];
        for ($i = 0; $i < $totalHours; $i++) {
            $xAxis[] = sprintf('%02d:00', $i);
            $yAxis[] = $payStats[$i];
        }
        return ['xAxis' => $xAxis, 'yAxis' => $yAxis];
    }

    // 行 352-380
    protected function processdailydata($orders, int|null $startTime = null, int|null $totalDays = null): array
    {
        $payStats = array_fill(0, (int) $totalDays, 0);
        $startKey = date('Y-m-d', $startTime);
        foreach ($orders as $order) {
            $dayKey   = date('Y-m-d', $order['create_time']);
            $dayIndex = (int) ((strtotime($dayKey) - strtotime($startKey)) / 86400);
            if ($dayIndex >= 0 && $dayIndex < $totalDays) {
                $payStats[$dayIndex] += $order['pay_money'];
            }
        }
        $xAxis = [];
        $yAxis = [];
        for ($i = 0; $i < $totalDays; $i++) {
            $xAxis[] = date('m-d', $startTime + $i * 86400);
            $yAxis[] = $payStats[$i];
        }
        return ['xAxis' => $xAxis, 'yAxis' => $yAxis];
    }

    // 行 384-409
    protected function processmonthlydata($orders, int|null $startTime = null, int|null $totalMonths = null): array
    {
        $payStats = array_fill(0, (int) $totalMonths, 0);
        foreach ($orders as $order) {
            $month = (int) date('n', $order['create_time']);
            $index = $month - 1;
            if ($index >= 0 && $index < $totalMonths) {
                $payStats[$index] += $order['pay_money'];
            }
        }
        $xAxis = [];
        $yAxis = [];
        for ($i = 0; $i < $totalMonths; $i++) {
            $xAxis[] = sprintf('%d月', $i + 1);
            $yAxis[] = $payStats[$i];
        }
        return ['xAxis' => $xAxis, 'yAxis' => $yAxis];
    }
}
