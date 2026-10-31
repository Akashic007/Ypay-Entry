<?php

// 还原自 Swoole Compiler 加密文件: Test.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/Test.php

namespace app\api\controller;

class Test extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];

    public function index(): void
    {
        $rules = \app\admin\model\UserRule::where('status', 1)->select();
        $arr   = [];
        foreach ($rules as $rule) {
            $arr[] = $rule->toArray();
        }

        echo '[' . PHP_EOL;
        foreach ($arr as $key => $val) {
            echo '    [' . PHP_EOL;
            foreach ($val as $k => $v) {
                $vs = var_export($v, true);
                echo "        '" . $k . "' => " . $vs . ',' . PHP_EOL;
            }
            echo '    ],' . PHP_EOL;
        }
        echo '];' . PHP_EOL;

        echo PHP_EOL . '// JSON格式：' . PHP_EOL;
        echo json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function entryTest(): void
    {
        $channel = \app\admin\model\EntryChannel::find(2);
        try {
            $entry  = new \app\common\library\entryChannel\Alipay($channel);
            $result = $entry->querySignStatus('15865310521', ['I1080300001000060370']);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('ok');
    }

    public function entryPay(): void
    {
        \app\admin\model\UserMoneyLog::create([
            'user_id' => 1026,
            'money'   => -0.5,
            'memo'    => '扣除订单手续费0.5元',
            'type'    => \app\admin\model\UserMoneyLog::TYPE_ORDER_FEE,
            'content' => '12312321',
        ]);

        $this->success('ok扣费');
    }

    public function viewTest()
    {
        app()->getRootPath() . 'public/views/pay-template/newpay/index.html';
        return view('pay-template/newpay/index');
    }

    public function ocr(): void
    {
        return;
    }

    public function eventTest(): void
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
