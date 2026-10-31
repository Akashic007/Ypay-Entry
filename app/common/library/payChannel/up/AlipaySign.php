<?php

// 还原自 Swoole Compiler 加密文件: AlipaySign.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/up/AlipaySign.php

namespace app\common\library\payChannel\up;

class AlipaySign extends \app\common\library\payChannel\up\UpPayChannel implements \app\common\service\channel\contract\AlipaySignInterface {
    public function pay(\app\admin\model\Order|null $order = null): \app\common\service\channel\PayInfo
    {
        $payInfo = parent::unifiedPay($order);
        $payInfoObj = new \app\common\service\channel\PayInfo();
        if (!empty($payInfo['pay_info']['qrcode'])) {
            $payInfoObj->setQrcode($payInfo['pay_info']['qrcode']);
        }
        if (!empty($payInfo['pay_info']['h5_url'])) {
            $payInfoObj->setH5Url($payInfo['pay_info']['h5_url']);
        } else {
            $payInfoObj->setH5Url('alipays://platformapi/startapp?saId=10000007');
        }
        return $payInfoObj;
    }

}
