<?php

// 还原自 Swoole Compiler 加密文件: AllowCrossDomain.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/middleware/AllowCrossDomain.php
// $header 属性默认值为 opcode 真值恢复。handle() 原始方法体数据流断链（$T6 未定义、
// 动态 Origin 计算与 allowCrossDomainHeaders 传递丢失）无法直接运行，流程按 CORS
// 标准语义重建；Allow-Credentials=true 时 Origin 必须回显请求 Origin（不能输出 *）。
// 精确还原（如对 Origin 的白名单校验）待 opcode 对照后覆盖本方法。

namespace app\common\middleware;

class AllowCrossDomain{
    protected array $header = ['Access-Control-Allow-Credentials' => 'true', 'Access-Control-Max-Age' => 1800, 'Access-Control-Allow-Methods' => '*', 'Access-Control-Allow-Headers' => '*'];
    public function handle(\think\Request|null $request = null, \Closure|null $next = null, null|array $header = null): \think\Response
    {
        $header = array_merge($this->header, (array) ($header ?? []));

        if ($origin = (string) $request->header('origin', '')) {
            $header['Access-Control-Allow-Origin'] = $origin;
            $header['Vary'] = 'Origin';
        }

        if (strtoupper($request->method()) === 'OPTIONS') {
            $response = \think\Response::create()->code(204);
        } else {
            try {
                $response = $next($request);
            } catch (\TypeError $e) {
                if (str_contains($e->getMessage(), 'strtolower')) {
                    $trace = array_map(fn($f) => ($f['file'] ?? '?') . ':' . ($f['line'] ?? '') . ' ' . ($f['class'] ?? '') . ($f['type'] ?? '') . ($f['function'] ?? ''), $e->getTrace());
                    \think\facade\Log::write('strtolower-null-trace: ' . json_encode($trace, JSON_UNESCAPED_SLASHES), 'notice');
                }
                throw $e;
            }
        }

        $response->header($header);

        return $response;
    }
}
