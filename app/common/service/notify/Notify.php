<?php

// 还原自 Swoole Compiler 加密文件: Notify.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/notify/Notify.php

namespace app\common\service\notify;

class Notify{
    public function msgnotify(string|null $event = null, \app\admin\model\User|null $user = null, array|null $params = null): bool
    {
        $config = $user->userConfig->notify_config ?? [];
        if (!isset($config[$event . '_notify']) || $config[$event . '_notify'] !== 'open') {
            return false;
        }
        $notifyMethod = $config['notify_method'] ?? '';
        if ($notifyMethod === '') {
            throw new \ba\Exception('通知方式未配置');
        }
        // [opcode 对照] GET_CALLED_CLASS(Template)+where/where/status=1/find 链
        $template = \app\admin\model\notify\Template::where('event', $event)
            ->where('notify_type', $notifyMethod)
            ->where('status', 1)
            ->find();
        if (!$template) {
            throw new \ba\Exception('消息模板不存在');
        }
        $eventConfig = \app\common\service\notify\TemplateParser::getTpConfig($event);
        if (!$eventConfig) {
            throw new \ba\Exception('通知事件配置不存在');
        }
        $keys = array_keys($eventConfig['vars'] ?? []);
        // [opcode 对照] sms: (string)content；否则 title."\n".content，用于提取占位变量
        $content = $notifyMethod === 'sms' ? (string)$template['content'] : (string)$template['title'] . "\n" . $template['content'];
        $vars = array_values(array_intersect(\app\common\service\notify\TemplateParser::extractVars($content), $keys));
        try {
            $built = $this->buildVars($event, $user, $eventConfig['event_type'], $vars, $params);
            $title = \app\common\service\notify\TemplateParser::render((string)$template['title'], $built);
            // [opcode 对照] renderSmsContent 在加密原件中仅 1 参（与已还原 TemplateParser::renderSmsContent(string $text) 一致）
            $content = $notifyMethod === 'sms' ? \app\common\service\notify\TemplateParser::renderSmsContent((string)$template['content']) : \app\common\service\notify\TemplateParser::render((string)$template['content'], $built);
            if ($notifyMethod == 'ems') {
                if (empty($user->email)) {
                    throw new \ba\Exception('用户邮箱为空');
                }
                $ems = new \app\common\service\notify\channel\Ems();
                // [opcode 对照] Ems::send(title, content, email)
                $ems->send($title, $content, $user->email);
                return true;
            }
            if (empty($user->mobile)) {
                throw new \ba\Exception('用户手机号为空');
            }
            $sms = new \app\common\service\notify\channel\Sms();
            // [opcode 对照] Sms::send(content, mobile, template_id, built)
            $sms->send($content, $user->mobile, $template['template_id'], $built);
            return true;
        } catch (\Exception $e) {
            throw new \ba\Exception('发送通知失败：' . $e->getMessage());
        }
    }
    private function buildvars(string|null $event = null, \app\admin\model\User|null $user = null, string|null $event_type = null, array|null $keys = null, array|null $params = null): array
    {
        $vars = [];
        if (!$keys) {
            return $vars;
        }
        $keysMap = array_fill_keys($keys, true);
        $config = get_sys_config('', 'basics');
        foreach ($keys as $key) {
            if (isset($user[$key])) {
                $vars[$key] = $this->formatVarValue($key, $user[$key]);
            }
            if (isset($config[$key])) {
                $vars[$key] = $this->formatVarValue($key, $config[$key]);
            }
        }
        if (isset($keysMap['user_id'])) {
            $vars['user_id'] = $user->id;
        }
        switch ($event_type) {
            case 'user':
                $this->setVar($vars, $keysMap, 'login_time', $user->last_login_time);
                $this->setVar($vars, $keysMap, 'ip', $user->last_login_ip);
                $this->setVar($vars, $keysMap, 'reg_time', $user->join_time ?? $user->create_time);
                if ($event == 'user_register') {
                    $this->setVar($vars, $keysMap, 'join_ip', $user->join_ip);
                }
                break;
            case 'user_money':
                break;
            case 'order':
                if (empty($params['order_id'])) {
                    throw new \ba\Exception('订单ID不能为空');
                }
                $order = \app\admin\model\Order::find($params['order_id']);
                if (!$order) {
                    throw new \ba\Exception('订单不存在');
                }
                foreach ($keys as $key) {
                    if (isset($order[$key])) {
                        $vars[$key] = $this->formatVarValue($key, $order[$key]);
                    }
                }
                $this->setVar($vars, $keysMap, 'order_id', $order->id);
                $this->setVar($vars, $keysMap, 'order_amount', $order->money);
                $this->setVar($vars, $keysMap, 'channel_type', $order->channel->channelTypeTable->name ?? '未知');
                break;
            case 'channel':
                if (empty($params['channel_id'])) {
                    throw new \ba\Exception('通道ID不能为空');
                }
                $account = \app\admin\model\channel\Account::find($params['channel_id']);
                if (!$account) {
                    throw new \ba\Exception('通道不存在');
                }
                foreach ($keys as $key) {
                    if (isset($account[$key])) {
                        $vars[$key] = $this->formatVarValue($key, $account[$key]);
                    }
                }
                $this->setVar($vars, $keysMap, 'channel_id', $account->id);
                $this->setVar($vars, $keysMap, 'pay_type', $account->payTypeTable->name ?? '未知');
                $this->setVar($vars, $keysMap, 'channel_type', $account->channelTypeTable->name ?? '未知');
                break;
        }
        foreach ($keys as $key) {
            if (array_key_exists($key, $params)) {
                $vars[$key] = $this->formatVarValue($key, $params[$key]);
            }
        }
        return $vars;
    }
    // [opcode 对照] $vars 形参为引用传递（SEND_REF + arg flag 0x2000000），补回签名中丢失的 &
    private function setvar(array|null &$vars = null, array|null $keyMap = null, string|null $key = null, mixed $value = null): void
    {
        if (!isset($keyMap[$key])) {
            return;
        }
        $vars[$key] = $this->formatVarValue($key, $value);
    }
    private function formatvarvalue(string|null $key = null, mixed $value = null): mixed
    {
        // [opcode 对照] 仅 time 类字段做时间格式化，其余字段原样返回（受保护比较指令取语义真值）
        if (stripos($key, 'time') === false) {
            return $value;
        }
        if ($value === null || $value === '') {
            return '';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_numeric($value)) {
            $ts = (int)$value;
            if ($ts == 0) {
                return '';
            }
            if ($ts > 9999999999) {
                $ts = (int)floor($ts / 1000);
            }
            return date('Y-m-d H:i:s', $ts);
        }
        $ts = strtotime((string)$value);
        if (is_int($ts)) {
            return (string)$value;
        }
        return date('Y-m-d H:i:s', $ts);
    }
}
