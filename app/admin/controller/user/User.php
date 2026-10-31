<?php

// 还原自 Swoole Compiler 加密文件: User.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/user/User.php

namespace app\admin\controller\user;

class User extends \app\common\controller\Backend {
    protected object $model;
    protected array $withJoinTable = ['userGroup', 'userConfig'];
    protected string|array $preExcludeFields = ['last_login_time', null, 'login_failure'];
    protected string|array $quickSearchField = ['username', null, 'nickname', null, 'email'];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\User();
    }

    public function settotpstatus(): void
    {
        // [opcode 对照] 原方法 line 170-184，字面量解密真值：id/status/ID为空/状态参数错误/app\admin\model\user\Config/where/user_id/find/google_auth/secret/用户未绑定TOTP身份验证器/save/操作成功
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('ID为空');
        }
        $status = $this->request->param('status');
        // [opcode 对照] 数组字面量 {0: 0, 1: ('RAW0',0)} 奇数位为清零哑桶，按状态机取 [0, 1]
        if (!in_array($status, [0, 1])) {
            $this->error('状态参数错误');
        }
        $config = \app\admin\model\user\Config::where('user_id', $id)->find();
        $googleAuth = $config->google_auth;
        if (empty($googleAuth['secret'])) {
            $this->error('用户未绑定TOTP身份验证器');
        }
        $googleAuth['status'] = $status;
        $config->google_auth  = $googleAuth;
        $config->save();
        $this->success('操作成功');
    }

    public function clearloginnumber(): void
    {
        // [opcode 对照] 原方法 line 195-204，字面量真值：id/ID为空/find/用户不存在/login_failure=0/save/清除成功
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('ID为空');
        }
        $row = $this->model->find($id);
        if (!$row) {
            $this->error('用户不存在');
        }
        $row->login_failure = 0;
        $row->save();
        $this->success('清除成功');
    }

    public function directlogin(): void
    {
        // [opcode 对照] 原方法 line 211-224，字面量真值：post(id)/isPost/用户ID为空！/find/用户不存在/app\common\library\Auth::instance/direct/登录失败/登录成功/getToken/请求方法错误！
        $id = $this->request->post('id');
        if (!$this->request->isPost()) {
            $this->error('请求方法错误！');
        }
        if (empty($id)) {
            $this->error('用户ID为空！');
        }
        $row = $this->model->find($id);
        if (!$row) {
            $this->error('用户不存在');
        }
        $auth = \app\common\library\Auth::instance();
        $ret = $auth->direct($id);
        if (!$ret) {
            $this->error('登录失败');
        }
        $this->success('登录成功', ['token' => $auth->getToken()]);
    }

}
