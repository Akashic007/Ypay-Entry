<?php

// 还原自 Swoole Compiler 加密文件: Test.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Test.php

namespace app\api\controller;

class Test extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];
    public function index(): void
    {
        $list = \app\admin\model\Order::where('id', '>', 1)->select();
        foreach ($list as $item) {
            $item->status = $item->status - 1;
            $item->save();
        }
        $this->success('ok');
    }

    public function entrytest(): void
    {
        $url = 'https://chatgpt.com/c/69f7976e-f018-8324-bdf0-5b4399859956';
        $url = parse_url($url);
        $this->success('ok', ['url' => $url]);
    }

    public function entrypay(): void
    {
        \app\admin\model\UserMoneyLog::create([
            'user_id' => 1026,
            'money' => -0.5,
            'memo' => '扣除订单手续费0.5元',
            'type' => \app\admin\model\UserMoneyLog::TYPE_ORDER_FEE,
            'content' => '12312321',
        ]);
        $this->success('ok扣费');
    }

    public function viewtest()
    {
        // [opcode 对照] 行81 CONCAT 结果被 FREE 指令丢弃（原文件即无用表达式，原样保留）
        app()->getRootPath() . 'public/views/pay-template/newpay/index.html';
        return view('pay-template/newpay/index');
    }

    public function ocr(): void
    {
        return;
    }

    public function eventtest(): void
    {
        return;
    }

    public function token(): void
    {
        return;
    }

    public function redis(): void
    {
        return;
    }

    public function queue(): void
    {
        $this->success('ok');
    }

    public function channel(): void
    {
        exit;
    }

}
