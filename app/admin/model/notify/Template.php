<?php

// 还原自 Swoole Compiler 加密文件: Template.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/notify/Template.php

namespace app\admin\model\notify;

class Template extends \think\Model {
    protected $name = 'notify_template';
    protected $autoWriteTimestamp = true;
    protected $type = ['template_ids' => 'array'];
    public static function normalizenotifytype(string|null $v = null): string
    {
        // [opcode 还原] trim + email→ems 归一化
        $v = trim($v);
        if ($v === 'email') {
            return 'ems';
        }
        return $v;
    }

    public function getcontentattr($value): string
    {
        // [opcode 还原] $value ? htmlspecialchars_decode($value) : ''
        return $value ? htmlspecialchars_decode($value) : '';
    }

    public function setnotifytypeattr($value): string
    {
        // [opcode 还原] static::normalizeNotifyType((string)$value)
        return static::normalizeNotifyType((string)$value);
    }

    public static function onbeforewrite(\app\admin\model\notify\Template|null $model = null): void
    {
        // [opcode 还原] 归一化 notify_type 并做 event+notify_type 唯一性校验
        $event = trim((string)$model->getAttr('event'));
        $notifyType = static::normalizeNotifyType((string)$model->getAttr('notify_type'));
        if ($event === '' || $notifyType === '') {
            return;
        }
        $model->notify_type = $notifyType;

        $query = (new static())->newQuery();
        $query = $query->where('event', $event)->where('notify_type', function ($q) use ($notifyType) {
            // [opcode 还原] 闭包体取自 dynamic_func_defs；ems 同时匹配历史 email 值
            if ($notifyType === 'ems') {
                return $q->whereIn('notify_type', ['ems', 'email']);
            }
            return $q->where('notify_type', $notifyType);
        });
        $pk = $model->getPk();
        $id = $model->getKey();
        if ($id) {
            $query->where($pk, '<>', $id);
        }
        if (0 < $query->count()) {
            throw new \think\exception\ValidateException('该事件与通知类型已存在一条模板，请勿重复添加');
        }
    }

}
