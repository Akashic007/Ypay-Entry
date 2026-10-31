<?php

// 还原自 Swoole Compiler 加密文件: EntryService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/EntryService.php

namespace app\common\service;

class EntryService{
    private \app\common\service\entry\EntryRequest $request;
    private \app\admin\model\merchant\MerchantEntry $me;
    public function __construct(\app\common\service\entry\EntryRequest $req)
    {
    $this->request = $req;
    }

    public function apply(): \app\admin\model\merchant\MerchantEntry
    {
    $request = $this->request;
    $request->validate();
    \think\facade\Db::startTrans();
    try {
        $this->createEntryRecord();
        if ($request->deduction) {
            $this->deductFee();
        }
        \think\facade\Db::commit();
    } catch (\Exception $e) {
        \think\facade\Db::rollback();
        throw new \ba\Exception($e->getMessage());
    }
    return $this->me;
    }

    public function editdata(\app\admin\model\merchant\MerchantEntry|null $me = null): void
    {
    if (!$me) {
        return; // [opcode 对照] 形参可空，事件系统可能以 null 调用
    }
    $request = $this->request;
    $request->validate();
    if (in_array($me->status, [5, 6])) {
        throw new \ba\Exception($me->statusText . '状态无法编辑');
    }
    \think\facade\Db::startTrans();
    try {
        $data = $me->apply_data ?? [];
        $data = array_replace_recursive($data, $request->data);
        $me->apply_data = $data;
        $me->save();
        $this->me = $me;
        \think\facade\Db::commit();
    } catch (\Exception $e) {
        \think\facade\Db::rollback();
        throw new \ba\Exception($e->getMessage());
    }
    if (!$request->draft) {
        $this->submit();
    }
    }

    public function uploadmedia(\app\common\model\Attachment|null $attachment = null): void
    {
    $channel = $this->request->channel;
    if (empty($channel)) {
        throw new \ba\Exception('进件通道未设置');
    }
    $entry = \app\common\service\entry\EntryFactory::create($channel);
    $entry->uploadMedia($attachment);
    }

    public function querystatus(\app\admin\model\merchant\MerchantEntry|null $me = null): array
    {
    if (!$me) {
        return []; // [opcode 对照] 形参可空，事件系统可能以 null 调用
    }
    $channel = $this->request->channel ?? \app\admin\model\EntryChannel::find($me->ec_id);
    if (empty($channel)) {
        throw new \ba\Exception('进件通道不存在');
    }
    $entry = \app\common\service\entry\EntryFactory::create($channel);
    return $entry->getApplyInfo($me);
    }

    public function getupchannellist(\app\admin\model\supply\Upstream|null $up = null): array
    {
    if ($up) {
        $ur = new \app\common\library\upstream\UpRequest($up);
        $entryChannel = new \app\common\library\upstream\EntryChannel($ur);
        $list = $entryChannel->getChannelList();
        return $list;
    }
    $upList = \app\admin\model\supply\Upstream::where('status', 1)->where('api_status', 1)->select();
    $list = [];
    $upList->each(function ($item) use (&$list) {
        try {
            $ur = new \app\common\library\upstream\UpRequest($item);
            $entryChannel = new \app\common\library\upstream\EntryChannel($ur);
            $channelList = $entryChannel->getChannelList();
            foreach ($channelList as $v) {
                $v['up_id'] = $item['id'];
                $list[] = $v;
            }
        } catch (\ba\Exception $e) {
            return null;
        }
        return null;
    });
    return $list;
    }

    public function getlocalchannellist(): array
    {
    $list = \app\admin\model\EntryChannel::hasWhere('sp', function ($query) {
        $query->where('status', 1);
    })->where('status', 1)->with(['sp' => function ($query) {
        $query->field(['id', 'name']);
    }])->select();
    $return = [];
    foreach ($list as $v) {
        $return[] = [
            'id'            => $v->id,
            'sp_id'         => $v->sp_id,
            'platform_code' => $v->platform_code,
            'price'         => $v->price,
            'status'        => $v->status,
            'channel_name'  => getEntryChannelName($v->platform_code)['name'] . '[' . $v->sp['name'] . ']',
            'platform_name' => getEntryChannelName($v->platform_code)['name'],
            'sp_name'       => $v->sp['name'],
        ];
    }
    return $return;
    }

    public function getchannellist(): array
    {
    $local = $this->getLocalChannelList();
    $up = $this->getUpChannelList();
    return array_merge($local, $up);
    }

    private function deductfee(): void
    {
    if (!$this->request->deduction) {
        return;
    }
    if ($this->me == null) {
        throw new \ba\Exception('暂无进件记录，无法预扣费');
    }
    $request = $this->request;
    $user = $request->user;
    $agent = $request->agent;
    $downAgent = null;
    if (0 < $user->agent_id) {
        $downAgent = \app\admin\model\agent\Agent::find($user->agent_id) ?? null;
    }
    $price = $request->channel->price;
    $targetUserId = $user->id;
    $logType = \app\admin\model\UserMoneyLog::TYPE_ENTRY_FEE;
    $agentService = new \app\common\service\AgentService();
    if ($request->isAgent) {
        $price = $agentService->calculateEntryDiscountAmount($agent, $request->channel);
        $targetUserId = $agent->user_id;
        $logType = \app\admin\model\UserMoneyLog::TYPE_AGENT_ENTRY;
        $targetUser = \app\admin\model\User::find($targetUserId);
        if (!$targetUser) {
            throw new \ba\Exception('扣费目标用户不存在');
        }
        if ($targetUser->money < $price) {
            throw new \ba\Exception('代理商余额不足');
        }
    }
    if ($downAgent) {
        $price = $agentService->calculateEntryDiscountAmount($downAgent, $request->channel);
        if ($user->money < $price) {
            throw new \ba\Exception('用户余额不足');
        }
    }
    if ($request->source != 'local') {
        $logType = \app\admin\model\UserMoneyLog::TYPE_DOWN_ENTRY;
    }
    \app\admin\model\UserMoneyLog::create([
        'user_id' => $targetUserId,
        'money'   => $price * -1,
        'type'    => $logType,
        'content' => $this->me->business_id,
        'memo'    => '用户进件扣费',
    ]);
    }

    private function createentryrecord(): void
    {
    $request = $this->request;
    $data = [
        'business_id' => $this->generateBusinessNo(),
        'user_id'     => $request->user->id,
        'status'      => 0,
        'apply_data'  => $request->data,
        'source'      => $request->source,
    ];
    $data = array_merge($data, [
        'sp_id'         => $request->channel->sp_id,
        'ec_id'         => $request->channel->id,
        'platform_code' => $request->channel->platform_code,
    ]);
    if ($request->source == 'downstream') {
        $data['down_id'] = $request->down->id;
    }
    $me = \app\admin\model\merchant\MerchantEntry::create($data);
    $this->me = $me;
    if ($request->isNewMerchant) {
        if ($request->channel->source === 'up') {
            throw new \ba\Exception('上游通道无法直接新增商户，请完成进件');
        }
        $mch = \app\admin\model\merchant\Merchant::where([
            'user_id'     => $request->user->id,
            'merchant_id' => $request->mchData['merchant_id'],
            'ec_id'       => $request->channel->id,
        ])->find();
        if (empty($mch)) {
            $mch = \app\admin\model\merchant\Merchant::create([
                'user_id'       => $request->user->id,
                'sp_id'         => $request->channel->sp_id,
                'ec_id'         => $request->channel->id,
                'name'          => $request->mchData['name'],
                'platform_code' => $request->channel->platform_code,
                'merchant_id'   => $request->mchData['merchant_id'],
                'status'        => 1,
            ]);
        }
        $merchantService = new \app\common\service\MerchantService();
        $merchantService->setMerchantConfig($mch);
        \app\admin\model\merchant\EntryMerchant::bindMerchant($me->id, $mch->id);
        $me->status = 8;
        $me->save();
    }
    if ($request->isAgent) {
        \app\admin\model\agent\UserEntry::create([
            'agent_id' => $request->agent->id,
            'user_id'  => $request->user->id,
            'me_id'    => $me->id,
            'price'    => 0,
        ]);
    }
    if (!$request->draft && !$request->isNewMerchant) {
        $this->submit();
    }
    }

    public function submit(): void
    {
    $channel = $this->request->channel;
    if (empty($channel)) {
        throw new \ba\Exception('进件通道未设置');
    }
    $entry = \app\common\service\entry\EntryFactory::create($channel);
    $entry->apply($this->me);
    }

    public function delete(\app\admin\model\merchant\MerchantEntry|null $me = null, bool|null $delete = true): void
    {
    if (!$me) {
        return; // [opcode 对照] 形参可空，事件系统可能以 null 调用
    }
    $statusArr = [4, 5];
    if (in_array($me->status, $statusArr)) {
        throw new \ba\Exception($me->statusText . '，无法删除操作');
    }
    \think\facade\Db::startTrans();
    try {
        $log1 = \app\admin\model\UserMoneyLog::where('content', $me->business_id)->whereIn('type', [4, 7])->find();
        $log2 = \app\admin\model\UserMoneyLog::where('content', $me->business_id)->where('type', 5)->find();
        if (!$log2 && $log1 && $log1->money < 0) {
            \app\admin\model\UserMoneyLog::create([
                'user_id' => $log1->user_id,
                'money'   => $log1->money * -1,
                'type'    => \app\admin\model\UserMoneyLog::TYPE_RETUND_ENTRY_FEE,
                'content' => $me->business_id,
                'memo'    => '退还用户进件费用',
            ]);
        }
        if ($delete) {
            $me->delete();
        } else {
            $me->status = 7;
            $me->save();
        }
        \think\facade\Db::commit();
    } catch (\Exception $e) {
        \think\facade\Db::rollback();
        throw new \ba\Exception($e->getMessage());
    }
    }

    private static function generatebusinessno(): string
    {
    do {
        $no = date('YmdHis') . mt_rand(10000, 99999);
        $businessId = 'Y' . $no;
    } while (\app\admin\model\merchant\MerchantEntry::where('business_id', $businessId)->find());
    return $businessId;
    }

}
