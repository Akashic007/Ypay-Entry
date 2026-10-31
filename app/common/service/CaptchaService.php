<?php

// 还原自 Swoole Compiler 加密文件: CaptchaService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/CaptchaService.php
//
// [2026-10-01 opcode 全量对照还原] 修复前 __construct/check 均为断链垃圾（$T1/$T5 未定义）。
// opcode 真值（构造器行 25-39 / check 行 46-64）：
//   __construct: get_sys_config('captcha_config') ?? []；empty(config)||empty(type) → type='img'；
//                type=='geet' 时校验 geetCaptchaId/geetCaptchaKey（缺失抛 '极验ID配配置'/'极验KEY配配置'）；
//   check():     $params = request()->param()（无参方法，自取请求）；
//                switch($this->type)：img → 空 captcha/'验证码不能为空'、空 captchaId/'验证码ID不能为空'、
//                \ba\Captcha::check(captcha, captchaId) 失败抛 '验证码错误'；
//                geet → GeetCaptcha(id,key)->validate($params)，!success 抛 $result['msg']。

namespace app\common\service;

class CaptchaService
{
    protected string $type = 'img';
    protected string $geet_captcha_id;
    protected string $geet_captcha_key;

    public function __construct()
    {
        $config = get_sys_config('captcha_config') ?? [];
        if (empty($config) || empty($config['type'])) {
            $config['type'] = 'img';
        }
        if ($config['type'] == 'geet') {
            if (empty($config['geetCaptchaId'])) {
                throw new \ba\Exception('极验ID配配置');
            }
            if (empty($config['geetCaptchaKey'])) {
                throw new \ba\Exception('极验KEY配配置');
            }
            $this->geet_captcha_id  = $config['geetCaptchaId'];
            $this->geet_captcha_key = $config['geetCaptchaKey'];
        }
        $this->type = $config['type'];
    }

    public function check(): void
    {
        $params = request()->param();
        switch ($this->type) {
            case 'img':
                if (empty($params['captcha'])) {
                    throw new \ba\Exception('验证码不能为空');
                }
                if (empty($params['captchaId'])) {
                    throw new \ba\Exception('验证码ID不能为空');
                }
                $captcha = new \ba\Captcha();
                if (!$captcha->check($params['captcha'], $params['captchaId'])) {
                    throw new \ba\Exception('验证码错误');
                }
                break;
            case 'geet':
                $geet   = new \app\common\library\GeetCaptcha($this->geet_captcha_id, $this->geet_captcha_key);
                $result = $geet->validate($params);
                if (!$result['success']) {
                    throw new \ba\Exception($result['msg']);
                }
                break;
        }
    }
}
