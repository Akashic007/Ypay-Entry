<?php

// 还原自 Swoole Compiler 加密文件: WorkerWsApp.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/worker/library/WorkerWsApp.php

namespace app\worker\library;

class WorkerWsApp extends \think\App {
    public string $clientId;
    public array $requestData = [];
    public mixed $message;
    public function init(array|null $server = null): void
    {
        array_walk_recursive($this->message, [\app\worker\library\Helper::class, 'cleanXss']);
        array_walk_recursive($this->requestData, [\app\worker\library\Helper::class, 'cleanXss']);
        $_GET = $this->requestData['get'] ?? [];
        $_REQUEST = array_merge($_REQUEST, $_GET);
        $script = public_path() . 'index.php';
        $_SERVER = array_merge([
            'PATH_INFO'       => $this->message['pathInfo'] ?? 'worker/WebSocket/index',
            'SCRIPT_FILENAME' => $script,
            'SCRIPT_NAME'     => DIRECTORY_SEPARATOR . pathinfo($script, PATHINFO_BASENAME),
            'DOCUMENT_ROOT'   => dirname($script),
            'HTTP_ACCEPT'     => 'application/json, text/plain, */*',
        ], $server, $this->requestData['server'] ?? []);
        $this->message['MESSAGE_TIME'] = time();
        $this->initialize();
    }
    public function send(string|null $type = null, mixed $data = null, mixed $uid = null): bool
    {
        $message = $this->assembleMessage($type, $data);
        if (!is_null($uid)) {
            if (\GatewayWorker\Lib\Gateway::isUidOnline($uid)) {
                \GatewayWorker\Lib\Gateway::sendToUid($uid, $message);
            }
            return true;
        } else {
            if (\GatewayWorker\Lib\Gateway::isOnline($this->clientId)) {
                return \GatewayWorker\Lib\Gateway::sendToClient($this->clientId, $message);
            }
            return true;
        }
    }
    public function assembleMessage(string|null $type = null, mixed $data = null): string
    {
        return json_encode([
            'type' => $type,
            'data' => $data,
            'path' => $this->message['pathInfo'] ?? '',
            'time' => $this->message['MESSAGE_TIME'] ?? '',
        ]);
    }
}
