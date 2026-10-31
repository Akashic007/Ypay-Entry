<?php

// 还原自 Swoole Compiler 加密文件: Entry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Entry.php

namespace app\api\controller;

class Entry extends \app\common\controller\Frontend {
    protected array $middleware = ['app\\common\\middleware\\YAuthCheck' => ['only' => ['add', 'edit'], ], 'app\\common\\middleware\\UserCheck'];
    public function initialize(): void
    {
        parent::initialize();
    }

    public function add(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误！');
        }
        $post = $this->request->post(['channel_id', 'merchant_data']);
        if (empty($post['channel_id'])) {
            $this->error('进件渠道ID为空');
        }
        try {
            $channel = \app\admin\model\EntryChannel::find($post['channel_id']);
            if (empty($channel)) {
                throw new \ba\Exception('进件通道不存在');
            }
            $user = \app\admin\model\User::find($this->auth->id);
            $req = new \app\common\service\entry\EntryRequest();
            $req->setUser($user);
            $req->setChannel($channel);
            $req->setData($post['merchant_data']);
            if ($post['draft'] == 1) {
                $req->asDraft();
            }
            $service = new \app\common\service\EntryService($req);
            $service->apply();
        } catch (\ba\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('进件资料保存成功');
    }

    public function edit(): void

    {

        $data = $this->request->param(['me_id']);

        if (empty($data['me_id'])) {

            $this->error('进件资料ID为空');

        }

        $me = \app\admin\model\merchant\MerchantEntry::find($data['me_id']);

        if (empty($me)) {

            $this->error('商户进件记录不存在');

        }

        if ($me->user_id != $this->auth->id) {

            $this->error('非用户进件操作，暂无权限');

        }

        if (!$this->request->post()) {

            $this->success('ok', $me);

        }

        if (in_array($me->status, [5, 6])) {

            $this->error('当前状态无法编辑');

        }

        $data['merchant_data'] = $this->request->post('merchant_data');

        $data['draft'] = $this->request->post('draft', 0);

        if (empty($data['merchant_data'])) {

            $this->error('商户进件资料为空');

        }

        if ($me->status != 0 && $me->status != 3) {

            $this->error('非草稿和驳回状态无法编辑');

        }

        try {

            $channel = \app\admin\model\EntryChannel::find($me->ec_id);

            if (empty($channel)) {

                throw new \ba\Exception('进件通道不存在');

            }

            $user = \app\admin\model\User::find($this->auth->id);

            $req = new \app\common\service\entry\EntryRequest();

            if (isset($data['draft']) && $data['draft'] == 0 || $data['draft'] == '0') {

                $req->asDraft();

            }

            $req->setUser($user);

            $req->setChannel($channel);

            $req->setData($data['merchant_data']);

            $service = new \app\common\service\EntryService($req);

            $service->editdata($me);

        } catch (\Exception $e) {

            $this->error($e->getMessage());

        }

        $this->success('进件资料保存草稿成功');

    }

    public function submit(): void

    {

        $id = $this->request->param('id');

        if (empty($id)) {

            $this->error('进件记录ID为空');

        }

        $me = \app\admin\model\merchant\MerchantEntry::find($id);

        if (empty($me)) {

            $this->error('进件记录不存在');

        }

        try {

            $channel = \app\admin\model\EntryChannel::find($me->ec_id);

            if (empty($channel)) {

                throw new \Exception('进件通道不存在');

            }

            $factory = \app\common\service\entry\EntryFactory::create($channel);

            $factory->apply($me);

        } catch (\Exception $e) {

            $this->error($e->getMessage());

        }

        $this->success('进件资料提交成功');

    }

    public function getentrylist(): void

    {

        $limit = input('limit', 10);

        $list = \app\admin\model\merchant\MerchantEntry::where('user_id', $this->auth->id)

            ->withoutField(['apply_data'])

            ->with(['sp'])

            ->where('source', '<>', 'downstream')

            ->order('id', 'desc')

            ->paginate($limit);

        $this->success('ok', ['list' => $list->items(), 'total' => $list->total()]);

    }

    public function getentryconfig(): void

    {

        $id = $this->request->param('id');

        $channel = \app\admin\model\EntryChannel::find($id);

        if (empty($channel)) {

            $this->error('通道不存在');

        }

        try {

            $factory = \app\common\service\entry\EntryFactory::create($channel);

        } catch (\ba\Exception $e) {

            $this->error($e->getMessage());

            return;

        }

        $this->success('ok', $factory->getConfig() ?? []);

    }

    public function getserviceproviderlist(): void

    {

        $limit = input('limit', 10);

        $list = \app\admin\model\ServiceProvider::where('status', 1)->paginate($limit);

        $this->success('ok', $list);

    }

    public function getentrychannellist(): void

    {

        $limit = $this->request->param('limit');

        $spId = $this->request->param('sp_id');

        $query = \app\admin\model\EntryChannel::hasWhere('sp', ['status' => 1])

            ->where('status', 1);

        if (!empty($spId) && 0 < $spId) {

            $query->where('sp_id', $spId);

        }

        $list = $query->paginate($limit)

            ->each(function ($item) {

                unset($item['config']);

            });

        $this->success('ok', ['list' => $list]);

    }

    public function getapplyinfo(): void

    {

        $id = $this->request->param('id');

        if (empty($id)) {

            $this->error('ID为空');

        }

        $me = \app\admin\model\merchant\MerchantEntry::find($id);

        if (empty($me)) {

            $this->error('进件商户不存在');

        }

        if ($me->user_id != $this->auth->id) {

            $this->error('非用户进件操作，暂无权限');

        }

        try {

            $channel = \app\admin\model\EntryChannel::find($me->ec_id);

            if (empty($channel)) {

                throw new \ba\Exception('进件通道不存在');

            }

            $req = new \app\common\service\entry\EntryRequest();

            $req->setUser($me->user);

            $req->setChannel($channel);

            $service = new \app\common\service\EntryService($req);

            $result = $service->queryStatus($me);

        } catch (\ba\Exception $e) {

            $this->error($e->getMessage());

            return;

        }

        $this->success('ok', $result);

    }

}
