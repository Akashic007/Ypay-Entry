<?php

// 还原自 Swoole Compiler 加密文件: Sms.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Sms.php

namespace app\api\controller;

class Sms extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['send'];
    public function initialize(): void
    {
        parent::initialize();
    }

    public function send()
    {
        $params = $this->request->post(['mobile', 'template_code']);
        try {
            validate([
                'mobile'        => 'require|mobile',
                'template_code' => 'require',
            ])->message([
                'mobile.require' => '手机号不能为空',
                'mobile.mobile'  => '手机号格式错误',
                'template_code'  => '验证码模板为空',
            ])->check($params);
        } catch (\think\exception\ValidateException $e) {
            $this->error($e->getMessage());
        }

        $captchaObj    = new \ba\Captcha();
        $isSendCaptcha = get_sys_config('is_send_captcha') == 'open';
        if ($isSendCaptcha) {
            try {
                $captcha = new \app\common\service\CaptchaService();
                $captcha->check();
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $captcha              = $captchaObj->getCaptchaData($params['mobile'] . $params['template_code']);
        $sendFrequentInterval = get_sys_config('send_frequent_interval') ?? 60;
        if ($captcha && time() - $captcha['create_time'] < $sendFrequentInterval) {
            $this->error(__('Frequent SMS sending'));
        }

        $userInfo = \app\common\model\User::where('mobile', $params['mobile'])->find();
        if ($params['template_code'] == 'user_register' && $userInfo) {
            $this->error(__('Mobile number has been registered, please log in directly'));
        } elseif ($params['template_code'] == 'user_change_mobile' && $userInfo) {
            $this->error(__('The mobile number has been occupied'));
        } elseif (in_array($params['template_code'], ['user_retrieve_pwd', 'user_mobile_verify']) && !$userInfo) {
            $this->error(__('Mobile number not registered'));
        }

        if ($params['template_code'] == 'user_mobile_verify') {
            if (!$this->auth->isLogin()) {
                $this->error(__('Please login first'));
            }
            if ($this->auth->mobile != $params['mobile']) {
                $this->error(__('Please use the account registration mobile to send the verification code'));
            }
            // [opcode 对照] 模板变量 code/alnum 存在时，将其值登记为后续校验用验证码（闭包 opcode 全解）
            \think\facade\Event::listen('TemplateAnalysisAfter', function ($templateData) use ($params) {
                if (array_key_exists('code', $templateData['variables'])) {
                    (new \ba\Captcha())->create($params['mobile'] . $params['template_code'], $templateData['variables']['code']);
                }
                if (array_key_exists('alnum', $templateData['variables'])) {
                    (new \ba\Captcha())->create($params['mobile'] . $params['template_code'], $templateData['variables']['alnum']);
                }
            });
        }

        try {
            \modules\sms\Sms::send($params['template_code'], $params['mobile']);
        } catch (\Throwable $e) {
            if (!env('APP_DEBUG', false)) {
                $this->error(__('Failed to send SMS. Please contact the website administrator'));
            } else {
                $this->error(__($e->getMessage()));
            }
        }

        $this->success(__('SMS sent successfully'));
    }

}
