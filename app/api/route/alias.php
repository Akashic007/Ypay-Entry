<?php

// [2026-10-01] SPA bundle（BuildAdmin 通用模板）URL 与 YPay 后端方法名的适配别名
//
// 商户端 SPA bundle 中存在少量 BuildAdmin 模板残留 URL，在 YPay 后端加密件中无对应
// 控制器/方法。经 bundle 反查（public/assets/index-D_-bKQLd.js，全量 64 个 API URL
// 逐条与后端控制器方法对照），以下两个 URL 是真实调用路径（登录页验证码 / 进件页
// 富文本上传回退地址），按本文件别名路由适配；user/list、role/list 为管理端模板
// 残留（商户端无 UI 入口），有意不适配。
//
// login/logout、login/getUserTotpStatus 已按 opcode 原版还原 Login 控制器方法
// （见 app/api/controller/Login.php），走常规多应用分发，无需别名。

use think\facade\Route;

// SPA 登录页验证码: /api/auth/captcha?captchaId=xxx → Common::captcha
Route::rule('auth/captcha', 'Common/captcha', 'GET');

// SPA 富文本上传回退地址: /api/common/upload/wangeditor（bundle 中 uploadConfig.server
// 缺省时回退）→ Ajax::upload；route_complete_match=true，需覆盖带子段的变体
Route::rule('common/upload', 'Ajax/upload', 'POST');
Route::rule('common/upload/:extra', 'Ajax/upload', 'POST');
