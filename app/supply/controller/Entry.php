<?php

// 还原自 Swoole Compiler 加密文件: Entry.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/supply/controller/Entry.php

namespace app\supply\controller;

class Entry extends \app\common\controller\SupplyBase {
    public function getchannellist(): void
    {
        $spId = (int)($this->request->param('sp_id', 0) ?? 0);
        $query = \app\admin\model\EntryChannel::hasWhere('sp', function ($query) {
            $query->where('status', 1);
        })->where('status', 1)->where('source', 'local')->with(['sp' => function ($query) {
            $query->field(['id', 'name', 'logo']);
        }, 'agentDiscount' => function ($query) {
            $query->where('agent_id', $this->agent->id);
        }]);
        if ($spId > 0) {
            $query = $query->where('sp_id', $spId);
        }
        $list = $query->select();
        $return = [];
        $channelType = config('entry.channel_type');
        $agentService = new \app\common\service\AgentService();
        foreach ($list as $v) {
            $price = $v->price;
            $orderRate = $v->order_rate;
            $rateService = new \app\common\service\RateService($this->user, $this->agent);
            try {
                $price = $agentService->calculateEntryDiscountAmount($this->agent, $v);
                $orderRate = $rateService->getUserRate($v->id);
            } catch (\Exception $e) {
            }
            $return[] = [
                'id'            => $v->id,
                'sp_id'         => $v->sp_id,
                'name'          => $v->name,
                'platform_code' => $v->platform_code,
                'price'         => $price,
                'order_rate'    => $orderRate,
                'status'        => $v->status,
                'sp_name'       => $v->sp['name'],
                'channel_name'  => getEntryChannelName($v->platform_code)['name'] . '[' . $v->sp['name'] . ']',
                'platform_name' => self::getChannelTypeByCode($channelType, $v->platform_code)['name'],
            ];
        }
        $this->success('ok', $return);
    }


    public function getchannelinfo(): void
    {
        $ecId = $this->request->param('ec_id');
        if (empty($ecId)) {
            $this->error('进件渠道ID为空');
        }
        $channel = \app\admin\model\EntryChannel::hasWhere('sp', function ($query) {
            $query->where('status', 1);
        })->where('status', 1)->hidden(['config', 'price', 'mch_id'])->with(['sp' => function ($query) {
            $query->field(['id', 'name', 'logo']);
        }, 'agentDiscount' => function ($query) {
            $query->where('agent_id', $this->agent->id);
        }])->find($ecId);
        if (empty($channel)) {
            $this->error('进件通道不存在');
        }
        $price = $channel['price'];
        if (isset($channel->agentDiscount) && !empty($channel->agentDiscount[0]['api_price'])) {
            $price = $channel->agentDiscount[0]['api_price'];
        }
        $result = [
            'id'            => $channel['id'],
            'sp_id'         => $channel['sp_id'],
            'platform_code' => $channel['platform_code'],
            'price'         => $price,
            'status'        => $channel['status'],
            'sp_name'       => $channel['sp']['name'],
            'platform_name' => getEntryChannelName($channel['platform_code'])['name'],
        ];
        if (!empty($channel['config']['payCapabilities'])) {
            $result['payCapabilities'] = json_encode($channel['config']['payCapabilities']);
        }
        $result['sign'] = \app\common\library\SignUtil::generateSign($result, $this->agent->api_key);
        $this->success('ok', $result);
    }


    public function apply(): void
    {
        if (!$this->request->isPost()) {
            $this->error('请求方式错误！');
        }
        $params = $this->request->post(['channel_id', 'merchant_data', 'draft', 'business_id']);
        if (empty($params['channel_id'])) {
            $this->error('进件渠道ID为空');
        }
        $channel = \app\admin\model\EntryChannel::find($params['channel_id']);
        if (empty($channel)) {
            $this->error('进件渠道不存在');
        }
        try {
            $user = \app\admin\model\User::find($this->agent->user_id);
            $request = new \app\common\service\entry\EntryRequest();
            $request->setChannel($channel);
            $request->setSource('downstream');
            $request->setUser($user);
            $request->setDownstream($this->downstream);
            if (!empty($params['merchant_data'])) {
                $request->setData($params['merchant_data']);
            }
            if ((isset($params['draft']) && $params['draft'] == 0) || $params['draft'] == '0') {
                $request->asDraft();
            }
            if (!empty($params['business_id'])) {
                $me = \app\admin\model\merchant\MerchantEntry::where('business_id', $params['business_id'])->find();
            }
            $service = new \app\common\service\EntryService($request);
            if (empty($me)) {
                $me = $service->apply();
            } else {
                $service->editData($me);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('申请成功', [
            'status'      => $me->status,
            'status_text' => $me->statusText,
            'business_id' => $me->business_id,
        ]);
    }



    public function getstatus(): void
    {
        $businessId = $this->request->param('business_id');
        if (empty($businessId)) {
            $this->error('业务编号为空');
        }
        $me = \app\admin\model\merchant\MerchantEntry::where('business_id', $businessId)->where('source', 'downstream')->find();
        if (empty($me)) {
            $this->error('申请记录不存在');
        }
        if ($me->user_id != $this->agent->user_id) {
            $this->error('暂无权限操作该记录');
        }
        $channel = \app\admin\model\EntryChannel::find($me->ec_id);
        if (empty($channel)) {
            $this->error('进件通道不存在');
        }
        try {
            $entry = \app\common\service\entry\EntryFactory::create($channel);
            $entry->getApplyInfo($me);
        } catch (\ba\Exception $e) {
            $this->error($e->getMessage());
        }
        $return = [
            'status'       => $me->status,
            'status_text'  => $me->statusText,
            'business_id'  => $me->business_id,
            'audit_result' => $me->audit_result,
            'apply_result' => $me->apply_result,
        ];
        if (!empty($me->merchant) && !empty($me->merchant->merchant_id)) {
            $return['merchant'] = [
                'merchant_id'   => $me->merchant->merchant_id,
                'name'          => $me->merchant->name,
                'platform_code' => $me->merchant->platform_code,
                'merchant_info' => $me->merchant->merchant_info,
                'config'        => $me->merchant->config,
                'status'        => $me->merchant->status,
            ];
        }
        $this->success('ok', $return);
    }


    public function mediaupload(): void
    {
        $file = $this->request->file('file');
        $config = \think\facade\Config::get('filesystem.disks.private');
        \think\facade\Config::set($config, 'upload_disks');
        $size = $file->getSize();
        $maxSize = 2097152;
        if ($maxSize < $size) {
            $this->error('进件资料文件不能超过2M');
        }
        try {
            $upload = new \app\common\library\Upload();
            $upload->setFile($file);
            $upload->setDriver('local');
            $upload->setTopic('entry');
            $attachment = $upload->upload(null, $this->agent->user_id);
            $attachment->disk = 'private';
            $attachment->save();
            event('EntryUpload', $attachment);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success('文件上传成功', $attachment);
    }


    private static function getchanneltypebycode(array|null $list = null, string|null $code = null): array
    {
        foreach ($list as $v) {
            if ($code === $v['code']) {
                return $v;
            }
        }
        return ['name' => '未知', 'code' => '未知'];
    }


}
