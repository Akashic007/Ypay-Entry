<?php

// 还原自 Swoole Compiler 加密文件: AesEncrypt.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/AesEncrypt.php

namespace app\common\library;

class AesEncrypt{
    private string $key;
    private string $cipher = 'aes-256-gcm';
    // [2026-10-02 opcode 对照还原] 构造器行 17: key = config('buildadmin.token.key') ?? 'zheshisuijidekeynibieguan'
    public function __construct()
    {
        $this->key = config('buildadmin.token.key') ?? 'zheshisuijidekeynibieguan';
    }

    // [2026-10-02 opcode 对照还原] 行 21-37: aes-256-gcm；输出 base64(iv[16] . tag[16] . 密文)
    public function encryptdata($data): string
    {
        $iv        = random_bytes(16);
        $json      = json_encode($data);
        $encrypted = openssl_encrypt($json, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $encrypted);
    }

    // [2026-10-02 opcode 对照还原] 行 41-66: 拆 iv/tag/密文三段 → openssl_decrypt(6参) → 失败 null → json_decode
    public function decryptdata($encrypted)
    {
        $encrypted = base64_decode($encrypted);
        $iv        = substr($encrypted, 0, 16);
        $tag       = substr($encrypted, 16, 16);
        $data      = substr($encrypted, 32);
        $decrypted = openssl_decrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($decrypted === false) {
            return null;
        }
        return json_decode($decrypted, true);
    }

}
