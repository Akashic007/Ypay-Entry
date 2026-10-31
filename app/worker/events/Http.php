<?php

// 还原自 Swoole Compiler 加密文件: Http.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/worker/events/Http.php

namespace app\worker\events;

class Http{
    protected static array $monitorConfig = [];
    protected static int $waitResponseCount = 0;
    protected static array $serverData;
    protected static array $bind = [];
    public function __construct()
    {
    }
    public function onworkerstart(\Workerman\Worker|null $worker = null): void
    {
        static::$bind['worker'] = $worker;
        static::$serverData = $_SERVER;
        if (!static::$monitorConfig) {
            static::$monitorConfig = \think\facade\Config::get('worker_monitor');
        }
        if (!isset(static::$bind['db'])) {
            try {
                \think\facade\Db::execute('SELECT 1');
                $app = \think\facade\App::getInstance();
                static::$bind['db'] = $app->db;
                static::$bind['cache'] = $app->cache;
            } catch (\think\db\exception\PDOException) {
            }
        }
        if ($worker->id == 0) {
            new \app\worker\library\Monitor(static::$monitorConfig);
        }
    }
    public function onmessage(\Workerman\Connection\TcpConnection|null $connection = null, \Workerman\Protocols\Http\Request|null $request = null): void
    {
        $app = new \app\worker\library\WorkerHttpApp(root_path());
        foreach (static::$bind as $key => $value) {
            $app->{$key} = $value;
        }
        $app->init($connection, $request, static::$serverData);
        $path = $request->path() ?: '/';
        $file = \ba\Filesystem::fsFit(public_path() . urldecode($path));
        if (!is_file($file)) {
            static::$waitResponseCount++;
            if (static::$monitorConfig['soft_reboot']) {
                if (!\app\worker\library\Monitor::isPaused()) {
                    \app\worker\library\Monitor::pause();
                }
            }
            while (1 < ob_get_level()) {
                ob_end_clean();
            }
            ob_start();
            $response = $app->http->run();
            $str = ob_get_clean();
            ob_start();
            $response->send();
            $app->http->end($response);
            $str .= ob_get_clean() ?: '';
            $connection->send(new \Workerman\Protocols\Http\Response($response->getCode(), $response->getHeader(), $str));
            static::$waitResponseCount--;
            if (static::$waitResponseCount <= 0 && static::$monitorConfig['soft_reboot']) {
                \Workerman\Timer::add(static::$monitorConfig['interval'], function () {
                    if (\app\worker\library\Monitor::isPaused()) {
                        \app\worker\library\Monitor::resume();
                    }
                }, [], false);
            }
        } else {
            $if_modified_since = $request->header('if-modified-since');
            if ($if_modified_since) {
                $last_modified = date('D, d M Y H:i:s', filemtime($file)) . ' ' . date_default_timezone_get();
                if ($last_modified === $if_modified_since) {
                    $connection->send(new \Workerman\Protocols\Http\Response(304));
                    return;
                }
            }
            $info = pathinfo($file);
            $response = (new \Workerman\Protocols\Http\Response())->withFile($file);
            if (!empty($info['extension'])) {
                $extension = strtolower($info['extension']);
                if (in_array($extension, \think\facade\Config::get('worker_http.staticServer.deny'))) {
                    $connection->send(new \Workerman\Protocols\Http\Response(404));
                    return;
                }
                if (in_array($extension, \think\facade\Config::get('worker_http.staticServer.attachment'))) {
                    $response = $response->withHeader('Content-Disposition', 'attachment; filename=' . $info['basename']);
                }
            }
            $connection->send($response);
        }
    }
    public function onerror(\Workerman\Connection\TcpConnection|null $connection = null, $code, $msg)
    {
        // opcode 真值: no-op（行 170-172）
return;
    }
}
