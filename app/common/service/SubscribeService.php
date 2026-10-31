<?php

// 还原自 Swoole Compiler 加密文件: SubscribeService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/SubscribeService.php
// 方法体按加密原件 opcode 真值重建（批次 B60）

namespace app\common\service;

class SubscribeService{
    public function usersubscribe(\app\admin\model\User|null $user = null, \app\admin\model\user\Subscribe|null $subscribe = null): array
    {
        if ($subscribe->duration <= 0) {
            throw new \Exception('套餐时长为0');
        }

        if ($user->subscribe_id == '0' || $user->sub_expire_time < time()) {
            // 无有效套餐：新开通
            \think\facade\Db::startTrans();
            try {
                if (0 < $subscribe->price) {
                    \app\admin\model\UserMoneyLog::create([
                        'user_id' => $user->id,
                        'money'   => $subscribe->price * -1,
                        'type'    => \app\admin\model\UserMoneyLog::TYPE_BUY_PACKAGE,
                        'memo'    => '用户开通套餐-[' . $subscribe->name . ']',
                    ]);
                }
                $user->subscribe_id          = $subscribe->id;
                $user->subscribe_expire_time = $this->calculateExpireTime(time(), $subscribe->duration);
                $user->save();
                \think\facade\Db::commit();
                return ['code' => 1, 'msg' => '开通套餐成功'];
            } catch (\Exception $e) {
                \think\facade\Db::rollback();
                throw new \Exception($e->getMessage());
            }
        } elseif ($user->subscribe_id == $subscribe->id) {
            // 同套餐：续费（从当前到期时间顺延）
            \think\facade\Db::startTrans();
            try {
                \app\admin\model\UserMoneyLog::create([
                    'user_id' => $user->id,
                    'money'   => $subscribe->price * -1,
                    'type'    => \app\admin\model\UserMoneyLog::TYPE_BUY_PACKAGE,
                    'memo'    => '用户开通套餐-[' . $subscribe->name . ']',
                ]);
                $user->subscribe_id          = $subscribe->id;
                $user->subscribe_expire_time = $this->calculateExpireTime($user->sub_expire_time, $subscribe->duration);
                $user->save();
                \think\facade\Db::commit();
                return ['code' => 1, 'msg' => '续费套餐成功'];
            } catch (\Exception $e) {
                \think\facade\Db::rollback();
                throw new \Exception($e->getMessage());
            }
        } else {
            // 不同套餐：升级（剩余价值折算成新套餐天数）
            $oldPackage = \app\admin\model\user\Subscribe::find($user->subscribe_id);
            $oldPerDay  = $oldPackage->price / $oldPackage->duration;
            $newPerDay  = $subscribe->price / $subscribe->duration;
            if ($newPerDay < $oldPerDay) {
                throw new \Exception('当前不支持降级续费');
            }
            $remainingValue = $this->calculateRemainingValue($user, $oldPackage);
            $days           = $this->calculateDaysFromValue($remainingValue, $subscribe);
            $totalDays      = $days + $subscribe->duration;
            \think\facade\Db::startTrans();
            try {
                \app\admin\model\UserMoneyLog::create([
                    'user_id' => $user->id,
                    'money'   => $subscribe->price * -1,
                    'type'    => \app\admin\model\UserMoneyLog::TYPE_BUY_PACKAGE,
                    'memo'    => '用户开通套餐-[' . $subscribe->name . ']',
                ]);
                $user->subscribe_id          = $subscribe->id;
                $user->subscribe_expire_time = $this->calculateExpireTime(time(), $totalDays);
                $user->save();
                \think\facade\Db::commit();
                return ['code' => 1, 'msg' => '套餐升级成功，原套餐已换算成新套餐[+' . $days . '天]'];
            } catch (\Exception $e) {
                \think\facade\Db::rollback();
                throw new \Exception($e->getMessage());
            }
        }
    }

    private function calculateexpiretime($startTime, int|null $day = null): int
    {
        // [opcode 对照] 比较指令 op=-1(专用 handler 3103，全树无标定)；`<` 依异常文案「天数不能为负数」判定（`<=` 为 handler 627，已排除）
        if ($day < 0) {
            throw new \InvalidArgumentException('天数不能为负数');
        }
        if (!istimestamp($startTime)) {
            $startTime = strtotime($startTime);
        }
        return $startTime + $day * 86400;
    }

    private function calculateremainingvalue(\app\admin\model\User|null $user = null, \app\admin\model\user\Subscribe|null $subscribe = null): float
    {
        $remainingDays = max(0, ($user->sub_expire_time - time()) / 86400);
        return round($remainingDays * ($subscribe->price / $subscribe->duration), 2);
    }

    private function calculatedaysfromvalue(float|null $value = null, \app\admin\model\user\Subscribe|null $subscribe = null): int
    {
        // [opcode 对照] 比较指令 op=-1(专用 handler 3178，全树无标定)；`==`（`<=`=627、`<`=552/3103 均已排除）；其后续 max(0,…) 使 `==`/`<=` 行为等价
        if ($value == 0.0) {
            return 0;
        }
        if ($subscribe->price <= 0) {
            return 0;
        }
        if ($subscribe->duration <= 0) {
            throw new \InvalidArgumentException('目标套餐时长小于0');
        }
        $daily = $subscribe->price / $subscribe->duration;
        if ($daily <= 0) {
            return 0;
        }
        return max(0, (int) floor($value / $daily));
    }

}
