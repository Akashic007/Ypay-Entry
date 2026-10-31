<?php

// 还原自 Swoole Compiler 加密文件: TemplateParser.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/notify/TemplateParser.php

namespace app\common\service\notify;

class TemplateParser{
    // [opcode 对照] 常量值取自加密原件 payload 常量表真值（部署版曾丢失为 null）
    const VAR_PATTERN = '/\{\$([a-zA-Z_][a-zA-Z0-9_]*)\}/';
    public static function gettpconfig(string|null $event = null): array
    {
        $config = config('notify.template');
        if (empty($config)) {
            return [];
        }
        foreach ($config as $item) {
            if ($event == $item['event']) {
                return $item;
            }
        }
        return [];
    }

    public static function extractvars(string|null $text = null): array
    {
        if ($text == '') {
            return [];
        }
        preg_match_all(self::VAR_PATTERN, $text, $matches);
        return array_values(array_unique($matches[1] ?? []));
    }

    public static function render(string|null $text = null, array|null $vars = null): string
    {
        if ($text == '') {
            return $text;
        }
        if ($vars == []) {
            return $text;
        }
        // [opcode 对照] 闭包体在 opcode 缓存中未存留，按 VAR_PATTERN 捕获组语义还原：未命中的占位符保留原文
        return (string) preg_replace_callback(self::VAR_PATTERN, function ($match) use ($vars) {
            return $vars[$match[1]] ?? $match[0];
        }, $text);
    }

    public static function rendersmscontent(string|null $text = null): string
    {
        if ($text == '') {
            return $text;
        }
        return (string) preg_replace(self::VAR_PATTERN, '$(\1)', $text);
    }

}
