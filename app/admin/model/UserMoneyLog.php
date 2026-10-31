<?php

namespace app\admin\model;

use Throwable;
use think\model;
use think\Exception;
use think\model\relation\BelongsTo;

/**
 * UserMoneyLog 模型
 * 1. 创建余额日志自动完成会员余额的添加
 * 2. 创建余额日志时，请开启事务
 */
class UserMoneyLog extends model
{
    // [opcode 真值 2026-09-22] 加密原件常量表解引用（部署版缺失，SubscribeService 等引用会 fatal）
    const TYPE_RECHARGE = 1;
    const TYPE_BUY_PACKAGE = 2;
    const TYPE_ORDER_FEE = 3;
    const TYPE_ENTRY_FEE = 4;
    const TYPE_RETUND_ENTRY_FEE = 5;
    const TYPE_BUY_AGENT = 6;
    const TYPE_AGENT_ENTRY = 7;
    const TYPE_DOWN_ENTRY = 8;
    const TYPE_SITE_OPEN = 9;
    const TYPE_SITE_RENEW = 10;
    const TYPE_SITE_UPGRADE = 11;
    const TYPE_WALLET_TRANSFER = 12;

    protected $autoWriteTimestamp = true;
    protected $updateTime         = false;

    /**
     * 入库前
     * @throws Throwable
     */
    public static function onBeforeInsert($model): void
    {
        $user = User::where('id', $model->user_id)->lock(true)->find();
        if (!$user) {
            throw new Exception("The user can't find it");
        }
        if (!$model->memo) {
            throw new Exception("Change note cannot be blank");
        }
        $model->before = $user->money;

        $user->money += $model->money;
        $user->save();

        $model->after = $user->money;
    }

    public static function onBeforeDelete(): bool
    {
        return false;
    }

    public function getMoneyAttr($value): string
    {
        return bcdiv($value, 100, 2);
    }

    public function setMoneyAttr($value): string
    {
        return bcmul($value, 100, 2);
    }

    public function getBeforeAttr($value): string
    {
        return bcdiv($value, 100, 2);
    }

    public function setBeforeAttr($value): string
    {
        return bcmul($value, 100, 2);
    }

    public function getAfterAttr($value): string
    {
        return bcdiv($value, 100, 2);
    }

    public function setAfterAttr($value): string
    {
        return bcmul($value, 100, 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}