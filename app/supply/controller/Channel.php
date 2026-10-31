<?php

// 还原自 Swoole Compiler 加密文件: Channel.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/supply/controller/Channel.php

namespace app\supply\controller;

class Channel extends \app\common\controller\SupplyBase {
    public function initialize(): void
    {
        // [opcode 对照] GET_CALLED_CLASS('initialize')+DO_FCALL → parent::initialize()
        parent::initialize();
    }

    public function apply(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式！');
        }
        $data = $this->request->post();
        if (empty($data['business_id'])) {
            $this->error('业务申请ID为空');
        }
        $entry = \app\admin\model\merchant\MerchantEntry::where('business_id', $data['business_id'])->find();
        if (empty($entry)) {
            $this->error('商户申请记录不存在');
        }
        if ($entry->status != 8 || empty($entry->merchant) || $entry->merchant->status != 1) {
            $this->error('商户未完成入驻');
        }
        if ($entry->source != 'downstream') {
            $this->error('非下游申请商户，暂无权限申请支付能力');
        }
        $account = \app\admin\model\channel\Account::where('user_id', $this->user->id)
            ->where('source_type', 'down')
            ->where('source_id', $this->downstream->id)
            ->where('channel_type', $data['channel_type'])
            ->where('mch_id', $entry->merchant->id)
            ->find();
        if ($account) {
            $this->success('您已经申请过啦，直接使用即可', $account);
        }
        $channelService = new \app\common\service\ChannelService();
        try {
            // [opcode 对照] 受保护段中 2 字符加密属性名 ']T'（dec_literal 失真为 'nm'），四处分别回填 user_id/mch_id/mch_id/source_id，对应模型仅有 id 适配，按语义定案为 id
            $data['user_id'] = $this->user->id;
            $data['config']['mch_id'] = $entry->merchant->id;
            $data['mch_id'] = $entry->merchant->id;
            $data['source_cid'] = $data['cid'];
            $data['source_type'] = 'down';
            $data['source_id'] = $this->downstream->id;
            $account = $channelService->create($this->user, $data);
        } catch (\ba\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('支付通道申请成功', $account);
    }

    public function delete(): void
    {
        if (!$this->request->isPost()) {
            $this->error('错误的请求方式！');
        }
        // [opcode 对照] 打包字面量 {0:'business_id',1:RAW0} 隐藏槽按字符串池序/后续 $data['cid'] 语义还原为 'cid'（同 B18 cleardata 的 param([...]) 打包模式）
        $data = $this->request->param(['business_id', 'cid']);
        if (empty($data['business_id'])) {
            $this->error('业务申请ID为空');
        }
        if (empty($data['cid'])) {
            $this->error('通道ID为空');
        }
        $entry = \app\admin\model\merchant\MerchantEntry::where('business_id', $data['business_id'])->find();
        if (empty($entry)) {
            $this->error('通道商户不存在');
        }
        $account = \app\admin\model\channel\Account::where('mch_id', $entry->merchant->id)->where('id', $data['cid'])->find();
        $channelService = new \app\common\service\ChannelService();
        try {
            $channelService->delete($account);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('删除成功');
    }

}
