<?php

// 还原自 Swoole Compiler 加密文件: PlatformCertTool.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/wechat/PlatformCertTool.php

namespace app\common\library\wechat;

class PlatformCertTool{
    const WXPAY_API_BASE = 'https://api.mch.weixin.qq.com';
    const KEY_LENGTH_BYTE = 32;
    const AUTH_TAG_LENGTH_BYTE = 16;
    public function getplatformcert(array|null $config = null): array
    {
        // opcode: root_path().'runtime'.DS.'wechat'.DS.'cert'.DS.mchId.DS.'wechat_platform_cert.pem'（L36）
        $certPath = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'wechat' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . $config['mchId'] . DIRECTORY_SEPARATOR . 'wechat_platform_cert.pem';
        $cacheKey = 'wechat_platform_cert_' . $config['mchId'];
        if (\think\facade\Cache::has($cacheKey)) {
            $cached = \think\facade\Cache::get($cacheKey);
            return [
                'cert_path' => $certPath,
                'serial' => $cached,
            ];
        }
        $downloadConfig = [
            'api_v3_key' => $config['apiV3Key'],
            'mch_id' => $config['mchId'],
            'private_key_path' => root_path() . 'upload' . $config['apiKeyPem'],
            'serial_no' => $config['merchantSerial'],
            'output_path' => $certPath,
        ];
        $result = $this->downloadcertificate($downloadConfig);
        if ($result['status'] == 1) {
            \think\facade\Cache::set($cacheKey, $result['serial_no'], 43200);
            return [
                'cert_path' => $certPath,
                'serial' => $result['serial_no'],
            ];
        }
        throw new \ba\Exception($result['msg']);
    }
    private function downloadcertificate(array|null $config = null): array
    {
        try {
            $this->validateconfig($config);
            $certificates = $this->getcertificates($config);
            $certificate = $this->getcertificate($certificates);
            if (!$certificate) {
                throw new \ba\Exception('暂无可用的平台证书');
            }
            $certContent = $this->decryptcertificate($certificate['encrypt_certificate'], $config);
            $saved = $this->savecertificate($config['output_path'], $certContent);
            if (!$saved) {
                throw new \RuntimeException('保存证书文件失败');
            }
            return [
                'status' => 1,
                'msg' => '下载平台证书成功',
                'output_path' => $config['output_path'],
                'serial_no' => $certificate['serial_no'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }
    private function validateconfig(array|null $config = null): void
    {
        // opcode 真值数组（raw arData 直读）: api_v3_key, mch_id, private_key_path, serial_no, output_path
        foreach (['api_v3_key', 'mch_id', 'private_key_path', 'serial_no', 'output_path'] as $field) {
            if (empty($config[$field])) {
                throw new \InvalidArgumentException('缺少必要参数: ' . $field);
            }
        }
        if (!file_exists($config['private_key_path'])) {
            throw new \InvalidArgumentException('私钥文件不存在: ' . $config['private_key_path']);
        }
    }
    private function getcertificates(array|null $config = null): array
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => self::WXPAY_API_BASE,
            'timeout' => 10.0,
        ]);
        $timestamp = time();
        $nonce = uniqid();
        $signature = $this->generatesignature($timestamp, $nonce, $config['private_key_path']);
        $response = $client->get('/v3/certificates', [
            \GuzzleHttp\RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => sprintf(
                    'WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
                    $config['mch_id'],
                    $nonce,
                    $timestamp,
                    $config['serial_no'],
                    $signature
                ),
                'User-Agent' => 'WXPay/1.0',
            ],
            \GuzzleHttp\RequestOptions::VERIFY => true,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('API 请求失败: ' . $response->getStatusCode());
        }
        $data = json_decode($response->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('响应解析失败: ' . json_last_error_msg());
        }
        return $data['data'] ?? [];
    }
    private function getcertificate(array|null $certificates = null): null|array
    {
        if (empty($certificates)) {
            return null;
        }
        $latestCert = null;
        $latestTime = false;
        foreach ($certificates as $cert) {
            $effectiveTime = $this->parsedatetime($cert['effective_time'] ?? '');
            if ($latestTime === false || $latestTime < $effectiveTime) {
                $latestTime = $effectiveTime;
                $latestCert = $cert;
            }
        }
        return $latestCert;
    }
    private function parsedatetime(string|null $dateString = null): \DateTime
    {
        $trimmed = trim($dateString);
        if (empty($trimmed)) {
            throw new \RuntimeException('时间字符串不能为空');
        }
        $dateTime = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $trimmed);
        if ($dateTime !== false) {
            return $dateTime;
        }
        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $trimmed);
        if ($dateTime === false) {
            throw new \RuntimeException('无法解析时间字符串: ' . $trimmed);
        }
        return $dateTime;
    }
    private function decryptcertificate(array|null $encryptData = null, array|null $config = null): string
    {
        if (strlen($config['api_v3_key']) != self::KEY_LENGTH_BYTE) {
            throw new \RuntimeException('无效的 API v3 密钥长度');
        }
        $ciphertext = base64_decode($encryptData['ciphertext'] ?? '');
        $associatedData = $encryptData['associated_data'] ?? '';
        $nonce = $encryptData['nonce'] ?? '';
        if (empty($ciphertext) || empty($nonce)) {
            throw new \RuntimeException('无效的加密证书数据11');
        }
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            // [opcode 对照] opcode 真值为 return false（VERIFY_RETURN_TYPE 前 op1=false）；
            // 在 : string 签名下与原件一样会触发 TypeError，按 opcode 原样保留
            return false;
        }
        if (PHP_VERSION_ID < 70100 || !in_array('aes-256-gcm', openssl_get_cipher_methods())) {
            throw new \RuntimeException('需要PHP 7.1以上或者安装相关扩展');
        }
        $ciphertextBody = substr($ciphertext, 0, self::AUTH_TAG_LENGTH_BYTE * -1);
        $tag = substr($ciphertext, self::AUTH_TAG_LENGTH_BYTE * -1);
        return openssl_decrypt($ciphertextBody, 'aes-256-gcm', $config['api_v3_key'], OPENSSL_RAW_DATA, $nonce, $tag, $associatedData);
    }
    private function savecertificate(string|null $path = null, string|null $content = null): bool
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 493, true)) {
                throw new \RuntimeException('创建目录失败: ' . $dir);
            }
        }
        return file_put_contents($path, $content) !== false;
    }
    private function generatesignature(int|null $timestamp = null, string|null $nonceStr = null, string|null $privateKeyPath = null): string
    {
        $message = sprintf("%s\n%s\n%d\n%s\n%s\n", 'GET', '/v3/certificates', $timestamp, $nonceStr, '');
        $privateKey = file_get_contents($privateKeyPath);
        $privateKey = openssl_pkey_get_private($privateKey);
        if (!$privateKey) {
            throw new \RuntimeException('加载私钥失败');
        }
        $signature = '';
        if (!openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('签名生成失败');
        }
        return base64_encode($signature);
    }
}
