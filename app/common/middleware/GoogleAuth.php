<?php

// 还原自 Swoole Compiler 加密文件: GoogleAuth.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/middleware/GoogleAuth.php

namespace app\common\middleware;

class GoogleAuth{
    public function handle($request, \Closure|null $next = null): mixed
    {
        if (get_sys_config('is_totp') != 'open') {
            return $next($request);
        }
        $token = $request->header('ba-user-token');
        if (empty($token)) {
            return self::res('请先登录', 303);
        }
        $auth = new \app\common\library\Auth();
        $auth->init($token);
        if (!$auth->isLogin()) {
            return self::res('请先登录', 303);
        }
        $user = $auth->getUser();
        if ($user->totp_auth_status != '1') {
            return $next($request);
        }
        $sessionTime = \think\facade\Session::get('user_google_auth', 0);
        if (0 < $sessionTime && time() - $sessionTime < 1800) {
            return $next($request);
        }
        return self::res('请先完成TOTP身份验证', 407);
    }
    private static function res(string|null $msg = null, int|null $code = null): \think\response\Json
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => null,
        ]);
    }
}
