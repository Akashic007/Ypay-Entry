<?php

// 还原自 Swoole Compiler 加密文件: Subscribe.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/Subscribe.php

namespace app\api\controller\agent;

class Subscribe extends \app\common\controller\Frontend {
    public function initialize(): void
    {
        parent::initialize();
    }

    public function getsubscribelist(): void
    {
        $list = \app\admin\model\agent\Subscribe::where('status', 1)->select();
        $this->success('ok', $list);
    }

    public function subscribe(): void
    {
        $id = $this->request->param('id');
        if (!$id) {
            $this->error('套餐ID为空');
        }
        $subscribe = \app\admin\model\agent\Subscribe::find($id);
        if (!$subscribe) {
            $this->error('代理套餐不存在');
        }
        $user = \app\admin\model\User::find($this->auth->id);
        if (!$user) {
            $this->error('用户不存在');
        }
        try {
            $service = new \app\common\service\agent\SubscribeService();
            $res = $service->agentSubscribe($user, $subscribe);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }
        $this->success('成功开通代理', $res);
    }

}
