<?php

// 还原自 Swoole Compiler 加密文件: WsBusiness.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/worker/events/WsBusiness.php

namespace app\worker\events;

class WsBusiness{
    protected static array $monitorConfig = [];
    protected static array $serverData;
    protected static ?\think\Db $db = null;
    protected static \GatewayWorker\BusinessWorker $worker;
    public static function onworkerstart(\GatewayWorker\BusinessWorker|null $worker = null): void
    {
        self::$worker = $worker;
        if (!self::$monitorConfig) {
            self::$monitorConfig = \think\facade\Config::get('worker_monitor');
        }
        if (!self::$db) {
            try {
                \think\facade\Db::execute('SELECT 1');
                $app = \think\facade\App::getInstance();
                self::$db = $app->db;
            } catch (\think\db\exception\PDOException) {
            }
        }
        if ($worker->id == 0) {
            new \app\worker\library\Monitor(self::$monitorConfig);
        }
        self::callModuleEvent('onWorkerStart', ['worker' => $worker]);
    }
    public static function onworkerstop(\GatewayWorker\BusinessWorker|null $worker = null): void
    {
        self::callModuleEvent('onWorkerStop', ['worker' => $worker]);
    }
    public static function onwebsocketconnect(string|null $clientId = null, array|null $data = null): void
    {
        self::$serverData = $_SERVER;
        $_SESSION['requestData'] = $data;
        self::callModuleEvent('onWebSocketConnect', ['clientId' => $clientId, 'data' => $data]);
    }
    public static function onmessage(string|null $clientId = null, mixed $message = null): bool
    {
        if ($message == 'ping') {
            return true;
        }
        $app = new \app\worker\library\WorkerWsApp(root_path());
        $app->db = self::$db;
        $app->worker = self::$worker;
        $app->clientId = $clientId;
        $app->requestData = $_SESSION['requestData'] ?? [];
        $app->message = json_decode($message, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            return $app->send('error', ['message' => 'Message parsing error:' . json_last_error_msg(), 'code' => 500]);
        }
        $app->init(self::$serverData ?? []);
        $http     = $app->http;
        $response = $http->run();
        $code     = $response->getCode();
        if (300 <= $code) {
            $content = $response->getContent();
            $data    = json_decode($content, true);
            $app->send('error', [
                'code'    => $code,
                'content' => json_last_error() != JSON_ERROR_NONE ? $data : $content,
                'data'    => $response->getData(),
                'header'  => $response->getHeader(),
            ]);
        }
        $http->end($response);
        return true;
    }
    public static function onclose(string|null $clientId = null): void
    {
        self::callModuleEvent('onWebSocketClose', ['clientId' => $clientId]);
    }
    public static function callmoduleevent($fun, $params): void
    {
        $modules = \app\admin\library\module\Server::installedList(root_path() . 'modules' . DIRECTORY_SEPARATOR);
        foreach ($modules as $module) {
            if ($module['state'] != 1) {
                continue;
            }
            $class = \app\admin\library\module\Server::getClass($module['uid']);
            if (!class_exists($class) || !method_exists($class, $fun)) {
                continue;
            }
            $obj = new $class();
            $obj->$fun($params);
        }
    }
}
