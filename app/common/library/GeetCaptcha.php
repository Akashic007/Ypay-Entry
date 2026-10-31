<?php

// 还原自 Swoole Compiler 加密文件: GeetCaptcha.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/library/GeetCaptcha.php
//
// [2026-10-01 对照还原] 修复前构造器不赋值、validate 顶部恒 return 失败（断链垃圾）。
// 依据：opcode 可见真值字面量（'请先完成安全验证'、'极验服务异常，已降级放行'、
// 必填字段表 ['lot_number', ?, 'captcha_output', ?]、url 拼接 apiServer.'?captcha_id='.id、
// result==='success' 判定）+ 极验 v4 服务端二次校验标准协议（gcaptcha4.geetest.com/validate，
// sign_token = hash_hmac('sha256', lot_number, captcha_key)，POST JSON）。
// captcha_config 当前 type=geet 时本类从未被执行过（login 的 CaptchaService 调用块同时丢失），
// 故协议细节无法 opcode 逐条对照，按上述证据链重建。

namespace app\common\library;

class GeetCaptcha
{
    protected string $captchaId;
    protected string $captchaKey;
    protected string $apiServer = 'http://gcaptcha4.geetest.com/validate';

    public function __construct(string $captchaId, string $captchaKey)
    {
        $this->captchaId  = $captchaId;
        $this->captchaKey = $captchaKey;
    }

    protected function generatesigntoken(string|null $lotNumber = null): string
    {
        return hash_hmac('sha256', $lotNumber, $this->captchaKey);
    }

    public function validate(array|null $data = null): array
    {
        $data     = $data ?: request()->param();
        $required = ['lot_number', 'pass_token', 'captcha_output', 'gen_time'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'msg' => '请先完成安全验证'];
            }
        }

        $post = [
            'lot_number'     => $data['lot_number'],
            'captcha_output' => $data['captcha_output'],
            'pass_token'     => $data['pass_token'],
            'gen_time'       => $data['gen_time'],
            'sign_token'     => $this->generatesigntoken($data['lot_number']),
        ];
        $url = $this->apiServer . '?captcha_id=' . $this->captchaId;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $body    = curl_exec($ch);
            $httpOk  = curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200;
            curl_close($ch);
            $response = $httpOk && $body ? (json_decode($body, true) ?: []) : ['status' => 0];
        } catch (Throwable $e) {
            $response = ['status' => 0];
        }

        // 极验服务不可达/异常 → 降级放行，不阻塞正常用户
        if (($response['status'] ?? 1) == 0 || empty($response['result'])) {
            return ['success' => true, 'msg' => '极验服务异常，已降级放行', 'error' => $response['msg'] ?? null];
        }

        if ($response['result'] !== 'success') {
            return ['success' => false, 'msg' => $response['msg'] ?? ($response['reason'] ?? '验证失败'), 'data' => $response];
        }
        return ['success' => true, 'msg' => '', 'data' => $response];
    }
}
