<?php

// 还原自 Swoole Compiler 加密文件: WxpayOfficialSpecial.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/WxpayOfficialSpecial.php

namespace app\common\library\payChannel;

class WxpayOfficialSpecial implements \app\common\service\channel\contract\WxpayOfficialSpecialInterface, \app\common\service\channel\Refundable {
    protected string $sp_appid;
    protected string $sp_mchid;
    protected string $sub_appid;
    protected string $sub_mchid;
    protected string $platformCertPath;
    protected string $platformCertSerial;
    protected \app\admin\model\EntryChannel $entryChannel;
    protected \app\admin\model\merchant\Merchant $merchant;
    protected \app\admin\model\channel\Account $account;
    protected \app\common\library\wechat\RequestTool $requestTool;
    protected object $instance;
    protected array $apiTypes = ['JSAPI', null];
    protected array $apiUrls = ['JSAPI' => '/v3/pay/partner/transactions/jsapi', 'Native' => '/v3/pay/partner/transactions/native'];
    public function __construct(\app\admin\model\channel\Account $account)
    {
    $this->account = $account;
    }

    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
    $this->init();
    if ($this->account->config['api_type'] == 'JSAPI') {
        $jsApiTool = new \app\common\library\payChannel\wxpayOfficialSpecial\JsApiTool($this->entryChannel->config['appId'], $this->entryChannel->config['appSecret']);
        $url = $jsApiTool->getLoginUrl($order->order_no);
        return new \app\common\service\channel\PayInfo(['qrcode' => $url, 'wx_url' => $url, 'h5_url' => 'weixin://']);
    }
    throw new \ba\Exception('暂不支持的支付接口');
    }

    public function getprepayid(\app\admin\model\Order|null $order = null): string
    {
    $this->init();
    $params = [
        'sp_appid'     => $this->sp_appid,
        'sp_mchid'     => $this->sp_mchid,
        'sub_appid'    => $this->sub_appid ?? '',
        'sub_mchid'    => $this->sub_mchid,
        'description'  => $order->name,
        'out_trade_no' => $order->order_no,
        'notify_url'   => request()->domain() . '/notify/pay/wxpaySpecial/' . $order->order_no,
        'amount'       => ['total' => (int) round($order->pay_money * 100), 'currency' => 'CNY'],
    ];
    $params = array_merge($params, $this->buildExtraParams($order));
    try {
        $response = $this->instance->chain($this->apiUrls[$this->account->config['api_type']])->post(['json' => $params, 'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json']]);
    } catch (\Exception $e) {
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            if ($e->hasResponse()) {
                $errResponse = $e->getResponse();
                throw new \ba\Exception('支付异常：' . $errResponse->getBody());
            }
        }
        throw new \ba\Exception('请求异常' . $e->getMessage());
    }
    $response = json_decode($response->getBody()->getContents(), true);
    return $response['prepay_id'] ?? '';
    }

    public function callback(\app\admin\model\Order|null $order = null): bool
    {
    try {
        $this->init();
    } catch (\Exception $e) {
        return false;
    }
    $verify = $this->requestTool->verifySign();
    if (!$verify) {
        return false;
    }
    $data = $this->requestTool->getContentDecryption();
    if ($data['trade_state'] == 'SUCCESS') {
        $order->trade_no = $data['transaction_id'];
        $order->save();
        return true;
    }
    return false;
    }

    public function refund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
    $maxRefundTime = 31536000;
    if ($order->pay_time + $maxRefundTime < time()) {
        throw new \ba\Exception('订单已超过最大退款时间');
    }
    $merchant = \app\admin\model\merchant\Merchant::find($this->account->mch_id);
    if (empty($merchant)) {
        throw new \ba\Exception('商户信息不存在');
    }
    $params = [
        'sub_mchid'     => $merchant->merchant_id,
        'out_refund_no' => $record->refund_no,
        'notify_url'    => request()->domain() . '/notify/pay/wxpaySpecial/refund/' . $record->refund_no,
        'amount'        => ['refund' => (int) round($order->pay_money * 100), 'total' => (int) round($order->pay_money * 100), 'currency' => 'CNY'],
    ];
    if (!empty($order->trade_no)) {
        $params['transaction_id'] = $order->trade_no;
    } else {
        if (empty($order->order_no)) {
            throw new \ba\Exception('订单号不存在');
        }
        $params['out_trade_no'] = $order->order_no;
    }
    // [opcode 对照] reason 校验源在 opcode 中是未绑定 CV($Sh44882, 全方法无赋值)，运行时恒 null → empty() 短路，本分支不可达；按 null 保守还原
    $reasonSource = null;
    if (!empty($reasonSource['reason'])) {
        if (strlen($reasonSource['reason']) > 80) {
            throw new \ba\Exception('退款原因长度不能超过80个字符');
        }
        $params['reason'] = $reasonSource['reason'];
    }
    $refundFen = yuantofen($record->refund_amount);
    if (0 < $refundFen) {
        $params['amount']['refund'] = $refundFen;
    }
    // [opcode 对照] whereIn 第二元素 zval 解析为 UNDEF(0)，按状态语义(0=退款中 1=成功，3/4 不计)取 1
    $check = \app\admin\model\RefundRecord::where(['order_id' => $order->id, 'order_no' => $order->order_no])->whereIn('status', [0, 1]);
    if (0 < $check->count()) {
        if (50 <= $check->count()) {
            throw new \ba\Exception('累计退款次数超过50次');
        }
    }
    try {
        $this->init();
        $response = $this->instance->chain('/v3/refund/domestic/refunds')->post(['json' => $params, 'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json']]);
        $response = json_decode($response->getBody()->getContents(), true);
    } catch (\Exception $e) {
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            if ($e->hasResponse()) {
                $errResponse = $e->getResponse();
                $errData = json_decode($errResponse->getBody());
                $message = $errData->message;
            } else {
                $message = $e->getMessage();
            }
        } else {
            $message = $e->getMessage();
        }
        throw new \ba\Exception($message);
    }
    $refundStatus = match ($response['status']) {
        'SUCCESS' => 1, 'CLOSED' => 3, 'PROCESSING' => 0, default => 4,
    };
    $record->status = $refundStatus;
    $record->refund_channel = $response['channel'];
    $record->refund_trade_no = $response['refund_id'];
    $record->order_amount = fentoyuan($response['amount']['total']);
    $record->refund_amount = fentoyuan($response['amount']['refund']);
    $record->refund_account = $response['user_received_account'];
    if ($refundStatus == 1) {
        $record->refund_time = time();
    }
    $record->save();
    }

    public function queryrefund(\app\admin\model\Order|null $order = null, \app\admin\model\RefundRecord|null $record = null): void
    {
    $this->init();
    $merchant = \app\admin\model\merchant\Merchant::find($this->account->mch_id);
    if (empty($merchant)) {
        throw new \ba\Exception('商户信息不存在');
    }
    try {
        $response = $this->instance->chain('/v3/refund/domestic/refunds/' . $record->refund_no)->get(['query' => ['sub_mchid' => $merchant->merchant_id], 'headers' => ['Accept' => 'application/json']]);
        $response = json_decode($response->getBody()->getContents(), true);
    } catch (\Exception $e) {
        if ($e instanceof \GuzzleHttp\Exception\RequestException) {
            if ($e->hasResponse()) {
                $errResponse = $e->getResponse();
                $errData = json_decode($errResponse->getBody());
                throw new \ba\Exception('退款查询失败：' . ($errData->message ?? $e->getMessage()));
            }
        }
        throw new \ba\Exception('退款查询请求异常：' . $e->getMessage());
    }
    $refundStatus = match ($response['status']) {
        'SUCCESS' => 1, 'CLOSED' => 3, 'PROCESSING' => 0, default => 4,
    };
    $record->status = $refundStatus;
    if (!empty($response['channel'])) {
        $record->refund_channel = $response['channel'];
    }
    if (!empty($response['refund_id'])) {
        $record->refund_trade_no = $response['refund_id'];
    }
    if (!empty($response['amount']['total'])) {
        $record->order_amount = fentoyuan($response['amount']['total']);
    }
    if (!empty($response['amount']['refund'])) {
        $record->refund_amount = fentoyuan($response['amount']['refund']);
    }
    if (!empty($response['user_received_account'])) {
        $record->refund_account = $response['user_received_account'];
    }
    if ($refundStatus == 1) {
        $record->refund_time = time();
    }
    $record->save();
    }

    public function handleparams(array|null $params = null, string|null $scene = 'add'): void
    {
    if ($scene == 'add') {
        if (empty($params['mch_id'])) {
            throw new \ba\Exception('商户ID为空');
        }
        if (empty($params['config']['api_type'])) {
            throw new \ba\Exception('支付产品类型为空');
        }
        if (!in_array($params['config']['api_type'], $this->apiTypes)) {
            throw new \ba\Exception('不支持该产品类型');
        }
        $merchant = \app\admin\model\merchant\Merchant::find($params['mch_id']);
        if (empty($merchant)) {
            throw new \ba\Exception('商户信息不存在');
        }
        $this->account->save();
        if ($merchant->source == 'downstream') {
            $this->account->source_type = 'down';
            $downId = $merchant->merchant_info['down_id'] ?? null;
            if (empty($downId)) {
                throw new \ba\Exception('下游商户ID不存在');
            }
            $this->account->source_id = $downId;
        }
        $this->account->save();
    }
    }

    public function handledelete(): void
    {
    return;
    }

    public function verifysign(): array
    {
    $this->init();
    $requestTool = new \app\common\library\wechat\RequestTool($this->entryChannel->config);
    if ($requestTool->verifySign()) {
        return $requestTool->getContentDecryption();
    }
    throw new \ba\Exception($requestTool->getErrorMsg());
    }

    private function buildextraparams(\app\admin\model\Order|null $order = null): array
    {
    switch ($this->account->config['api_type']) {
        case 'JSAPI':
            $extend = $order->extend;
            return ['payer' => ['sp_openid' => $extend['sp_openid']]];
    }
    return [];
    }

    private function init(): void
    {
    $merchant = \app\admin\model\merchant\Merchant::find($this->account->mch_id);
    if (empty($merchant)) {
        throw new \ba\Exception('特约商户不存在');
    }
    if ($merchant->status != 1) {
        throw new \ba\Exception('商户状态异常');
    }
    if ($merchant->user_id != $this->account->user_id) {
        throw new \ba\Exception('商户与通道用户信息不一致');
    }
    if (empty($merchant->merchant_id)) {
        throw new \ba\Exception('商户号为空');
    }
    $entryChannel = \app\admin\model\EntryChannel::find($merchant->ec_id);
    if (empty($entryChannel)) {
        throw new \ba\Exception('商户所在的进件通道不存在');
    }
    if ($entryChannel->status != 1) {
        throw new \ba\Exception('服务商状态异常');
    }
    if (empty($entryChannel->config['appId'])) {
        throw new \ba\Exception('服务商APPID不存在');
    }
    if (empty($entryChannel->config['mchId'])) {
        throw new \ba\Exception('服务商商户号为空');
    }
    $this->entryChannel = $entryChannel;
    $this->merchant = $merchant;
    $this->sp_appid = $entryChannel->config['appId'];
    $this->sp_mchid = $entryChannel->config['mchId'];
    $this->sub_mchid = $merchant->merchant_id;
    if (!empty($merchant->merchant_info['appId'])) {
        $this->sub_appid = $merchant->merchant_info['appId'];
    }
    try {
        $requestTool = new \app\common\library\wechat\RequestTool($entryChannel->config);
        $this->requestTool = $requestTool;
        $this->platformCertPath = $requestTool->getPlatformCertPath();
        // [opcode 对照] 原件字面量即 'getPlatformCertSeriao'（双字面量对均为该拼写，原代码如此）；RequestTool 实定义方法名为 getPlatformCertSerial
        $this->platformCertSerial = $requestTool->getPlatformCertSeriao();
        $this->instance = $requestTool->getInstance();
    } catch (\Exception $e) {
        throw new \ba\Exception($e->getMessage());
    }
    }

}
