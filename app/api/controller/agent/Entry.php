<?php

// 还原自 Swoole Compiler 加密文件: Entry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/agent/Entry.php

namespace app\api\controller\agent;

class Entry extends \app\common\controller\Frontend {
    private \app\admin\model\agent\Agent $agent;
    public function initialize(): void
    {
        parent::initialize();
        $agent = \app\admin\model\agent\Agent::where('user_id', $this->auth->id)
            ->with(['subscribe' => function ($query) {
                $query->withoutField('price');
            }])
            ->find();
        if (empty($agent)) {
            $this->error('暂未开通代理');
        }
        if ($agent->status != 1) {
            $this->error('代理状态异常');
        }
        $this->agent = $agent;
    }

    public function getuserentrylist(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $list = \app\admin\model\agent\UserEntry::where('agent_id', $this->agent->id)
            ->with(['me' => function ($query) {
                $query->field(['id', 'user_id', 'sp_id', 'ec_id', 'platform_code']);
            }])
            ->paginate($limit);
        $this->success('ok', $list);
    }

    public function getdownentrylist(): void
    {
        $limit = $this->request->param('limit', 10) ?? 10;
        $list = \app\admin\model\merchant\MerchantEntry::where('user_id', $this->auth->id)
            ->where('source', 'downstream')
            ->withoutField(['apply_data'])
            ->paginate($limit);
        $this->success('ok', $list);
    }

    public function add(): void
    {
        if (!$this->request->post()) {
            $this->error('错误的请求方式');
        }
        $post = $this->request->param(['channel_id', 'merchant_data']);
        $user = \app\admin\model\User::find($post['user_id']);
        if ($user->agent_id != $this->agent->id) {
            $this->error('代理商关系权限不足');
        }
        $channel = \app\admin\model\EntryChannel::find($post['channel_id']);
        if (empty($channel)) {
            $this->error('进件通道不存在');
        }
        try {
            $req = new \app\common\service\entry\EntryRequest();
            $req->setChannel($channel);
            $req->setData($post['merchant_data']);
            $req->setUser($user);
            if (isset($post['draft']) && $post['draft'] == 0 || $post['draft'] == '0') {
                $req->asDraft();
            }
            $req->byagent($this->agent);
            $req->setUser($user);
            $req->setData($post['merchant_data']);
            $service = new \app\common\service\EntryService($req);
            $service->apply();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('代理进件申请成功');
    }

    public function edit(): void
    {
        $data = $this->request->param(['me_id']);
        $ue = \app\admin\model\agent\UserEntry::where('agent_id', $this->agent->id)
            ->where('me_id', $data['me_id'])
            ->find();
        if (empty($ue)) {
            $this->error('进件记录不存在或权限不足');
        }
        $me = \app\admin\model\merchant\MerchantEntry::find($ue->me_id);
        if (!$this->request->isPost()) {
            $this->success('ok', $me);
        }
        try {
            $merchantData = $this->request->post('merchant_data');
            $user = \app\admin\model\User::find($ue->user_id);
            $req = new \app\common\service\entry\EntryRequest();
            $req->setUser($user);
            $req->setData($merchantData);
            $req->byagent($this->agent);
            if (isset($data['draft']) && $data['draft'] == 0 || $data['draft'] == '0') {
                $req->asDraft();
            }
            $channel = \app\admin\model\EntryChannel::find($me->ec_id);
            $req->setChannel($channel);
            $service = new \app\common\service\EntryService($req);
            $service->editdata($me);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

}
