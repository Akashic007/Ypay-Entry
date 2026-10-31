<?php

// 还原自 Swoole Compiler 加密文件: GoogleAuth.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/user/GoogleAuth.php

namespace app\api\controller\user;

class GoogleAuth extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['test'];
    public function initialize(): void
    {
        parent::initialize();
    }

    public function getgoogleauthqrcode(): void
    {
        if (get_sys_config('is_totp') == 'close') {
            $this->error('暂未开启TOTP安全验证');
        }

        $ga = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        $secret = $ga->generateSecret();
        $siteName = get_sys_config('site_name');

        $qrCodeUrl = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($siteName . '-' . $this->auth->username, $secret);

        $this->success('ok', [
            'secret'    => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    public function bindgoogleauth(): void
    {
        if (!request()->isPost()) {
            $this->error('请求方式错误');
        }

        if (get_sys_config('is_totp') == 'close') {
            $this->error('暂未开启TOTP安全验证');
        }

        $post = request()->only(['code', 'secret', 'password']);
        if (empty($post['secret']) || empty($post['code']) || empty($post['password'])) {
            $this->error('参数错误');
        }

        if (!verify_password($post['password'], $this->auth->password)) {
            $this->error('用户密码错误');
        }

        $ga = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        if (!$ga->checkCode($post['secret'], $post['code'])) {
            $this->error('验证失败');
        }

        $model = \app\admin\model\user\Config::where('user_id', $this->auth->id)->find();
        if (empty($model)) {
            $model = new \app\admin\model\user\Config();
            $model->user_id = $this->auth->id;
        }
        $model->google_auth = [
            'secret' => $post['secret'],
            'status' => 1,
        ];
        $model->save();

        $this->success('绑定成功');
    }

    public function unbindgoogleauth(): void
    {
        if (!request()->isPost()) {
            $this->error('错误的请求方式');
        }

        $post = request()->only(['password']);
        if (!verify_password($post['password'], $this->auth->password)) {
            $this->error('用户密码错误');
        }

        $model = \app\admin\model\user\Config::where('user_id', $this->auth->id)->find();
        if (empty($model)) {
            $this->error('用户未绑定');
        }

        $model->google_auth = [
            'secret' => '',
            'status' => 0,
        ];
        $model->save();

        $this->success('解除成功');
    }

    public function setgoogleauthstatus(): void
    {
        $post = request()->only(['status']);
        if (!in_array($post['status'], [0, 1])) {
            $this->error('参数错误');
        }

        if (get_sys_config('is_totp') == 'close') {
            $this->error('暂未开启TOTP安全验证');
        }

        $model = \app\admin\model\user\Config::where('user_id', $this->auth->id)->find();
        if (empty($model)) {
            $this->error('用户未绑定');
        }

        if ($model->google_auth['status'] == $post['status']) {
            $this->error('状态未改变');
        }

        if ($post['status'] == 1 && empty($model->google_auth['secret'])) {
            $this->error('请先绑定');
        }

        $model->google_auth = [
            'secret' => $model->google_auth['secret'],
            'status' => $post['status'],
        ];
        $model->save();

        $this->success('设置成功');
    }

    public function verifygooglecode(): void
    {
        $post = request()->only(['code']);
        if (empty($post['code'])) {
            $this->error('请输入验证码');
        }

        $model = \app\admin\model\user\Config::where('user_id', $this->auth->id)->find();
        if (empty($model) || empty($model->google_auth['secret'])) {
            $this->error('用户未绑定');
        }

        $ga = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        if ($ga->checkCode($model->google_auth['secret'], $post['code'])) {
            \think\facade\Session::set('user_google_auth', time());
            $this->success('验证成功');
        }

        $this->error('验证失败');
    }
}
