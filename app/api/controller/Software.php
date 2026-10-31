<?php

// 还原自 Swoole Compiler 加密文件: Software.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Software.php

namespace app\api\controller;

class Software extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];
    protected array $channel_types = ['wxpay_software', null];
    public function callback(): void
    {
        $params = $this->request->param(['id', 'key']);
        try {
            validate([
                'id' => 'require|number',
                'key' => 'require',
                'type' => 'require',
                'money' => 'require|float',
            ])->message([
                'id.require' => '商户ID不能为空',
                'id.number' => '商户ID格式错误',
                'key.require' => '通讯密钥不能为空',
                'type.require' => '通道支付类型不能为空',
                'money.require' => '订单金额不能为空',
            ])->check($params);
        } catch (\think\exception\ValidateException $e) {
            $this->error($e->getError());
        }
        $user = \app\admin\model\User::find($params['id']);
        if (empty($user)) {
            $this->error('商户不存在');
        }
        if ($user->key != $params['key']) {
            $this->error('商户密钥错误');
        }
        $order = \app\admin\model\Order::hasWhere('channel', function ($query) use ($params, $user) {
            $query->whereIn('channel_type', $this->channel_types);
            $query->where('user_id', $user->id);
        })->where('type', $params['type'])->where('status', 0)->where('user_id', $user->id)->where('expire_time', '>', time())->where('pay_money', bcmul($params['money'], 100, 0))->order('id desc')->findOrEmpty();
        if ($order->isEmpty()) {
            $this->error('订单不存在');
        }
        try {
            $orderService = new \app\common\service\OrderService();
            $orderService->callback($order);
        } catch (\Exception $e) {
            $this->error('回调失败：' . $e->getMessage());
        }
        $this->success('订单回调成功');
    }

}
