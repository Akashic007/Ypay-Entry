<?php

// 还原自 Swoole Compiler 加密文件: Frontend.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/controller/Frontend.php
// 本类为 BuildAdmin 框架件，方法体按 BuildAdmin 原版 initialize 重建（opcode 残片
// static::NEED_LOGIN / LOGIN_RESPONSE_CODE / ['type'=>…] / '/' 路径拼接与之完全吻合），
// 属性默认值为 opcode 真值。YPay 对本类无自有扩展。

namespace app\common\controller;

use app\common\library\Auth;
use app\common\library\token\TokenExpirationException;
use think\facade\Event;

class Frontend extends Api {
    protected array $noNeedLogin = [];
    protected array $noNeedPermission = [];
    protected \app\common\library\Auth $auth;
    public function initialize(): void
    {
        parent::initialize();

        $needLogin = !action_in_arr($this->noNeedLogin);

        try {
            // 初始化会员鉴权实例
            $this->auth = Auth::instance();
            $token      = get_auth_token(['ba', 'user', 'token']);
            if ($token) $this->auth->init($token);
        } catch (TokenExpirationException) {
            if ($needLogin) {
                $this->error(__('Token expiration'), [], 409);
            }
        }

        if ($needLogin) {
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'), [
                    'type' => $this->auth::NEED_LOGIN,
                ], $this->auth::LOGIN_RESPONSE_CODE);
            }
            if (!action_in_arr($this->noNeedPermission)) {
                $routePath = ($this->app->request->controllerPath ?? '') . '/' . $this->request->action(true);
                if (!$this->auth->check($routePath)) {
                    $this->error(__('You have no permission'), [], 401);
                }
            }
        }

        // 会员验权和登录标签位
        Event::trigger('frontendInit', $this->auth);
    }
}
