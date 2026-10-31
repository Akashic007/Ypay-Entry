<?php

// 还原自 Swoole Compiler 加密文件: TotpAuth.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/routine/admin/TotpAuth.php

namespace app\admin\controller\routine\admin;

class TotpAuth extends \app\common\controller\Backend {
    public function initialize(): void
    {
        parent::initialize();
    }

    public function gettotpqrcode(): void
    {
        $google   = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        $secret   = $google->generateSecret();
        $siteName = get_sys_config('site_name');
        $url      = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate($siteName . '-admin-' . $this->auth->username, $secret);
        $this->success('ok', ['secret' => $secret, 'qrCodeUrl' => $url]);
    }

    public function bindtotp(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        // [opcode 对照] LIT9 数组经 RAW0 清零后仅剩 ['operate','code']，方法体实际读取 $data['secret'] 与 $data['password']，按语义补全字段
        $data = $this->request->post(['operate', 'secret', 'code', 'password']);
        if ($data['operate'] != 'bind' && $data['operate'] != 'unBind') {
            $this->error('操作错误');
        }
        $admin = $this->auth->getAdmin();
        if (!verify_password($data['password'], $admin->password)) {
            $this->error('用户密码错误');
        }
        if ($data['operate'] == 'bind') {
            if (empty($data['secret']) || empty($data['code'])) {
                $this->error('参数错误');
            }
            if (!empty($admin->extend['totp']['secret'])) {
                $this->error('已绑定TOTP');
            }
            $google = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
            if (!$google->checkCode($data['secret'], $data['code'])) {
                $this->error('验证失败');
            }
            $update = ['totp' => ['status' => 0, 'secret' => $data['secret']]];
            if (!$admin->setextend($admin->id, $update)) {
                $this->error('绑定失败');
            }
        } else {
            if (empty($admin->extend['totp']['secret'])) {
                $this->error('暂未绑定TOTP');
            }
            $update = ['totp' => ['status' => 0, 'secret' => '']];
            if (!$admin->setextend($admin->id, $update)) {
                $this->error('解绑失败');
            }
        }
        $this->success('操作成功');
    }

    public function settotpstatus(): void
    {
        $data  = $this->request->param(['status']);
        $admin = $this->auth->getAdmin();
        if (empty($admin->extend['totp']['secret'])) {
            $this->error('请先绑定TOTP');
        }
        $update = ['totp' => ['status' => $data['status'] ?? 0]];
        if (!$admin->setextend($admin->id, $update)) {
            $this->error('设置失败');
        }
        $this->success('设置成功');
    }

}
