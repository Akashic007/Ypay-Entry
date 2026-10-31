<?php

// 还原自 Swoole Compiler 加密文件: RealName.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/user/RealName.php
//
// [2026-09-22 opcode 全量对照还原] 依据 dump_m.py 指令序重建全部方法：
//   initialize:       parent::initialize()（对照已还原 Withdraw::initialize 同一 opcode 序）
//   getrealnameqrcode: name/id_no 参数 → get_sys_config('real_name_config') 校验 →
//                      WechatFace->getUrl → domain.'/qrcode?data='.url → success
//   getrealstatus:    User::find(auth->id) → realName 关联 → idno/name 脱敏 → success('ok')
//   callback:         orderNo 空/未开启实名 → redirect(generatenoticeurl(...)) →
//                      WechatFace->checkRealNameStatus → redirect 结果页

namespace app\api\controller\user;

class RealName extends \app\common\controller\Frontend {
    protected array $noNeedLogin = ['callback'];
    public function initialize(): void
    {
        parent::initialize();
    }
    public function getrealnameqrcode(): void
    {
        $name = $this->request->param('name');
        $idNo = $this->request->param('id_no');
        $config = get_sys_config('real_name_config');
        if ($config['type'] == 'close') {
            $this->error('后台没有开启实名认证');
        }
        try {
            $wechatFace = new \app\common\library\realName\WechatFace();
            $url = $wechatFace->getUrl($name, $idNo, $this->auth->id);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $qrcode = $this->request->domain() . '/qrcode?data=' . $url;
        $this->success('', ['qrcode' => $qrcode, 'url' => $url]);
    }
    public function getrealstatus(): void
    {
        $user = \app\admin\model\User::find($this->auth->id);
        $realName = $user->realName ?? null;
        if (isset($realName['id_no'])) {
            $realName['id_no'] = idnomasking($realName['id_no']);
        }
        if (isset($realName['name'])) {
            $realName['name'] = namemasking($realName['name']);
        }
        $this->success('ok', $realName);
    }
    public function callback(): \think\response\Redirect
    {
        $orderNo = $this->request->param('orderNo');
        if (empty($orderNo)) {
            return redirect(generatenoticeurl('参数为空'));
        }
        $config = get_sys_config('real_name_config');
        if ($config['type'] == 'close') {
            return redirect(generatenoticeurl('未开启实名'));
        }
        try {
            $wechatFace = new \app\common\library\realName\WechatFace();
            $wechatFace->checkRealNameStatus($orderNo);
        } catch (\Exception $e) {
            return redirect(generatenoticeurl($e->getMessage()));
        }
        return redirect(generatenoticeurl('认证成功', 'success'));
    }
}
