<?php

// 还原自 Swoole Compiler 加密文件: RealNameCheck.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/middleware/RealNameCheck.php
//
// [2026-09-13 opcode 全量对照还原] 本文件曾为临时放行实现，现按原始 opcode 指令序重建门禁语义：
//   1. 后台开关 get_sys_config('admin_force_real_name') != 'open' → 放行
//   2. isAllowPath($request->pathinfo()) 放行清单命中 → 放行
//      （清单 opcode 真值: index/login, index/logout, index/loginbefore, index/index, routine.admininfo/）
//   3. get_auth_token() 为空 → 放行（未登录请求由鉴权中间件处理）
//   4. Token::get($token) + Token::tokenExpirationCheck() 包 try/catch Throwable → 异常放行
//   5. ($adminInfo['type'] ?? '') !== Auth::TOKEN_TYPE → 放行（非管理员 token）
//   6. intval($adminInfo['user_id'] ?? 0) <= 0 → 放行
//   7. Admin::where('id',$userId)->where('status','enable')->find() 为空 → 放行
//   8. isset($admin->realName) 且 realName->status == 1（已实名）→ 放行
//   9. 其余 → 拦截: '请先完成管理员实名认证' + data{routePath:/admin/routine/adminInfo} + code 302
//      （原始为 marker 'res' 调用携带 302 与 routePath，按框架标准响应中断形态重建）

namespace app\admin\middleware;

use app\admin\library\Auth;
use app\admin\model\Admin;
use app\common\facade\Token;
use think\exception\HttpResponseException;
use think\Request;
use Throwable;

class RealNameCheck
{
    /**
     * 实名认证门禁
     */
    public function handle(Request|null $request = null, \Closure|null $next = null)
    {
        // 强制实名开关未开启 → 放行
        $forceConfig = get_sys_config('admin_force_real_name');
        if ($forceConfig != 'open') {
            return $next($request);
        }

        // 放行清单命中 → 放行
        if ($this->isAllowPath($request->pathinfo())) {
            return $next($request);
        }

        try {
            $token = get_auth_token();
            if (empty($token)) {
                return $next($request);
            }
            $adminInfo = Token::get($token);
            Token::tokenExpirationCheck($adminInfo);
        } catch (Throwable $e) {
            return $next($request);
        }

        // 非管理员类型 token → 放行（本中间件只管管理员实名）
        if (($adminInfo['type'] ?? '') !== Auth::TOKEN_TYPE) {
            return $next($request);
        }

        $userId = intval($adminInfo['user_id'] ?? 0);
        if ($userId <= 0) {
            return $next($request);
        }

        $admin = Admin::where('id', $userId)->where('status', 'enable')->find();
        if (empty($admin)) {
            return $next($request);
        }

        // 已实名 → 放行
        if (isset($admin->realName) && $admin->realName->status == 1) {
            return $next($request);
        }

        // 未实名 → 302 指向实名信息页
        $response = json([
            'code' => 302,
            'msg'  => '请先完成管理员实名认证',
            'time' => time(),
            'data' => ['routePath' => '/admin/routine/adminInfo'],
        ]);
        throw new HttpResponseException($response);
    }

    /**
     * 实名门禁放行清单
     */
    protected function isAllowPath($path): bool
    {
        $path = strtolower(str_replace('\\', '/', $path));
        $allowList = [
            'index/login',
            'index/logout',
            'index/loginbefore',
            'index/index',
            'routine.admininfo/',
        ];
        foreach ($allowList as $item) {
            if (str_contains($path, $item)) {
                return true;
            }
        }
        return false;
    }
}
