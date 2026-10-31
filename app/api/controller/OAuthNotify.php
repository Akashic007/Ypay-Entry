<?php

// 还原自 Swoole Compiler 加密文件: OAuthNotify.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/OAuthNotify.php

namespace app\api\controller;

class OAuthNotify extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['*'];
    public function qqofficial(): \think\response\Redirect
    {
        $params = $this->request->param();
        if (empty($params['code'])) {
            return redirect('/login?message=登录失败');
        }
        try {
            $oauth = \app\common\service\oAuth\OAuthFactory::create('qq');
            $result = $oauth->handleCallback($params);
        } catch (\Exception $e) {
            return redirect('/login?message=请求异常');
        }
        if (isset($result['error'])) {
            return redirect('/login?message=登录失败');
        }
        if (isset($params['token'])) {
            $params['uid'] = $result['openid'];
            $params['type'] = 'qq';
            $params['content'] = $result;
            return redirect($this->handleBind($params));
        } else {
            $params['type'] = 'qq';
            $params['uid'] = $result['openid'];
            $params['group'] = 'qq';
            $params['content'] = $result;
            return redirect($this->handleLogin($params));
        }
    }

    public function aggregation(): \think\response\Redirect
    {
        $params = $this->request->param();
        try {
            $oauth = \app\common\service\oAuth\OAuthFactory::create($params['type']);
            $result = $oauth->handleCallback($params);
        } catch (\Exception $e) {
            return redirect('/login?message=请求异常');
        }
        if (!isset($result['code']) || $result['code'] != '0') {
            return redirect('/login?message=登录失败');
        }
        $log = new \app\admin\model\oauth\Log();
        if (isset($params['token'])) {
            $params['type'] = $result['type'];
            $params['uid'] = $result['social_uid'];
            $params['content'] = $result;
            return redirect($this->handleBind($params));
        } else {
            $params['type'] = $result['type'];
            $params['uid'] = $result['social_uid'];
            $params['group'] = 'aggregation';
            $params['content'] = $result;
            return redirect($this->handleLogin($params));
        }
    }

    private function handlelogin(array|null $params = null): string
    {
        $logModel = new \app\admin\model\oauth\Log();
        $oauth = $logModel->where(['type' => $params['type'], 'uid' => $params['uid'], 'status' => 1])->find();
        if ($oauth && !empty($oauth->user_id)) {
            $user = $this->auth->direct($oauth->user_id);
            if (!$user) {
                $logModel->where('uid', $params['uid'])->delete();
            } else {
                $logModel->save([
                    'user_id' => $this->auth->id,
                    'type' => $params['type'],
                    'uid' => $params['uid'],
                    'content' => $params['content'],
                    'login_ip' => $this->request->ip(),
                    'status' => 1,
                ]);
                \think\facade\Event::trigger(new \app\common\event\UserLogin($this->auth->getUser(), $params['type']));
                return '/login?token=' . $this->auth->getToken() . '&message=登录成功';
            }
        }
        // [opcode 对照] op35-36（$user=false 后恒假跳转）为不可达死代码，此处省略不影响行为
        $aes = new \app\common\library\AesEncrypt();
        $token = $aes->encryptData([
            'uid' => $params['uid'],
            'type' => $params['type'],
            'group' => $params['group'],
            'content' => $params['content'],
        ]);
        \think\facade\Cookie::set('oauth_info', $token, 360);
        return '/register?message=请先注册用户！';
    }

    private function handlebind($params): string
    {
        $logModel = new \app\admin\model\oauth\Log();
        $this->auth->init($params['token']);
        if (!$this->auth->isLogin()) {
            return '/login?message=登录信息已失效';
        }
        $row = $logModel->where(['user_id' => $this->auth->id, 'type' => $params['type'], 'status' => 1])->find();
        if ($row) {
            return '/tablerloginlock?message=用户已经绑定！';
        }
        $bind = $logModel->where([
            ['user_id', '<>', $this->auth->id],
            ['type', '=', $params['type']],
            ['uid', '=', $params['uid']],
        ])->find();
        if ($bind) {
            return '/tablerloginlock?message=已被其他用户绑定！';
        }
        $logModel->save([
            'user_id' => $this->auth->id,
            'type' => $params['type'],
            'uid' => $params['uid'],
            'content' => $params['content'],
            'login_ip' => $this->request->ip(),
            'status' => 1,
        ]);
        return '/tablerloginlock?message=绑定成功！';
    }

}
