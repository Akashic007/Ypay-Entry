<?php

// 还原自 Swoole Compiler 加密文件: Subscribe.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Subscribe.php

namespace app\api\controller;

class Subscribe extends \app\common\controller\Frontend {
    protected array $middleware = ['app\\common\\middleware\\YAuthCheck' => ['only' => ['subscribe']], 'app\\common\\middleware\\UserCheck'];
    public function initialize(): void
    {
        parent::initialize();
    }


// [opcode 还原] 套餐组：status=1 → weigh desc → with channelTypes
    public function getgrouplist(): void
    {
        $list = \app\admin\model\user\SubscribeGroup::where('status', 1)
            ->order('weigh', 'desc')
            ->with('channelTypes')
            ->select();
        $this->success('', $list);
    }

// [opcode 还原] 套餐列表：status=1 → weigh desc → 可选 group_id 过滤 → paginate
    public function getlist(): void
    {
        $groupId = $this->request->param('group_id');
        $limit   = (int) ($this->request->param('limit') ?? 10);
        $query = \app\admin\model\user\Subscribe::where('status', 1)
            ->order('weigh', 'desc');
        if ($groupId > 0) {
            $query->where('group_id', $groupId);
        }
        $list = $query->paginate($limit);
        $this->success('', $list);
    }

// [opcode 还原] 余额购买套餐：套餐校验→用户校验→余额比对→SubscribeService::userSubscribe
    public function subscribe()
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('套餐ID为空');
        }
        $package = \app\admin\model\user\Subscribe::find($id);
        if (!$package) {
            $this->error('会员套餐不存在');
        }
        if ($package->status != 1) {
            $this->error('会员套餐未启用');
        }
        $user = \app\admin\model\User::find($this->auth->id);
        if (!$user) {
            $this->error('你把用户弄丢了？？');
        }
        if ($user->money < $package->price) {
            $this->error('余额不足');
        }
        try {
            (new \app\common\service\SubscribeService())->usersubscribe($user, $package);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('msg', []);
    }

}
