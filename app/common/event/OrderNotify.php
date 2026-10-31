<?php

// 还原自 Swoole Compiler 加密文件: OrderNotify.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/event/OrderNotify.php

namespace app\common\event;

class OrderNotify implements \app\common\event\ShouldNotify {
    protected int $orderId;
    protected null|int $userId;
    protected string $event;
    protected array $params;
    const CREATE = 'order_create';
    const PAY_SUCCESS = 'order_pay_success';
    const CALLBACK = 'order_callback';
    // [2026-10-02 opcode 对照修复] 行 21-27: params 参数默认 []; orderId=order.id;
    // userId = is_int(order['user_id']) ? order['user_id'] : null; event 直存;
    // params = array_merge(params, ['order_id' => order.id])
    public function __construct(\app\admin\model\Order $order, string $event, array $params = [])
    {
        $this->orderId = $order->id;
        $this->userId  = is_int($order['user_id'] ?? null) ? $order['user_id'] : null;
        $this->event   = $event;
        $this->params  = array_merge($params, ['order_id' => $order->id]);
    }

    public function getnotifyevent(): string
    {
        return $this->event;
    }

    public function getnotifyuserid(): null|int
    {
        return $this->userId;
    }

    public function getnotifyparams(): array
    {
        return $this->params;
    }

}
