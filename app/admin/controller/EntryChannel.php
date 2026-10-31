<?php

// 还原自 Swoole Compiler 加密文件: EntryChannel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/EntryChannel.php

namespace app\admin\controller;

class EntryChannel extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id', null, 'create_time'];
    protected array $withJoinTable = ['sp'];
    protected string|array $quickSearchField = ['id', null];
    public function initialize(): void
    {
        // 修复：原始赋值链断链（new 与属性赋值分离），按 BuildAdmin 标准模式重建
        $this->model = new \app\admin\model\EntryChannel();
    }

    public function index(): void
    {
        // [opcode 对照] 加密原件本类覆写 index()（前次反编译整方法丢失，按 opcode 全量补齐）：
        // param('select')→select()；list(queryBuilder)；withJoin(withJoinTable, withJoinType)
        // ->visible(['sp'=>['name']])->alias->where->order->paginate；success('', ['list','total','remark'])
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->visible(['sp' => ['name']])
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $this->model->startTrans();
            try {
                $service = new \app\common\service\EntryChannelService();
                $result  = $service->create($data);
                $this->model->commit();
            } catch (\Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }

            $this->success(__('Added successfully'), ['id' => $result->id]);
            return;
        }

        $this->error(__('Parameter error'));
    }

    public function edit(): void
    {
        $pk  = $this->model->getPk();
        $id  = $this->request->param($pk);
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $this->model->startTrans();
            try {
                $service = new \app\common\service\EntryChannelService();
                $service->update((int)$id, $data);
                $this->model->commit();
            } catch (\Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }

            $this->success(__('Update successful'));
            return;
        }

        $arr = $row->toArray();
        if ($row->source === 'up') {
            $config = $row->config ?: [];
            $arr['up_id']         = $config['up_id'] ?? 0;
            $arr['up_channel_id'] = $config['up_channel_id'] ?? 0;
            $arr['service_name']  = $config['service_name'] ?? '';
            $arr['sync_time']     = empty($config['sync_time']) ? '' : date('Y-m-d H:i:s', $config['sync_time']);
            $up = \app\admin\model\supply\Upstream::find($config['up_id'] ?? 0);
            $arr['up_name'] = $up ? $up->name : '';
        }

        $this->success('', ['row' => $arr]);
    }

    public function syncupchannel(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('通道ID为空');
        }

        $service = new \app\common\service\EntryChannelService();
        try {
            $result = $service->syncUpChannel((int)$id);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return;
        }

        if ($result['exists']) {
            $this->success('同步成功，通道运行正常', $result);
            return;
        }

        $this->error('上游通道已不存在');
    }

    public function del(): void
    {
        $ids = $this->request->param('ids/a', []);
        if (empty($ids)) {
            $this->error(__('Parameter error'));
        }

        $this->model->startTrans();
        try {
            $service = new \app\common\service\EntryChannelService();
            foreach ($ids as $id) {
                $service->delete((int)$id);
            }
            $this->model->commit();
        } catch (\Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }

        $this->success(__('Deleted successfully'));
    }

    public function getupentrychannellist(): void
    {
        $upId = $this->request->param('up_id');
        if (empty($upId)) {
            $this->error('上游ID为空');
        }

        $up = \app\admin\model\supply\Upstream::find($upId);
        if (empty($up)) {
            $this->error('上游不存在');
        }

        $request = new \app\common\service\entry\EntryRequest();
        $service = new \app\common\service\EntryService($request);
        try {
            $list = $service->getUpChannelList($up);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('ok', ['list' => $list]);
    }

    public function getentryconfig(): void
    {
        $id = $this->request->param('id');
        $channel = \app\admin\model\EntryChannel::find($id);
        if (empty($channel)) {
            $this->error('通道不存在');
        }

        try {
            $service = \app\common\service\entry\EntryFactory::create($channel);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->success('ok', $service->getConfig() ?? []);
    }

    public function getoauthconfig(): void
    {
        $spId = $this->request->param('sp_id');
        if (empty($spId)) {
            $this->error('服务商ID为空');
        }

        $sp = \app\admin\model\ServiceProvider::find($spId);
        if (empty($sp)) {
            $this->error('服务商不存在');
        }

        $platformCode = $this->request->param('platform_code');
        $channel = \app\admin\model\EntryChannel::where([
            'sp_id'         => $spId,
            'platform_code' => $platformCode,
        ])->find();

        $this->success('ok', $channel);
    }

    public function setoauthconfig(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $data = $this->request->post();
        try {
            $this->setOAuthDataVal($data);
        } catch (\think\exception\ValidateException $e) {
            $this->error($e->getMessage());
        }

        $spId = $this->request->param('sp_id');
        $sp   = \app\admin\model\ServiceProvider::find($spId);
        if (empty($sp)) {
            $this->error('服务商不存在');
        }

        $channel = \app\admin\model\EntryChannel::where([
            'sp_id'         => $spId,
            'platform_code' => $data['platform_code'],
        ])->find();
        if (empty($channel)) {
            $insert = [
                'sp_id'         => $spId,
                'platform_code' => $data['platform_code'],
                'price'         => $data['price'] ?? 0,
                'config'        => [],
                'status'        => 1,
            ];
            $channel = \app\admin\model\EntryChannel::create($insert);
        } else {
            $update = [
                'config' => $data['config'],
            ];
            $fields = ['price', 'status'];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $update[$field] = $data[$field];
                }
            }
            $channel->save($update);
        }

        $this->success('操作成功');
    }

    private function setoauthdataval($data): void
    {
        validate([
            'sp_id|服务商ID'         => 'require',
            'platform_code|平台代码' => 'require',
            'config|配置'            => 'require',
        ])->check($data);

        $channelType = config('entry.channel_type');
        $found = false;
        foreach ($channelType as $item) {
            if ($data['platform_code'] == $item['code']) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new \think\exception\ValidateException('平台代码错误：' . $data['platform_code']);
        }
    }

    public function setcomplaintcallbackurl(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('ID为空');
        }

        $channel = \app\admin\model\EntryChannel::find($id);
        if (empty($channel)) {
            $this->error('进件通道不存在');
        }

        $method = $this->request->param('method');
        if (!in_array($method, ['create', 'update'])) {
            $this->error('设置方法错误');
        }

        $service = \app\common\service\complaint\ComplaintFactory::create($channel);
        try {
            $service->setCallbackUrl($method);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('设置成功', $channel);
    }

    public function delcomplaintcallbackurl(): void
    {
        $id = $this->request->param('id');
        if (empty($id)) {
            $this->error('ID为空');
        }

        $channel = \app\admin\model\EntryChannel::find($id);
        if (empty($channel)) {
            $this->error('进件通道不存在');
        }

        $service = \app\common\service\complaint\ComplaintFactory::create($channel);
        try {
            $service->delCallbackUrl();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('删除成功', $channel);
    }

}
