<?php

// 还原自 Swoole Compiler 加密文件: Config.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/Config.php
// 本类为 BuildAdmin 框架件（方法集与原版一致），访问器方法体按原版重建；
// $jsonDecodeType 为 YPay 定制：在原版基础上增加 secretKey/epayConfig（支付配置项），
// opcode 真值中的 null 字面量已按 fail-safe 去除。$cacheTag 缺省值按原版补回 'sys_config'
//（缺省时静态类型属性未初始化即致命，get_sys_config 全线不可用）。

namespace app\admin\model;

use think\facade\Cache;

class Config extends \think\Model {
    public static string $cacheTag = 'sys_config';

    protected array $needContent = ['radio', 'checkbox', 'select', 'selects'];
    protected array $jsonDecodeType = ['checkbox', 'array', 'selects', 'secretKey', 'epayConfig', 'ocrConfig', 'loginPageImg', 'realNameConfig', 'captchaConfig'];
    protected $append = [
        'value',
        'content',
        'extend',
        'input_extend',
    ];

    public static function onbeforeinsert(\app\admin\model\Config|null $model = null): void
    {
        if (!in_array($model->getData('type'), $model->needContent)) {
            $model->content = null;
        } else {
            $model->content = json_encode(str_attr_to_array($model->getData('content')));
        }
        if (is_array($model->rule)) {
            $model->rule = implode(',', $model->rule);
        }
        if ($model->getData('extend') || $model->getData('inputExtend')) {
            $extend      = str_attr_to_array($model->getData('extend'));
            $inputExtend = str_attr_to_array($model->getData('inputExtend'));
            if ($inputExtend) $extend['baInputExtend'] = $inputExtend;
            if ($extend) $model->extend = json_encode($extend);
        }
        $model->allow_del = 1;
    }

    public static function onafterwrite(): void
    {
        // 清理配置缓存
        Cache::tag(self::$cacheTag)->clear();
    }

    public function getvalueattr($value, $row)
    {
        if (!isset($row['type']) || $value == '0') return $value;
        if (in_array($row['type'], $this->jsonDecodeType)) {
            return empty($value) ? [] : json_decode($value, true);
        } elseif ($row['type'] == 'switch') {
            return (bool)$value;
        } elseif ($row['type'] == 'editor') {
            return !$value ? '' : htmlspecialchars_decode($value);
        } elseif (in_array($row['type'], ['city', 'remoteSelects'])) {
            if (!$value) return [];
            if (!is_array($value)) return explode(',', $value);
            return $value;
        } else {
            return $value ?: '';
        }
    }

    public function setvalueattr(mixed $value = null, $row): mixed
    {
        if (in_array($row['type'], $this->jsonDecodeType)) {
            return $value ? json_encode($value) : '';
        } elseif ($row['type'] == 'switch') {
            return $value ? '1' : '0';
        } elseif ($row['type'] == 'time') {
            return $value ? date('H:i:s', strtotime($value)) : '';
        } elseif ($row['type'] == 'city') {
            if ($value && is_array($value)) {
                return implode(',', $value);
            }
            return $value ?: '';
        } elseif (is_array($value)) {
            return implode(',', $value);
        }

        return $value;
    }

    public function getcontentattr($value, $row)
    {
        if (!isset($row['type'])) return '';
        if (in_array($row['type'], $this->needContent)) {
            $arr = json_decode($value, true);
            return $arr ?: [];
        } else {
            return '';
        }
    }

    public function getextendattr($value)
    {
        if ($value) {
            $arr = json_decode($value, true);
            if ($arr) {
                unset($arr['baInputExtend']);
                return $arr;
            }
        }
        return [];
    }

    public function getinputextendattr($value, $row)
    {
        if ($row && $row['extend']) {
            $arr = json_decode($row['extend'], true);
            if ($arr && isset($arr['baInputExtend'])) {
                return $arr['baInputExtend'];
            }
        }
        return [];
    }

}
