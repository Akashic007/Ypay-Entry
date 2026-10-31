<?php

// 还原自 Swoole Compiler 加密文件: Common.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Common.php

namespace app\api\controller;

class Common extends \app\common\controller\Api {
    public function captcha(): \think\Response
    {
        // 用扩展类 ba\Captcha 输出图形验证码（as_captcha 表已确认存在）。
        return (new \ba\Captcha((array) config('ba.captcha')))->entry($this->request->param('id', ''));
    }

    public function getcaptchaconfig(): void
    {
        $config = get_sys_config('captcha_config') ?: [];
        if (isset($config['geetCaptchaKey'])) {
            unset($config['geetCaptchaKey']);
        }
        $this->success('OK', $config);
    }

    public function clickcaptcha(): void
    {
        $id = $this->request->request('id/s');
        $captcha = new \ba\ClickCaptcha();
        $this->success('', $captcha->creat($id));
    }

    public function checkclickcaptcha(): void
    {
        $id = $this->request->request('id/s');
        $info = $this->request->post('info/s');
        $unset = $this->request->post('unset/b', false);
        $captcha = new \ba\ClickCaptcha();
        if ($captcha->check($id, $info, $unset)) {
            $this->success();
        }
        $this->error();
    }

    public function refreshtoken(): void
    {
        $refreshToken = $this->request->post('refreshToken');
        $refreshToken = \app\common\facade\Token::get($refreshToken);
        if (!$refreshToken || $refreshToken['expire_time'] < time()) {
            $this->error(__('Login expired, please login again.'));
        }
        $uuid = \ba\Random::uuid();
        if ($refreshToken['type'] == \app\admin\library\Auth::TOKEN_TYPE . '-refresh') {
            \app\common\facade\Token::set($uuid, \app\admin\library\Auth::TOKEN_TYPE, $refreshToken['user_id'], (int) \think\facade\Config::get('buildadmin.admin_token_keep_time'));
        }
        if ($refreshToken['type'] == \app\common\library\Auth::TOKEN_TYPE . '-refresh') {
            \app\common\facade\Token::set($uuid, \app\common\library\Auth::TOKEN_TYPE, $refreshToken['user_id'], (int) \think\facade\Config::get('buildadmin.user_token_keep_time'));
        }
        $this->success('', ['type' => $refreshToken['type'], 'token' => $uuid]);
    }

    public function qrcode(): \think\Response
    {
        if (preg_match('/data=(.*)/', request()->url(true), $matches)) {
            $qr = $matches[1];
        }
        $result = \Endroid\QrCode\Builder\Builder::create()
            ->writer(new \Endroid\QrCode\Writer\PngWriter())
            ->data($qr)
            ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
            ->size(400)
            ->margin(0)
            ->build();
        return \think\Response::create($result->getString())->contentType('image/png');
    }

}
