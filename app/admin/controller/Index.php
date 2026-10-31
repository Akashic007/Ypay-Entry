<?php
declare (strict_types=1);

// 还原自 Swoole Compiler 加密文件: Index.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/Index.php
// 本类为 BuildAdmin 框架件：index/login/logout 方法体按 BuildAdmin 原版重建（opcode 残片
// LOGGED_IN/LOGIN_RESPONSE_CODE/captchaId| 拼接/refreshToken 与之吻合）。
// noNeedLogin 四元素为 opcode 真值（含 YPay 自有的 loginbefore 与 index）。
// loginbefore 为 YPay 自有方法，原始逻辑断链（残留 status/bind 配置比对），暂以空成功响应顶替，
// opcode 还原后必须覆盖！

namespace app\admin\controller;

use Throwable;
use ba\ClickCaptcha;
use think\facade\Config;
use think\facade\Validate;
use app\common\facade\Token;
use app\admin\model\AdminLog;
use app\common\controller\Backend;

class Index extends Backend
{
    protected array $noNeedLogin      = ['logout', 'loginbefore', 'login', 'index'];
    protected array $noNeedPermission = ['index'];

    /**
     * 后台初始化请求
     * @return void
     * @throws Throwable
     */
    public function index(): void
    {
        // [2026-10-02 用户决策偏离原版] 原版无登录检查：会话过期时 getMenus 为空，
        // 返回误导性的 "No background menu" 并卡死在 loading 页。补登录检查返回
        // 303 need login，admin SPA 拦截器会清 token 并跳转 /admin/login。
        if (!$this->auth->isLogin()) {
            $this->error(__('Please login first'), ['type' => $this->auth::NEED_LOGIN], $this->auth::LOGIN_RESPONSE_CODE);
        }
        $adminInfo          = $this->auth->getInfo();
        $adminInfo['super'] = $this->auth->isSuperAdmin();
        unset($adminInfo['token'], $adminInfo['refresh_token']);

        $menus = $this->auth->getMenus();
        if (!$menus) {
            $this->error(__('No background menu, please contact super administrator!'));
        }
        $this->success('', [
            'adminInfo'  => $adminInfo,
            'menus'      => $menus,
            'siteConfig' => [
                'siteName'     => get_sys_config('site_name'),
                'version'      => get_sys_config('version'),
                'apiUrl'       => Config::get('buildadmin.api_url'),
            ],
            'terminal'   => $this->auth->isSuperAdmin(),
            'defaultLang' => Config::get('lang.default_lang') ?: 'zh-cn',
        ]);
    }

    /**
     * 管理员登录
     * @return void
     * @throws Throwable
     */
    public function login(): void
    {
        // 检查登录态
        if ($this->auth->isLogin()) {
            $this->success(__('You have already logged in. There is no need to log in again~'), [
                'type' => $this->auth::LOGGED_IN,
            ], $this->auth::LOGIN_RESPONSE_CODE);
        }

        $captchaSwitch = Config::get('buildadmin.admin_login_captcha');

        // 检查提交
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keep     = $this->request->post('keep');

            $rule = [
                'username|' . __('Username') => 'require|length:3,30',
                'password|' . __('Password') => 'require|regex:^(?!.*[&<>"\'\n\r]).{6,32}$',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
            ];
            if ($captchaSwitch) {
                $rule['captchaId|' . __('CaptchaId')] = 'require';
                $rule['captchaInfo|' . __('Captcha')] = 'require';

                $data['captchaId']   = $this->request->post('captchaId');
                $data['captchaInfo'] = $this->request->post('captchaInfo');
            }
            $validate = Validate::rule($rule);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            if ($captchaSwitch) {
                $captchaObj = new ClickCaptcha();
                if (!$captchaObj->check($data['captchaId'], $data['captchaInfo'])) {
                    $this->error(__('Captcha error'));
                }
            }

            AdminLog::instance()->setTitle(__('Login'));

            $res = $this->auth->login($username, $password, (bool)$keep);
            if ($res === true) {
                $this->success(__('Login succeeded!'), [
                    'userInfo' => $this->auth->getInfo(),
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ?: __('Incorrect user name or password!');
                $this->error($msg);
            }
        }

        $this->success('', [
            'captcha' => $captchaSwitch,
        ]);
    }

    /**
     * YPay 自有方法：登录前置检查（登录页配置）。
     * 原始方法体数据流断链（残留 status/bind 配置行比对），暂以空成功响应顶替，
     * opcode 还原后必须覆盖本方法。
     */
    public function loginbefore(): void
    {
        $this->success('', []);
    }

    /**
     * 管理员注销
     * @return void
     */
    public function logout(): void
    {
        if ($this->request->isPost()) {
            $refreshToken = $this->request->post('refreshToken', '');
            if ($refreshToken) Token::delete((string)$refreshToken);
            $this->auth->logout();
            $this->success();
        }
    }
}
