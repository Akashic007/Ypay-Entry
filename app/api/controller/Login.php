<?php

// 还原自 Swoole Compiler 加密文件: Login.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Login.php
//
// [2026-09-13 opcode 全量对照还原] 登录/注册控制器：
//   login: 已登录短路 → 配置读取(验证码开关/登录方式) → POST 校验(方式/账号) →
//          开启方式校验 → username 走 auth->login / mobile·email 走短信验证码+auth->direct →
//          TOTP 二次验证(googleVerify 用户强制) → UserLogin 事件 → success(登录成功,userInfo)
//   reg:   同构注册流 → auth->register(username, password, mobile, email) →
//          oauth_info 关联(Cookie+AES+OAuthFactory) → 邀请码绑定(agent_id) → success(注册成功)

namespace app\api\controller;

use app\common\event\UserLogin;
use think\exception\ValidateException;
use think\facade\Cookie;
use app\common\service\CaptchaService;
use think\facade\Event;

class Login extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['login', 'logout', 'reg', 'getusertotpstatus'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function login(): void
    {
        // 已登录 → 短路
        if ($this->auth->isLogin()) {
            $this->success(__('您已经登录，无需再次登录'), ['type' => $this->auth::LOGGED_IN], $this->auth::LOGIN_RESPONSE_CODE);
        }

        $captchaSwitch = get_sys_config('is_login_reg_captcha') == 'open';
        $loginMethod   = get_sys_config('login_method');

        if (request()->isPost()) {
            $params = request()->post();

            try {
                $method = $params['method'] ?? '';
                $label  = match ($method) {
                    'username' => '用户名',
                    'mobile'   => '手机号',
                    default    => '邮箱',
                };

                if (empty($params['method'])) {
                    throw new ValidateException('请选择登录方式');
                }
                if ($method != 'username' && $method != 'mobile' && $method != 'email') {
                    throw new ValidateException('登录方式错误');
                }
                if (empty($params['account'])) {
                    throw new ValidateException($label . '不能为空');
                }
            } catch (ValidateException $e) {
                $this->error($e->getMessage());
            }

            if (!in_array($params['method'], $loginMethod)) {
                $this->error('暂未开启的登录方式');
            }

            if ($params['method'] == 'username') {
                if (empty($params['password'])) {
                    $this->error('密码不能为空');
                }
                // [2026-10-01 opcode 对照补回] 原版行 74-84: 开启验证码开关时经 CaptchaService 校验（原还原版整体丢失，登录可绕过验证码）
                if ($captchaSwitch) {
                    try {
                        $captchaService = new CaptchaService();
                        $captchaService->check();
                    } catch (\ba\Exception $e) {
                        $this->error($e->getMessage());
                    }
                }
                $res = $this->auth->login($params['account'], $params['password'], $params['keep'] ?? false);
            } else {
                $user = \app\common\model\User::where('method', $params['account'])->find() ?? null;
                if (empty($user)) {
                    $this->error('用户不存在');
                }
                if ($user->status == 'disable') {
                    $this->error('用户已禁用');
                }
                if (empty($params['msgCaptcha'])) {
                    $this->error('验证码不能为空');
                }
                $captcha = new \ba\Captcha();
                if (!$captcha->check($params['msgCaptcha'], 'user_' . $params['method'] . '_login')) {
                    $this->error('消息验证码错误');
                }
                $res = $this->auth->direct($user->id);

                // TOTP 安全验证, 失败即登出（googleVerify 内置门禁: is_totp 系统开关 / totp_auth_status.is_open / google_auth.secret 绑定）
                if (is_object($res)) {
                    if (!$this->googleVerify($user, $params['totp_code'] ?? '')) {
                        $this->auth->logout();
                        $this->error('请先完成TOTP安全验证');
                    }
                }

                Event::trigger(new UserLogin($user, $params['method']));
                $this->success('登录成功', ['userInfo' => $user->getUserInfo()]);
            }

            if ($res === false) {
                $error = $this->auth->getError() ?: '登录失败';
                $this->error($error);
            }

            Event::trigger(new UserLogin($this->auth->getUser(), 'username'));
            $this->success('登录成功', ['userInfo' => $this->auth->getUserInfo()]);
        }

        // GET: 返回登录页配置
        $oauthConfig = new \app\admin\model\oauth\Config();
        $this->success('ok', [
            'isLoginCaptcha' => $captchaSwitch,
            'loginMethod'    => $loginMethod,
            'oAuthMethod'    => $oauthConfig->getLoginMethod(),
        ]);
    }

    public function reg(): void
    {
        if ($this->auth->isLogin()) {
            $this->success(__('您已经登录，请退出后重试'), ['type' => $this->auth::LOGGED_IN], $this->auth::LOGIN_RESPONSE_CODE);
        }

        $captchaSwitch = get_sys_config('is_login_reg_captcha') == 'open';
        $regMethod     = get_sys_config('reg_method');

        if (request()->isPost()) {
            $params = request()->post();

            if (empty($params['method'])) {
                $this->error('注册方式不能为空');
            }
            if ($params['method'] != 'username' && $params['method'] != 'mobile' && $params['method'] != 'email') {
                $this->error('注册方式错误');
            }
            if (!in_array($params['method'], $regMethod)) {
                $this->error('暂未开启的注册方式');
            }
            if ($params['method'] == 'username') {
                if (empty($params['username'])) {
                    $this->error('用户名不能为空');
                }
                if (empty($params['password'])) {
                    $this->error('用户名注册时，密码不能为空');
                }
            }
            try {
                (new CaptchaService())->check(); // [opcode 对照] 原版无参，自取 request()->param()
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }

            $method = $params['method'] == 'email' ? '邮箱' : '手机号';
            if (empty($params[$params['method']])) {
                $this->error($method . '不能为空');
            }
            if (empty($params['msgCaptcha'])) {
                $this->error('验证码不能为空');
            }
            $captcha = new \ba\Captcha();
            if (!$captcha->check($params['msgCaptcha'], 'user_register')) {
                $this->error('消息验证码错误');
            }

            $res = $this->auth->register(
                $params['username'] ?? '',
                $params['password'] ?? '',
                $params['mobile'] ?? '',
                $params['email'] ?? ''
            );

            if (is_object($res)) {
                // OAuth 注册关联：Cookie 中的 oauth_info (AES) → 换取授权记录绑定 uid
                if (Cookie::get('oauth_info')) {
                    $oauthInfo = json_decode((new \app\common\library\AesEncrypt())->decryptdata(Cookie::get('oauth_info')), true);
                    $oauth     = \app\common\service\oAuth\OAuthFactory::create($oauthInfo['type']);
                    $oauthUser = $oauth->getUser();

                    if ($oauthUser['code'] == 1) {
                        $oauthLog = \app\admin\model\oauth\Log::where('type', $oauthUser['type'])->where('uid', $oauthUser['uid'])->find();
                        if ($oauthLog) {
                            \app\admin\model\oauth\Log::where('id', $oauthLog->id)->update(['user_id' => $res->id]);
                        } else {
                            \app\admin\model\oauth\Log::create([
                                'user_id'  => $res->id,
                                'type'     => $oauthUser['type'],
                                'uid'      => $oauthUser['uid'],
                                'login_ip' => request()->ip(),
                                'content'  => $oauthUser['content'] ?? '',
                                'status'   => 1,
                            ]);
                        }
                    }
                    Cookie::delete('oauth_info');
                }

                // 邀请码绑定代理
                if (!empty($params['invite_code'])) {
                    $agent = \app\admin\model\agent\Agent::where('invite_code', $params['invite_code'])->find();
                    if ($agent->status == 1) {
                        $res->agent_id = $agent->id;
                        $res->save();
                    }
                }

                $this->success('注册成功', ['userInfo' => $res->getUserInfo()]);
            }

            $error = $this->auth->getError() ?: '注册失败';
            $this->error($error);
        }

        $this->success('ok', [
            'isRegCaptcha' => $captchaSwitch,
            'regMethod'    => $regMethod,
        ]);
    }

    // [2026-10-01 opcode 对照还原] 登出：仅 POST；携带 refreshToken 时删除该 token；再销毁当前会话
    public function logout(): void
    {
        if ($this->request->isPost()) {
            $refreshToken = $this->request->post('refreshToken', '');
            if ($refreshToken !== '') {
                \app\common\facade\Token::delete($refreshToken);
            }
            $this->auth->logout();
            $this->success();
        }
    }

    // [2026-10-01 opcode 对照还原] 登录前 TOTP 状态查询（登录页据 totp_status + user_totp_status 决定是否弹验证码输入）
    public function getUserTotpStatus(): void
    {
        $params = $this->request->param(['type', 'account']);
        if (empty($params['type']) || empty($params['account'])) {
            $label = match ($params['type']) {
                'email'  => '邮箱',
                'mobile' => '手机号',
                default  => '用户名',
            };
            $this->error($label . '不能为空');
        }
        $user = \app\common\model\User::where($params['type'], $params['account'])->find();
        if (empty($user)) {
            $this->error('账户不存在');
        }
        $this->success('ok', [
            'totp_status'      => get_sys_config('is_totp'),
            'user_totp_status' => $user->totp_auth_status,
        ]);
    }

    // [2026-10-01 opcode 对照还原] TOTP 二次验证判定：系统开关关/用户未开通/未绑定密钥 → 免验证放行
    private function googleVerify($user, $code)
    {
        if (get_sys_config('is_totp') !== 'open') {
            return true;
        }
        if ($user->totp_auth_status['is_open'] != 1) {
            return true;
        }
        if (empty($user->google_auth['secret'])) {
            return true;
        }
        if (empty($code)) {
            return false;
        }
        $ga = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
        if (!$ga->checkCode($user->google_auth['secret'], $code)) {
            return false;
        }
        return true;
    }
}
