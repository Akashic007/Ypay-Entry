<?php

// 还原自 Swoole Compiler 加密文件: RequestTool.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/wechat/RequestTool.php

namespace app\common\library\wechat;

class RequestTool{
    protected \WeChatPay\BuilderChainable $instance;
    protected array $config;
    protected string $platformCertSerial;
    protected string $platformCertPath;
    protected string $errorMsg = '';
    public function __construct(array $config)
    {
        if (empty($config['appId']) || empty($config['mchId'])) {
            throw new \ba\Exception('服务商信息错误');
        }
        $this->config = $config;
        $pemPath = root_path() . 'upload';
        try {
            $certTool = new PlatformCertTool();
            $cert = $certTool->getplatformcert($config);
        } catch (\ba\Exception $e) {
            throw new \ba\Exception('获取平台证书失败：' . $e->getMessage());
        }
        $this->platformCertSerial = $cert['serial'];
        $this->platformCertPath = $cert['cert_path'];
        $this->instance = \WeChatPay\Builder::factory([
            'mchid' => $config['mchId'],
            'serial' => $config['merchantSerial'],
            'privateKey' => \WeChatPay\Crypto\Rsa::from(file_get_contents($pemPath . $config['apiKeyPem'])),
            'certs' => [
                $cert['serial'] => \WeChatPay\Crypto\Rsa::from(file_get_contents($cert['cert_path']), \WeChatPay\Crypto\Rsa::KEY_TYPE_PUBLIC),
            ],
        ]);
    }

    public function getinstance(): \WeChatPay\BuilderChainable
    {
        return $this->instance;
    }

    // 注意：本方法定义为 getPlatformCertSerial（opcode 真值）。
    // payChannel/WxpayOfficialSpecial 加密原件中调用的是 getPlatformCertSeriao()（拼写错误，同为真值），
    // 双方均按原件保留，勿"修正"任一侧。
    public function getplatformcertserial(): string
    {
        return $this->platformCertSerial;
    }

    public function getplatformcertpath(): string
    {
        return $this->platformCertPath;
    }

    public function geterrormsg(): string
    {
        return $this->errorMsg;
    }

    public function verifysign(): bool
    {
        try {
            $certificate = \WeChatPay\Crypto\Rsa::from(file_get_contents($this->platformCertPath), \WeChatPay\Crypto\Rsa::KEY_TYPE_PUBLIC);
            $headers = request()->header();
            $content = request()->getContent();
            $inTime = abs(\WeChatPay\Formatter::timestamp() - (int) $headers['wechatpay-timestamp']) <= 300;
            $verified = \WeChatPay\Crypto\Rsa::verify(
                \WeChatPay\Formatter::joinedByLineFeed($headers['wechatpay-timestamp'], $headers['wechatpay-nonce'], $content),
                $headers['wechatpay-signature'],
                $certificate
            );
            return $inTime && $verified;
        } catch (\Exception $e) {
            $this->errorMsg = '构造验证签名失败：' . $e->getMessage();
            return false;
        }
    }

    public function getcontentdecryption(): array
    {
        $response = request()->getContent();
        $data = (array) json_decode($response, true);
        $ciphertext = $data['resource']['ciphertext'];
        $nonce = $data['resource']['nonce'];
        $associatedData = $data['resource']['associated_data'];
        $decrypted = \WeChatPay\Crypto\AesGcm::decrypt($ciphertext, $this->config['apiV3Key'], $nonce, $associatedData);
        return (array) json_decode($decrypted, true);
    }

}
