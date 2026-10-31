<?php

// 还原自 Swoole Compiler 加密文件: ChannelService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/ChannelService.php

namespace app\common\service;

class ChannelService{
    public static array $monitor;
    public static array $supplyChannel;

    public function create(\app\admin\model\User|null $user = null, array|null $params = null): \app\admin\model\channel\Account
    {
        $this->paramsCheck($params);
        // [opcode 对照] in_array($params['channel_type'], self::$supplyChannel) 为真时才回填商户上游来源
        if (in_array($params['channel_type'], self::$supplyChannel)) {
            if (empty($params['source_type'])) {
                $source = $this->setSource($params['mch_id']);
                $params['source_type'] = $source['source_type'];
                if ($params['source_type'] == 'up') {
                    $params['source_id'] = $source['source_id'];
                }
            }
        }
        \think\facade\Db::startTrans();
        try {
            $account = new \app\admin\model\channel\Account();
            $account->user_id = $user->id;
            $account->pay_type = $params['pay_type'];
            $account->channel_type = $params['channel_type'];
            $account->mch_id = $params['mch_id'] ?? 0;
            $account->config = $params['config'] ?? [];
            $account->remark = $params['remark'] ?? '';
            $account->status = $params['status'] ?? 1;
            $account->online_status = $params['online_status'] ?? 1;
            $account->pay_config = $params['pay_config'] ?? [];
            $account->extend = $params['extend'] ?? [];
            $account->source_type = $params['source_type'] ?? 'local';
            $account->source_id = $params['source_id'] ?? 0;
            $account->source_cid = $params['source_cid'] ?? 0;
            $account->save();
            $this->handleParams($user, $account, 'add', $params);
            $factory = \app\common\service\channel\ChannelFactory::create($account);
            $factory->handleParams($params);
            \think\facade\Db::commit();
        } catch (\Throwable $e) {
            \think\facade\Db::rollback();
            throw new \ba\Exception($e->getMessage());
        }
        \think\facade\Event::trigger(new \app\common\event\ChannelNotify($account, \app\common\event\ChannelNotify::CREATE));
        return $account;
    }

    public function edit(\app\admin\model\User|null $user = null, \app\admin\model\channel\Account|null $account = null, array|null $params = null): \app\admin\model\channel\Account
    {
        \think\facade\Db::startTrans();
        try {
            if (isset($params['config'])) {
                $account->config = $params['config'];
            }
            if (isset($params['remark'])) {
                $account->remark = $params['remark'];
            }
            if (isset($params['status'])) {
                $account->status = $params['status'];
            }
            if (isset($params['online_status'])) {
                $account->online_status = $params['online_status'];
            }
            if (isset($params['weigh'])) {
                $account->weigh = $params['weigh'];
            }
            if (isset($params['pay_config'])) {
                $account->pay_config = $params['pay_config'];
            }
            if (isset($params['extend'])) {
                $account->extend = $params['extend'];
            }
            $this->handleParams($user, $account, 'edit', $params);
            $factory = \app\common\service\channel\ChannelFactory::create($account);
            $factory->handleParams($params, 'edit');
            $account->save();
            \think\facade\Db::commit();
        } catch (\Throwable $e) {
            \think\facade\Db::rollback();
            throw new \ba\Exception($e->getMessage());
        }
        \think\facade\Event::trigger(new \app\common\event\ChannelNotify($account, \app\common\event\ChannelNotify::UPDATE));
        return $account;
    }

    public function delete(\app\admin\model\channel\Account|null $account = null): void
    {
        \think\facade\Db::startTrans();
        try {
            $factory = \app\common\service\channel\ChannelFactory::create($account);
            $factory->handleDelete();
            $account->delete();
            \think\facade\Db::commit();
        } catch (\Exception $e) {
            \think\facade\Db::rollback();
            throw new \ba\Exception($e->getMessage());
        }
    }

    public function handleparams(\app\admin\model\User|null $user = null, \app\admin\model\channel\Account|null $account = null, string|null $scene = null, array|null $params = null): void
    {
        if (empty($account->pay_type)) {
            throw new \ba\Exception('通道支付类型为空');
        }
        if (empty($account->channel_type)) {
            throw new \ba\Exception('通道类型为空');
        }
        if (empty($account->channelTypeTable)) {
            throw new \ba\Exception('通道类型不存在');
        }
        if ($account->channelTypeTable['pay_type'] != $account->pay_type) {
            throw new \ba\Exception('通道支付类型错误');
        }
        if ($account->channelTypeTable['status'] != 1) {
            throw new \ba\Exception('通道类型已禁用');
        }
        if ($account->payTypeTable['status'] != 1) {
            throw new \ba\Exception('通道支付类型已禁用');
        }
        if (empty($params['source_type'])) {
            $params['source_type'] = 'local';
        }
        if ($params['source_type'] != 'down') {
            if ($user->subscribe_id == '0' || empty($user->subscribe_id)) {
                throw new \ba\Exception('请先开通会员套餐');
            }
            $subscribe = \app\admin\model\user\Subscribe::find($user->subscribe_id);
            if (empty($subscribe)) {
                throw new \ba\Exception('会员套餐不存在');
            }
            // [opcode 对照] subscribe_expire_status 为 User 模型 append 访问器（见 admin/model/User.php）
            if (!$user->subscribe_expire_status) {
                throw new \ba\Exception('会员套餐已到期');
            }
            if ($subscribe->status != 1) {
                throw new \ba\Exception('会员套餐已被禁用');
            }
            if ($scene == 'add') {
                $count = \app\admin\model\channel\Account::where('user_id', $user->id)->count();
                if (empty($subscribe->content['channel_number']) || $subscribe->content['channel_number'] <= 0) {
                    throw new \ba\Exception('当前套餐不可添加通道');
                }
                if ($subscribe->content['channel_number'] < $count) {
                    throw new \ba\Exception('通道数量已达到会员套餐上限：' . $subscribe->content['channel_number']);
                }
                if (0 < $subscribe->group_id) {
                    $found = false;
                    $subscribeGroup = $subscribe->subscribeGroup;
                    foreach ($subscribeGroup->channelTypes as $type) {
                        if ($type->type == $account->channel_type) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        throw new \ba\Exception('当前套餐组分未包含此通道类型');
                    }
                }
            }
        }
    }

    public function channeldispatcher(array|null $channels = null, string|null $mode = null): array
    {
        $allowed = ['weigh_polling', 'random', 'weigh_priority'];
        if (!in_array($mode, $allowed)) {
            $mode = 'random';
        }
        switch ($mode) {
            case 'weigh_polling':
                return $this->weightedShuffle($channels);
            case 'weigh_priority':
                usort($channels, function ($a, $b) {
                    return $b['weigh'] <=> $a['weigh'];
                });
                break;
            case 'random':
                shuffle($channels);
                break;
            default:
                break;
        }
        return $channels;
    }

    protected function pickone($channels)
    {
        $filtered = array_filter($channels, function ($c) {
            return (bool)$this->getPositiveWeight($c);
        });
        if (empty($filtered)) {
            return $channels[array_rand($channels)];
        }
        $sum = array_sum(array_map(function ($c) {
            return $this->getPositiveWeight($c);
        }, $filtered));
        if ($sum <= 0) {
            return $channels[array_rand($channels)];
        }
        $rand = $sum * (mt_rand() / mt_getrandmax());
        $accumulated = 0;
        foreach ($filtered as $channel) {
            $accumulated += $this->getPositiveWeight($channel);
            if ($rand <= $accumulated) {
                return $channel;
            }
        }
        return $channels[array_rand($channels)];
    }

    protected function weightedshuffle(array|null $channels = null): array
    {
        $positive = [];
        while (!empty($channels)) {
            $sum = array_sum(array_map(function ($c) {
                return $this->getPositiveWeight($c);
            }, $channels));
            if ($sum <= 0) {
                shuffle($channels);
                return array_merge($positive, array_values($channels));
            }
            $picked = $this->pickOne($channels);
            $positive[] = $picked;
            foreach ($channels as $key => $channel) {
                if ($channel['id'] == $picked['id']) {
                    unset($channels[$key]);
                    break;
                }
            }
            $channels = array_values($channels);
        }
        return $positive;
    }

    protected function getpositiveweight(array|null $channel = null): float
    {
        if (!isset($channel['weigh']) || !is_numeric($channel['weigh'])) {
            return 0.0;
        }
        $weigh = (float)$channel['weigh'];
        if (0.0 < $weigh) {
            return $weigh;
        }
        return 0.0;
    }

    private function setsource(int|null $mch_id = null): array
    {
        $merchant = \app\admin\model\merchant\Merchant::find($mch_id);
        $result = ['source_type' => $merchant->source];
        if ($result['source_type'] == 'up') {
            $result['source_id'] = $merchant->config['up_id'];
        }
        return $result;
    }

    private function paramscheck(array|null $params = null): void
    {
        if (empty($params['pay_type'])) {
            throw new \ba\Exception('支付类型为空');
        }
        if (empty($params['channel_type'])) {
            throw new \ba\Exception('通道类型为空');
        }
        if (in_array($params['channel_type'], self::$supplyChannel)) {
            if (empty($params['mch_id'])) {
                throw new \ba\Exception('商户ID为空');
            }
        }
    }
}
