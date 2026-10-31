<?php

// 还原自 Swoole Compiler 加密文件: Account.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Account.php
//
// [2026-09-22 opcode 全量对照还原] 商户账户中心（12 方法）:
//   initialize        父类初始化(会员鉴权)
//   overview          近 7 日资金概览(按天统计 score/money, money 分→元)
//   profile           个人资料: GET 返回资料/核验配置, POST 编辑资料
//   verification      发起账户核验: 校验验证码, 签发 accountVerificationToken(600s)
//   bind              绑定/解绑手机号邮箱(验证码+唯一性校验)
//   changePassword    修改密码(旧密码校验+密码规则验证+事件通知+强制下线)
//   retrievePassword  找回密码(GET 返回找回方式 / POST 重置)
//   getApiInfo        API 信息(key 缺失时自动生成)
//   resetKey          重置 API key
//   getBalanceLog     余额明细分页
//   unBindOAuthLogin  解绑第三方登录(密码校验)
//   getUserLog        登录日志分页

namespace app\api\controller;

class Account extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['retrievePassword'];
    protected array $noNeedPermission = ['verification'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function overview(): void
    {
        $day = \ba\Date::unixTime('day', -6);
        $days  = [];
        $money = [];
        $score = [];
        for ($i = 0; $i < 7; $i++) {
            $days[$i] = date('Y-m-d', $day + $i * 86400);
            $time     = strtotime($days[$i]);
            $end      = strtotime('+1 day', $time) - 1;
            $score[$i] = \app\common\model\UserScoreLog::where('user_id', $this->auth->id)
                ->where('create_time', 'BETWEEN', $time . ',' . $end)
                ->sum('score');
            $moneySum = \app\common\model\UserMoneyLog::where('user_id', $this->auth->id)
                ->where('create_time', 'BETWEEN', $time . ',' . $end)
                ->sum('money');
            $money[$i] = bcdiv($moneySum, 100, 2);
        }
        $this->success('', [
            'days'  => $days,
            'money' => $money,
        ]);
    }

    public function profile(): void
    {
        $user = $this->auth->getUser();
        // [opcode 对照] 原指令为 COALESCE(结果, 变量槽), 兜底字面量不可辨, 按 house style 取 ''
        $close = get_sys_config('is_user_edit_profile') ?? '';
        if ($this->request->isPost()) {
            if ($close == 'close') {
                $this->error('当前系统禁止编辑个人资料');
            }
            $data = $this->request->only(['avatar', 'nickname', 'username']);
            $data['id'] = $this->auth->id;
            if (!isset($data['birthday'])) {
                $data['birthday'] = null;
            }
            $validate = new \app\api\validate\Account();
            try {
                $validate->scene('edit')->check($data);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
            \think\facade\Db::startTrans();
            try {
                $user->save($data);
                \think\facade\Db::commit();
            } catch (Throwable $e) {
                \think\facade\Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('资料编辑成功');
        }
        $this->success('', [
            'accountVerificationType' => get_account_verification_type(),
            'profile'                 => $this->auth->getUserInfo(),
            'is_edit'                 => $close,
        ]);
    }

    public function verification(): void
    {
        $captcha = new \ba\Captcha();
        $post = $this->request->only(['type', 'captcha']);
        if (!$captcha->check($post['captcha'], ($post['type'] == 'email' ? $this->auth->email : $this->auth->mobile) . 'user_' . $post['type'] . '_verify')) {
            $this->error(__('Please enter the correct verification code'));
        }
        $code = \ba\Random::uuid();
        \app\common\facade\Token::set($code, $post['type'] . '-pass', $this->auth->id, 600);
        $this->success('', [
            'type'                     => $post['type'],
            'accountVerificationToken' => $code,
        ]);
    }

    public function bind(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $post = $this->request->post();
        if (empty($post['operate'])) {
            $this->error('操作参数为空');
        }
        if (empty($post['type'])) {
            $this->error('绑定类型为空');
        }
        if ($post['operate'] != 'bind' && $post['operate'] != 'unBind') {
            $this->error('操作错误');
        }
        if ($post['type'] != 'mobile' && $post['type'] != 'email') {
            $this->error('绑定类型错误');
        }
        $name = $post['type'] == 'mobile' ? '手机号' : '邮箱';
        if (empty($post[$post['type']])) {
            $this->error($name . '为空');
        }
        if ($post['operate'] == 'bind') {
            $user = \app\common\model\User::where($post['type'], $post[$post['type']])->find();
            if ($user) {
                $this->error($name . '已被占用');
            }
            if (empty($post['captcha'])) {
                $this->error('验证码为空');
            }
            $captcha = new \ba\Captcha();
            if (!$captcha->check($post['captcha'], $post[$post['type']] . 'user_change_' . $post['type'])) {
                $this->error('验证码错误');
            }
            $user = $this->auth->getUser();
            if ($post['type'] == 'email') {
                $validate = \think\facade\Validate::rule(['email' => 'require|email|unique:user'])
                    ->message(['email.require' => 'email format error', 'email.email' => 'email format error', 'email.unique' => 'email is occupied']);
                if (!$validate->check(['email' => $post['email']])) {
                    $this->error(__($validate->getError()));
                }
                $user->email = $post['email'];
            }
            if ($post['type'] == 'mobile') {
                $validate = \think\facade\Validate::rule(['mobile' => 'require|mobile|unique:user'])
                    ->message(['mobile.require' => 'mobile format error', 'mobile.mobile' => 'mobile format error', 'mobile.unique' => 'mobile is occupied']);
                if (!$validate->check(['mobile' => $post['mobile']])) {
                    $this->error(__($validate->getError()));
                }
                $user->mobile = $post['mobile'];
            }
            $user->save();
            $this->success('绑定成功');
        }
        $captcha = new \ba\Captcha();
        if (!$captcha->check($post['captcha'], $post[$post['type']] . 'user_' . $post['type'] . '_verify')) {
            $this->error('验证码错误');
        }
        $user = $this->auth->getUser();
        if ($post['type'] == 'email') {
            $user->email = '';
        } else {
            $user->mobile = '';
        }
        $user->save();
        $this->success('解绑成功');
    }

    public function changePassword(): void
    {
        if ($this->request->isPost()) {
            $user = $this->auth->getUser();
            $post = $this->request->only(['oldPassword', 'newPassword']);
            if (!verify_password($post['oldPassword'], $user->password)) {
                $this->error(__('Old password error'));
            }
            $rule = $this->auth->buildRegex(get_sys_config('password_rule'));
            try {
                validate(['password' => 'length:6,18' . $rule['regex']], [
                    'password.length' => '密码长度为6-18位',
                    'password.regex'  => '密码' . $rule['error'],
                ])->check(['password' => $post['newPassword']]);
            } catch (\think\exception\ValidateException $e) {
                $this->error($e->getMessage());
            }
            \think\facade\Db::startTrans();
            try {
                $user->resetPassword($this->auth->id, $post['newPassword']);
                \think\facade\Db::commit();
                // [opcode 对照] UserNotify 构造第三参(params)由 SEND op1=$post 槽位推断, 构造签名 (user, event, array params)
                \think\facade\Event::trigger(new \app\common\event\UserNotify($user, \app\common\event\UserNotify::RESET_PASSWORD, $post));
            } catch (Throwable $e) {
                \think\facade\Db::rollback();
                $this->error($e->getMessage());
            }
            $this->auth->logout();
            $this->success(__('Password has been changed, please login again~'));
        }
    }

    public function retrievePassword(): void
    {
        $post = $this->request->only(['type', 'captcha', 'account', 'password']);
        if ($this->request->isGet()) {
            $this->success('', [
                'method' => get_sys_config('recover_method'),
            ]);
        }
        if (empty($post['type'])) {
            $this->error('找回方式不能为空');
        }
        if ($post['type'] != 'email' && $post['type'] != 'mobile') {
            $this->error('找回方式错误');
        }
        $name = $post['type'] == 'email' ? '邮箱' : '手机号';
        if (empty($post['account'])) {
            $this->error($name . '不能为空');
        }
        if (empty($post['captcha'])) {
            $this->error('验证码不能为空');
        }
        if (empty($post['password'])) {
            $this->error('密码不能为空');
        }
        if ($post['type'] == 'email') {
            $user = \app\common\model\User::where('email', $post['account'])->find();
        } else {
            $user = \app\common\model\User::where('mobile', $post['account'])->find();
        }
        if (!$user) {
            $this->error(__('Account does not exist~'));
        }
        $captcha = new \ba\Captcha();
        if (!$captcha->check($post['captcha'], $post['account'] . 'user_retrieve_pwd')) {
            $this->error(__('Please enter the correct verification code'));
        }
        if ($user->resetPassword($user->id, $post['password'])) {
            $this->success(__('Password has been changed~'));
        }
        $this->error(__('Failed to modify password, please try again later~'));
    }

    public function getApiInfo(): void
    {
        $user = $this->auth->getUser();
        $key  = $user['key'];
        if (empty($key)) {
            $key = $user->resetKey($this->auth->id);
        }
        $this->success('ok', [
            'user_id' => $this->auth->id,
            'key'     => $key,
            'api'     => request()->domain() . '/',
        ]);
    }

    public function resetKey(): void
    {
        $user = $this->auth->getUser();
        $this->success('重置成功', [
            'key' => $user->resetKey($this->auth->id),
        ]);
    }

    public function getBalanceLog(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $log   = \app\admin\model\UserMoneyLog::where('user_id', $this->auth->id)
            ->order('id', 'desc')
            ->paginate($limit);
        $this->success('ok', $log);
    }

    public function unBindOAuthLogin(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $post = $this->request->only(['type', 'password']);
        if (empty($post['type']) || empty($post['password'])) {
            $this->error('参数错误');
        }
        if (!verify_password($post['password'], $this->auth->password)) {
            $this->error('密码错误');
        }
        $oauth = \app\admin\model\oauth\Log::where([
            'user_id' => $this->auth->id,
            'type'    => $post['type'],
            'status'  => 1,
        ])->value('id');
        if (!$oauth) {
            $this->error('暂无绑定记录');
        }
        \app\admin\model\oauth\Log::where([
            'user_id' => $this->auth->id,
            'type'    => $post['type'],
        ])->delete();
        $this->success('解绑成功');
    }

    public function getUserLog(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $log   = \app\admin\model\UserLog::where('user_id', $this->auth->id)
            ->withoutField('data')
            ->order('id', 'desc')
            ->paginate($limit);
        $this->success('ok', $log);
    }
}
