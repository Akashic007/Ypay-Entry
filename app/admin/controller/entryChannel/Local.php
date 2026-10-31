<?php

// 还原自 Swoole Compiler 加密文件: Local.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/entryChannel/Local.php

namespace app\admin\controller\entryChannel;

class Local extends \app\common\controller\Backend {
    protected object $model;
    protected string|array $preExcludeFields = ['id'];
    protected string|array $quickSearchField = ['id'];
    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\EntryChannel();
        $this->request->filter('clean_xss');
    }

    public function getconfig(): void
    {
        $channelId = $this->request->param('channel_id');
        if (empty($channelId)) {
            $this->error('通道ID为空');
        }
        $entryChannel = \app\admin\model\EntryChannel::find($channelId);
        if (empty($entryChannel)) {
            $this->error('进件通道不存在');
        }
        if ($entryChannel->source !== 'local') {
            $this->error('该通道不是本地通道');
        }
        $this->success('ok', ['channel' => $entryChannel->toArray(), 'config' => $entryChannel->config ?? []]);
    }

    public function setconfig(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式');
        }
        $channelId = $this->request->post('channel_id');
        $config = $this->request->post('config');
        if (empty($channelId)) {
            $this->error('通道ID为空');
        }
        if ($config === null) {
            $this->error('配置内容不能为空');
        }
        $entryChannel = \app\admin\model\EntryChannel::find($channelId);
        if (empty($entryChannel)) {
            $this->error('进件通道不存在');
        }
        if ($entryChannel->source !== 'local') {
            $this->error('该通道不是本地通道');
        }
        if (!is_array($config)) {
            $config = [];
        }
        $service = new \app\common\service\EntryChannelService();
        $service->setLocalConfig((int) $channelId, $config);
        $this->success('配置保存成功');
    }

}
