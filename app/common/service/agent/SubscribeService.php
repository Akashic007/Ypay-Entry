<?php

// 还原自 Swoole Compiler 加密文件: SubscribeService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/agent/SubscribeService.php

namespace app\common\service\agent;

class SubscribeService{
    public function agentsubscribe(\app\admin\model\User|null $user = null, \app\admin\model\agent\Subscribe|null $subscribe = null): \app\admin\model\agent\Agent
    {
        $agent = \app\admin\model\agent\Agent::where('user_id', $user->id)->find();
        if ($agent) {
            if ($agent->subscribe_id == $subscribe->id) {
                throw new \Exception('您已是此套餐，请勿重复开通');
            }
            $oldSubscribe = \app\admin\model\agent\Subscribe::where('id', $agent->subscribe_id)->find();
            if ($subscribe->price < $oldSubscribe->price) {
                throw new \Exception('代理套餐无法降级开通');
            }
            $diff = $subscribe->price - $oldSubscribe->price;
            if ($user->money < $diff) {
                throw new \Exception('用户余额不足');
            }
            \think\facade\Db::startTrans();
            try {
                if (0 < $diff) {
                    // [opcode 对照] 余额变动由 UserMoneyLog::onBeforeInsert 观察者自动落账
                    \app\admin\model\UserMoneyLog::create([
                        'user_id' => $user->id,
                        'type'    => \app\admin\model\UserMoneyLog::TYPE_BUY_AGENT,
                        'money'   => $subscribe->price,
                        'memo'    => '用户将代理套餐[' . $oldSubscribe->name . ']升级到[' . $subscribe->name . ']',
                    ]);
                }
                $agent->subscribe_id = $subscribe->id;
                $agent->save();
                \think\facade\Db::commit();
            } catch (\Exception $e) {
                \think\facade\Db::rollback();
            }
        } else {
            if ($user->money < $subscribe->price) {
                throw new \Exception('用户余额不足');
            }
            \think\facade\Db::startTrans();
            try {
                if (0 < $subscribe->price) {
                    \app\admin\model\UserMoneyLog::create([
                        'user_id' => $user->id,
                        'type'    => \app\admin\model\UserMoneyLog::TYPE_BUY_AGENT,
                        'money'   => $subscribe->price,
                        'memo'    => '用户开通代理套餐[' . $subscribe->name . ']',
                    ]);
                }
                // [opcode 对照] withdrawal_money 列当前 as_agent 表不存在，think-orm 保存时自动过滤
                $agent = \app\admin\model\agent\Agent::create([
                    'user_id'          => $user->id,
                    'subscribe_id'     => $subscribe->id,
                    'withdrawal_money' => 0,
                    'status'           => 1,
                ]);
                \think\facade\Db::commit();
            } catch (\Exception $e) {
                \think\facade\Db::rollback();
                throw new \Exception('开通失败：' . $e->getMessage());
            }
        }
        return $agent;
    }

}
