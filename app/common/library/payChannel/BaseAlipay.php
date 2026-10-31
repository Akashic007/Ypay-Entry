<?php

// 还原自 Swoole Compiler 加密文件: BaseAlipay.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/payChannel/BaseAlipay.php

namespace app\common\library\payChannel;

abstract class BaseAlipay{
    protected function init(array|null $config = null): void
    {
        if (($config['mode'] ?? '') != 'cert') {
            $config['mode'] = 'key';
        }
        if (empty($config['appId'])) {
            throw new \ba\Exception('支付宝应用ID为空');
        }
        if (empty($config['appPrivateKey'])) {
            throw new \ba\Exception('支付宝应用私钥为空');
        }
        if (isset($config['mode']) && $config['mode'] == 'key') {
            if (empty($config['alipayPublicKey'])) {
                throw new \ba\Exception('支付宝公钥为空');
            }
        } else {
            if (empty($config['appCertPem']) || empty($config['alipayPublicCertPem']) || empty($config['alipayRootCert'])) {
                throw new \ba\Exception('缺乏必要证书文件');
            }
        }
        $configObj = new \Alipay\EasySDK\Kernel\Config();
        $configObj->protocol = 'https';
        $configObj->gatewayHost = 'openapi.alipay.com';
        $configObj->signType = 'RSA2';
        $configObj->appId = $config['appId'];
        $configObj->merchantPrivateKey = $config['appPrivateKey'];
        if ($config['mode'] == 'key') {
            $configObj->alipayPublicKey = $config['alipayPublicKey'];
        } else {
            $certPath = root_path('runtime') . DIRECTORY_SEPARATOR . 'upload';
            $configObj->alipayCertPath = $certPath . $config['alipayPublicCertPem'];
            $configObj->alipayRootCertPath = $certPath . $config['alipayRootCert'];
            $configObj->merchantCertPath = $certPath . $config['appCertPem'];
        }
        if (!empty($config['encryptKey'])) {
            $configObj->encryptKey = $config['encryptKey'];
        }
        $configObj->notifyUrl = $this->getNotifyUrl();
        \Alipay\EasySDK\Kernel\Factory::setOptions($configObj);
    }

    protected function getnotifyurl(): string
    {
        return request()->domain() . '/notify/pay/alipaySign/payCallback';
    }

}
