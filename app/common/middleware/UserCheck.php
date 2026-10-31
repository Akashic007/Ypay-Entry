<?php

// 还原自 Swoole Compiler 加密文件: UserCheck.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/middleware/UserCheck.php
// ⚠️ 语义重建：原始方法体断链（残留片段：Auth 实例化、LOGIN_RESPONSE_CODE、'open'/'close'
// 配置比对、try/catch Throwable）。按与 Frontend::initialize 同款的登录门禁语义重建：
// 从请求 token 初始化会员登录态，未登录返回 NEED_LOGIN（code=LOGIN_RESPONSE_CODE）。
// 片段中的 'open' 比对疑似与用户订阅/TOTP 强校验相关，opcode 还原后必须覆盖！

namespace app\common\middleware;

use app\common\library\Auth;
use app\common\library\token\TokenExpirationException;

class UserCheck{
    public function handle(\think\Request|null $request = null, \Closure|null $next = null)
    {
        try {
            $auth  = Auth::instance();
            $token = get_auth_token(['ba', 'user', 'token']);
            if ($token) $auth->init($token);
        } catch (TokenExpirationException) {
            $this->fail();
        }

        if (!$auth->isLogin()) {
            $this->fail();
        }

        return $next($request);
    }

    private function fail(): void
    {
        $response = \think\Response::create([
            'code' => Auth::LOGIN_RESPONSE_CODE,
            'msg'  => __('Please login first'),
            'time' => time(),
            'data' => ['type' => Auth::NEED_LOGIN],
        ], 'json');
        throw new \think\exception\HttpResponseException($response);
    }
}
