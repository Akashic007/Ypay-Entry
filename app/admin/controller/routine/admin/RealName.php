<?php

// 还原自 Swoole Compiler 加密文件: RealName.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/routine/admin/RealName.php

namespace app\admin\controller\routine\admin;

class RealName extends \app\common\controller\Backend {
    protected array $noNeedLogin = ['callback'];
    protected array $noNeedPermission = ['getRealNameQrcode', 'getRealStatus'];
    public function initialize(): void
    {
        parent::initialize();
    }
    public function getrealnameqrcode(): void
    {
        $name  = $this->request->get('name');
        $id_no = $this->request->get('id_no');
        if (empty($name) || empty($id_no)) {
            $this->error('姓名和身份证号码不能为空');
        }
        $config = get_sys_config('real_name_config');
        if ($config['type'] == 'close') {
            $this->error('后台没有开启实名认证');
        }
        $wechatFace = new \app\common\library\realName\WechatFace();
        try {
            $url = $wechatFace->getadminurl($name, $id_no, $this->auth->id);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('', [
            'qrcode' => request()->domain() . '/qrcode?data=' . $url,
            'url'    => $url,
        ]);
    }
    public function getrealstatus(): void
    {
        $realName = \app\admin\model\auth\RealName::where('admin_id', $this->auth->id)->find();
        if (!empty($realName)) {
            $realName = $realName->toArray();
            if (!empty($realName['id_no'])) {
                $realName['id_no'] = idNoMasking($realName['id_no']);
            }
            if (!empty($realName['name'])) {
                $realName['name'] = nameMasking($realName['name']);
            }
        }
        $this->success('ok', $realName);
    }
    public function unbind(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误');
        }
        $realName = \app\admin\model\auth\RealName::where('admin_id', $this->auth->id)->find();
        if (empty($realName) || $realName['status'] != 1) {
            $this->error('暂未完成实名认证');
        }
        $realName->save(['status' => 0, 'real_time' => null, 'remark' => '管理员解除实名认证']);
        $this->success('解除认证成功');
    }
    public function callback(): \think\response\Redirect
    {
        $orderNo = $this->request->get('orderNo');
        if (empty($orderNo)) {
            return redirect(generateNoticeUrl('参数为空'));
        }
        $config = get_sys_config('real_name_config');
        if ($config['type'] == 'close') {
            return redirect(generateNoticeUrl('未开启实名'));
        }
        $wechatFace = new \app\common\library\realName\WechatFace();
        try {
            $wechatFace->checkadminrealnamestatus($orderNo);
        } catch (\Exception $e) {
            return redirect(generateNoticeUrl($e->getMessage()));
        }
        return redirect(generateNoticeUrl('认证成功', 'success'));
    }
}
