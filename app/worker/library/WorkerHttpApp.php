<?php

// 还原自 Swoole Compiler 加密文件: WorkerHttpApp.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/worker/library/WorkerHttpApp.php

namespace app\worker\library;

class WorkerHttpApp extends \think\App {
    public function init(\Workerman\Connection\TcpConnection|null $connection = null, \Workerman\Protocols\Http\Request|null $request = null, array|null $server = null): void
    {
        $this->woRequest = $request;
        $this->connection = $connection;
        $this->setRuntimePath(root_path() . 'runtime' . DIRECTORY_SEPARATOR);
        $script = public_path() . 'index.php';
        $_SERVER = array_merge($server, [
            'QUERY_STRING'    => $request->queryString(),
            'REQUEST_TIME'    => time(),
            'REQUEST_METHOD'  => $request->method(),
            'REQUEST_URI'     => $request->uri(),
            'SERVER_NAME'     => $request->host(true),
            'SERVER_PROTOCOL' => 'HTTP/' . $request->protocolVersion(),
            'SERVER_ADDR'     => $connection->getLocalIp(),
            'SERVER_PORT'     => $connection->getLocalPort(),
            'REMOTE_ADDR'     => $connection->getRemoteIp(),
            'REMOTE_PORT'     => $connection->getRemotePort(),
            'SCRIPT_FILENAME' => $script,
            'SCRIPT_NAME'     => DIRECTORY_SEPARATOR . pathinfo($script, PATHINFO_BASENAME),
            'DOCUMENT_ROOT'   => dirname($script),
            'PATH_INFO'       => $request->path(),
            'SERVER_SOFTWARE' => 'WorkerMan Development Server',
        ]);
        foreach ($request->header() as $name => $value) {
            $name = str_replace('-', '_', $name);
            if ($name == 'content_type') {
                $_SERVER['CONTENT_TYPE'] = $value;
            } elseif ($name == 'content_length') {
                $_SERVER['CONTENT_LENGTH'] = $value;
            } else {
                $name = strtoupper(str_starts_with($name, 'HTTP_') ? $name : 'HTTP_' . $name);
                $_SERVER[$name] = $value;
            }
        }
        $_GET = $request->get();
        $_POST = $request->post();
        $_FILES = $request->file();
        $_REQUEST = array_merge($_REQUEST, $_GET, $_POST);
        $this->initialize();
    }
}
