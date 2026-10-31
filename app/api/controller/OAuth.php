<?php

// 还原自 Swoole Compiler 加密文件: OAuth.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/OAuth.php

namespace app\api\controller;

class OAuth extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];
    public function initialize(): void
    {
        parent::initialize();
    }


// [opcode 还原] 三方登录取 URL：param() 全量 → 校验 method → 已登录带 token 建 OAuth → getLoginUrl
    public function getloginurl(): void
    {
        $params = $this->request->param();
        if (empty($params['method'])) {
            $this->error('登录方式为空');
        }
        try {
            if ($this->auth->isLogin()) {
                $oauth = \app\common\service\oAuth\OAuthFactory::create($params['method'], $this->auth->getToken());
            } else {
                $oauth = \app\common\service\oAuth\OAuthFactory::create($params['method']);
            }
            $url = $oauth->getLoginUrl();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('ok', ['url' => $url]);
    }


}
