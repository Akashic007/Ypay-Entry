<?php

// 还原自 Swoole Compiler 加密文件: Install.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Install.php

namespace app\api\controller;

class Install{
    public function envcheck(): \think\response\Json
    {
        // [opcode 对照] checks 数组的键名字面量在受保护指令中被编译器销毁(t2=0 不可恢复)，
        // 键名按对应检查方法语义拟定，仅作展示用途，isEnvironmentPassed 只读 value['status']
        $checks = [];
        $checks['php_version']        = $this->checkPhpVersion();
        $checks['required_functions'] = $this->checkRequiredFunctions();
        $checks                       = array_merge($checks, $this->checkExtensions());
        $checks['file_permissions']   = $this->checkFilePermissions();
        $checks['install_status']     = $this->checkInstallStatus();
        $passed = $this->isEnvironmentPassed($checks);
        $status = $this->getInstallStatus();

        return json([
            'code' => 1,
            'msg'  => '环境检查完成',
            'data' => [
                'checks'      => $checks,
                'passed'      => $passed,
                'can_install' => $status['can_install'],
                'installed'   => $status['installed'],
            ],
        ]);
    }

    public function testdatabase(): \think\response\Json
    {
        $database = [
            'hostname' => input('hostname'),
            'username' => input('username'),
            'password' => input('password'),
            'hostport' => input('hostport'),
            'database' => '',
        ];
        $result = $this->connectDb($database);

        return json($result);
    }

    public function testredis(): \think\response\Json
    {
        $redisInfo = [
            'host'     => input('host', '127.0.0.1'),
            'port'     => input('port', 6379),
            'password' => input('password', ''),
        ];
        try {
            return json($this->connectRedis($redisInfo));
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            return json([
                'code' => 0,
                'msg'  => 'Redis连接失败：' . $e->getMessage(),
                'info' => $redisInfo,
            ]);
        }
    }

    public function install(\think\Request|null $request = null): \think\response\Json
    {
        $database = [
            'hostname' => $request->post('hostname'),
            'username' => $request->post('username'),
            'password' => $request->post('password'),
            'hostport' => $request->post('hostport'),
            'database' => $request->post('database'),
            'prefix'   => $request->post('prefix', 'as_'),
        ];

        $redisInfo = [
            'host'         => $request->post('redis_host', '127.0.0.1'),
            'port'         => $request->post('redis_port', 6379),
            'password'     => $request->post('redis_password', ''),
            'name'         => $request->post('redis_name', 'entry:'),
            'token_select' => $request->post('token_select', 1),
            'cache_select' => $request->post('cache_select', 2),
            'queue_select' => $request->post('queue_select', 3),
        ];

        $siteName = trim($request->post('site_name', ''));
        $username = trim($request->post('admin_user', 'admin'));
        $password = $request->post('admin_password', '');
        if (empty($username) || empty($password)) {
            return json(['code' => 0, 'msg' => '管理员账号或密码不能为空']);
        }

        $checks = [];
        $checks['php_version']        = $this->checkPhpVersion();
        $checks['required_functions'] = $this->checkRequiredFunctions();
        $checks                       = array_merge($checks, $this->checkExtensions());
        $checks['file_permissions']   = $this->checkFilePermissions();
        $checks['install_status']     = $this->checkInstallStatus();
        $passed = $this->isEnvironmentPassed($checks);
        if (!$passed) {
            return json(['code' => 0, 'msg' => '环境检查未通过', 'data' => ['checks' => $checks]]);
        }

        $dbCheck = $this->prepareDatabase($database);
        if ($dbCheck['code'] === 0) {
            return json($dbCheck);
        }

        $redisCheck = $this->connectRedis($redisInfo);
        if ($redisCheck['code'] === 0) {
            return json($redisCheck);
        }

        $status = $this->getInstallStatus();
        if (!$status['can_install']) {
            return json(['code' => 0, 'msg' => '安装被拒绝：存在安装锁定或文件冲突', 'data' => $status]);
        }

        if (!file_exists(public_path() . 'index.ex.html')) {
            return json(['code' => 0, 'msg' => '安装引导文件缺失，请确认 public/index.ex.html 存在']);
        }

        $dbReconnect = $this->reconnectDatabase($database);
        if ($dbReconnect['code'] === 0) {
            return json($dbReconnect);
        }

        $migrationResult = $this->runMigrations();
        if ($migrationResult['code'] === 0) {
            return json($migrationResult);
        }

        $configResult = $this->writeAllConfigs($database, $redisInfo);
        if ($configResult['code'] === 0) {
            return json($configResult);
        }

        $tokenKey = $this->refreshTokenKey();

        $envResult = $this->writeEnvExample($database, $redisInfo);
        if ($envResult['code'] === 0) {
            return json($envResult);
        }

        $this->updateSiteName($siteName);
        $this->updateAdminAccount($username, $password);

        $lockResult = $this->writeInstallLock();
        if ($lockResult['code'] === 0) {
            return json($lockResult);
        }

        $indexResult = $this->finalizeIndexFile();
        if ($indexResult['code'] === 0) {
            return json($indexResult);
        }

        return json([
            'code' => 1,
            'msg'  => '安装完成',
            'data' => [
                'token_key' => $tokenKey,
                'database'  => $database,
                'redis'     => $redisInfo,
            ],
        ]);
    }

    private function connectredis(array|null $redisInfo = null): array
    {
        if (!extension_loaded('redis')) {
            return ['code' => 0, 'msg' => 'Redis扩展未安装，请先安装php-redis扩展', 'info' => $redisInfo];
        }

        $redis   = null;
        $timeout = 3;
        $retry   = 3;

        try {
            $redis = new \Redis();
            $connected = @$redis->connect($redisInfo['host'], (int)$redisInfo['port'], $timeout, null, $retry * 1000);
            if (!$connected) {
                $error = error_get_last();
                $errMsg = $error ? $error['message'] : '未知错误';
                return [
                    'code'  => 0,
                    'msg' => 'Redis连接失败：无法连接到服务器 ' . $redisInfo['host'] . ':' . $redisInfo['port'] . '，错误：' . $errMsg,
                    'info' => $redisInfo,
                    'debug' => [
                        'host'    => $redisInfo['host'],
                        'port'    => $redisInfo['port'],
                        'timeout' => $timeout,
                        'error'   => $errMsg,
                    ],
                ];
            }

            if (!empty($redisInfo['password'])) {
                $authResult = @$redis->auth($redisInfo['password']);
                if (!$authResult) {
                    return ['code' => 0, 'msg' => 'Redis连接失败：密码认证失败', 'info' => $redisInfo];
                }
            }

            $pong = @$redis->ping();
            if ($pong !== true && $pong !== '+PONG' && $pong !== 'PONG') {
                return ['code' => 0, 'msg' => 'Redis连接失败：PING测试失败，返回：' . var_export($pong, true), 'info' => $redisInfo];
            }

            $testKey = 'install_test_' . time();
            $value   = 'test_' . microtime(true);
            if (!@$redis->setex($testKey, 5, $value)) {
                return ['code' => 0, 'msg' => 'Redis连接失败：写入测试失败', 'info' => $redisInfo];
            }

            $getResult = @$redis->get($testKey);
            if ($getResult !== $value) {
                return ['code' => 0, 'msg' => 'Redis连接失败：读取测试失败，数据不一致。期望：' . $value . '，实际：' . var_export($getResult, true), 'info' => $redisInfo];
            }

            @$redis->del($testKey);

            $info    = @$redis->info('server');
            $version = '未知';
            if (is_array($info) && isset($info['redis_version'])) {
                $version = $info['redis_version'];
            } elseif (is_string($info) && preg_match('/redis_version:([^\r\n]+)/', $info, $matches)) {
                $version = trim($matches[1]);
            }

            return [
                'code' => 1,
                'msg'  => 'Redis连接成功',
                'data' => [
                    'redis_ext'      => '连接成功',
                    'thinkphp_cache' => '连接成功',
                    'version'        => $version,
                    'config'         => $redisInfo,
                ],
            ];
        } catch (\Throwable $e) {
            $exceptionType = get_class($e);
            $code          = method_exists($e, 'getCode') ? $e->getCode() : 0;

            return [
                'code' => 0,
                'msg'  => 'Redis连接异常: ' . $e->getMessage() . ($code ? ' (错误代码: ' . $code . ')' : ''),
                'info' => [
                    'host'            => $redisInfo['host'],
                    'port'            => $redisInfo['port'],
                    'exception_type'  => $exceptionType,
                    'debug'           => $code,
                ],
            ];
        } finally {
            if ($redis !== null && is_object($redis) && method_exists($redis, 'close')) {
                try {
                    @$redis->close();
                } catch (\Exception $e) {
                    // 忽略关闭连接时的异常
                }
            }
        }
    }

    private function connectdb(array|null $database = null, bool|null $returnPdo = false): array
    {
        try {
            $config                         = \think\facade\Config::get('database');
            $config['connections']['mysql'] = array_merge($config['connections']['mysql'], $database);
            \think\facade\Config::set(['connections' => $config['connections']], 'database');

            $connection = \think\facade\Db::connect('mysql');
            $connection->query('SELECT 1');
        } catch (\think\db\exception\PDOException $e) {
            return [
                'code' => 0,
                'msg'  => __('数据库连接失败:%s', [mb_convert_encoding($e->getMessage() ?: 'unknown', 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')]),
            ];
        }

        $databases = [];
        $skipList  = ['information_schema', 'mysql'];
        $list      = $connection->query('SHOW DATABASES');
        foreach ($list as $item) {
            if (in_array($item['Database'], $skipList)) {
                continue;
            }
            $databases[] = $item['Database'];
        }

        $result = [
            'code'      => 1,
            'msg'       => '连接成功',
            'databases' => $databases,
            'pdo'       => $returnPdo ? $connection->getPdo() : '',
        ];

        return $result;
    }

    private function checkphpversion(): array
    {
        $required = '8.1.0';
        $current  = PHP_VERSION;
        $status   = version_compare($current, $required, '>=');

        return [
            'name'       => 'PHP版本',
            'required'   => '>= ' . $required,
            'current'    => $current,
            'status'     => $status,
            'message'    => $status ? 'PHP版本满足要求' : 'PHP版本过高，请升级到8.1或更高版本',
            'suggestion' => $status ? null : '请联系服务器管理员升级PHP版本',
        ];
    }

    private function checkrequiredfunctions(): array
    {
        $required = ['proc_open', 'proc_close'];
        $missing  = [];
        foreach ($required as $func) {
            if (!function_exists($func)) {
                $missing[] = $func;
            }
        }

        return [
            'name'       => '必要函数',
            'required'   => implode(', ', $required),
            'current'    => empty($missing) ? '全部可用' : '缺失: ' . implode(', ', $missing),
            'status'     => empty($missing),
            'message'    => empty($missing) ? '所有必要函数都可用' : '部分必要函数被禁用',
            'suggestion' => empty($missing) ? null : '请在php.ini中启用以下函数: ' . implode(', ', $missing),
        ];
    }

    private function checkextensions(): array
    {
        $checks = [];
        foreach (['pdo_mysql' => 'PDO MySQL扩展', 'redis' => 'Redis扩展', 'fileinfo' => 'Fileinfo扩展'] as $ext => $desc) {
            $loaded = extension_loaded($ext);
            $checks[] = [
                'name'       => $desc,
                'required'   => '已安装',
                'current'    => $loaded ? '已安装' : '未安装',
                'status'     => $loaded,
                'message'    => $loaded ? '已安装' : '未安装',
                'suggestion' => $loaded ? null : '请安装' . $ext . ': sudo apt-get install php-' . $ext . ' (Ubuntu)',
            ];
        }

        return $checks;
    }

    private function checkfilepermissions(): array
    {
        $basePath = root_path();
        $paths    = [
            'runtime'        => $basePath . 'runtime',
            'public/storage' => $basePath . 'public/storage',
        ];
        $unwritable = [];
        foreach ($paths as $name => $path) {
            if (!is_writable($path)) {
                $unwritable[] = $name;
            }
        }

        return [
            'name'       => '文件权限',
            'required'   => '可写',
            'current'    => empty($unwritable) ? '全部可写' : '不可写: ' . implode(', ', $unwritable),
            'status'     => empty($unwritable),
            'message'    => empty($unwritable) ? '所有必要目录都可写' : '部分目录不可写',
            'suggestion' => empty($unwritable) ? null : '请执行命令: chmod -R 755 ' . implode(' ', array_values($unwritable)),
        ];
    }

    private function checkinstallstatus(): array
    {
        $status     = $this->getInstallStatus();
        $message    = '系统可以开始安装';
        $suggestion = null;

        if ($status['installed']) {
            $message    = '系统已安装完成';
            $suggestion = '如需重新安装，请先删除 public/install.lock 文件';
        } elseif (!$status['can_install']) {
            if ($status['has_lock']) {
                $message    = '存在安装锁定文件';
                $suggestion = '请检查 public/install.lock 文件内容';
            } elseif ($status['has_index']) {
                $message    = '存在 index.html 文件冲突';
                $suggestion = '请删除 public/index.html 文件';
            } else {
                $message    = '缺少安装引导文件';
                $suggestion = '请确认 public/index.ex.html 文件存在';
            }
        }

        return [
            'name'       => '安装状态',
            'required'   => '可安装',
            'current'    => $status['installed'] ? '已安装' : ($status['can_install'] ? '可安装' : '安装冲突'),
            'status'     => $status['can_install'],
            'message'    => $message,
            'suggestion' => $suggestion,
        ];
    }

    private function getinstallstatus(): array
    {
        $lockFile    = public_path() . 'install.lock';
        $indexFile   = public_path() . 'index.html';
        $indexExFile = public_path() . 'index.ex.html';

        $hasLock    = file_exists($lockFile);
        $hasIndex   = file_exists($indexFile);
        $hasIndexEx = file_exists($indexExFile);

        $installed = false;
        if ($hasLock) {
            $content   = @file_get_contents($lockFile);
            $installed = $content !== false && trim($content) === 'install-end';
        }

        $canInstall = !$installed && !$hasLock && !$hasIndex;

        return [
            'has_lock'      => $hasLock,
            'has_index'     => $hasIndex,
            'has_index_ex'  => $hasIndexEx,
            'installed'     => $installed,
            'can_install'   => $canInstall,
        ];
    }

    private function isenvironmentpassed(array|null $checks = null): bool
    {
        foreach ($checks as $check) {
            if (!$check['status']) {
                return false;
            }
        }

        return true;
    }

    private function preparedatabase(array|null $database = null): array
    {
        if (empty($database['hostname']) || empty($database['username']) || empty($database['hostport'])) {
            return ['code' => 0, 'msg' => '数据库连接参数不完整'];
        }

        $dbConfig             = $database;
        $dbConfig['database'] = '';
        $checkResult          = $this->connectDb($dbConfig, true);
        if ($checkResult['code'] === 0) {
            return $checkResult;
        }

        if (!empty($database['database']) && !in_array($database['database'], $checkResult['databases'])) {
            $checkResult['pdo']->exec('CREATE DATABASE IF NOT EXISTS `' . $database['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            $config                         = \think\facade\Config::get('database');
            $config['connections']['mysql'] = array_merge($config['connections']['mysql'], $database);
            \think\facade\Config::set(['connections' => $config['connections']], 'database');
        }

        return ['code' => 1, 'msg' => '数据库检查通过'];
    }

    private function reconnectdatabase(array|null $database = null): array
    {
        try {
            $config                         = \think\facade\Config::get('database');
            $config['connections']['mysql'] = array_merge($config['connections']['mysql'], $database);
            \think\facade\Config::set(['connections' => $config['connections']], 'database');

            $connection = \think\facade\Db::connect('mysql');
            $connection->execute('USE `' . $database['database'] . '`');
            $connection->query('SELECT 1');

            return ['code' => 1, 'msg' => '数据库重新连接成功'];
        } catch (\think\db\exception\PDOException $e) {
            return [
                'code' => 0,
                'msg'  => '数据库重新连接失败：' . mb_convert_encoding($e->getMessage() ?: 'unknown', 'UTF-8', 'UTF-8,GBK,GB2312,BIG5'),
            ];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => '数据库重新连接异常：' . $e->getMessage()];
        }
    }

    private function runmigrations(): array
    {
        try {
            \think\facade\Console::call('migrate:run');

            return ['code' => 1, 'msg' => '数据库迁移完成'];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => '数据库迁移失败：' . $e->getMessage()];
        }
    }

    private function writeallconfigs(array|null $database = null, array|null $redisInfo = null): array
    {
        $dbResult = $this->updateDatabaseConfig($database);
        if ($dbResult['code'] === 0) {
            return $dbResult;
        }

        $redisResult = $this->updateRedisConfigs($redisInfo);
        if ($redisResult['code'] === 0) {
            return $redisResult;
        }

        return ['code' => 1, 'msg' => '配置写入完成'];
    }

    private function updatedatabaseconfig(array|null $database = null): array
    {
        $configFile = config_path() . 'database.php';
        $content    = @file_get_contents($configFile);
        if ($content === false) {
            return ['code' => 0, 'msg' => '无法读取数据库配置文件'];
        }

        $replacements = [
            'database.hostname' => $database['hostname'],
            'database.database' => $database['database'],
            'database.username' => $database['username'],
            'database.password' => $database['password'],
            'database.hostport' => $database['hostport'],
            'database.prefix'   => $database['prefix'] ?? 'as_',
        ];
        foreach ($replacements as $envKey => $value) {
            $content = $this->replaceEnvDefault($content, $envKey, (string)$value);
        }

        if (@file_put_contents($configFile, $content) === false) {
            return ['code' => 0, 'msg' => '写入数据库配置失败，请检查权限'];
        }

        return ['code' => 1, 'msg' => '数据库配置写入成功'];
    }

    private function updateredisconfigs(array|null $redisInfo = null): array
    {
        $files = [
            'buildadmin' => config_path() . 'buildadmin.php',
            'cache'      => config_path() . 'cache.php',
            'queue'      => config_path() . 'queue.php',
        ];
        foreach ($files as $name => $file) {
            $content = @file_get_contents($file);
            if ($content === false) {
                return ['code' => 0, 'msg' => '无法读取配置文件：' . $file];
            }

            $content = $this->replaceEnvDefault($content, 'redis.host', (string)$redisInfo['host']);
            $content = $this->replaceEnvDefault($content, 'redis.port', (string)$redisInfo['port']);
            $content = $this->replaceEnvDefault($content, 'redis.password', (string)$redisInfo['password']);
            $content = $this->replaceEnvDefault($content, 'redis.name', (string)$redisInfo['name']);

            if ($name == 'buildadmin') {
                $content = $this->replaceEnvDefault($content, 'redis.token_select', (string)$redisInfo['token_select']);
            } elseif ($name == 'cache') {
                $content = $this->replaceEnvDefault($content, 'redis.cache_select', (string)$redisInfo['cache_select']);
            } elseif ($name == 'queue') {
                $content = $this->replaceEnvDefault($content, 'redis.queue_select', (string)$redisInfo['queue_select']);
            }

            if (@file_put_contents($file, $content) === false) {
                return ['code' => 0, 'msg' => '写入配置失败，请检查权限：' . $file];
            }
        }

        return ['code' => 1, 'msg' => 'Redis 配置写入成功'];
    }

    private function replaceenvdefault(string|null $content = null, string|null $envKey = null, string|null $value = null): string
    {
        $pattern     = "/env\\('" . $envKey . "',\\s*'[^']*'\\)/";
        $replacement = "env('" . $envKey . "', '" . $value . "')";
        $content     = preg_replace($pattern, $replacement, $content);

        $pattern     = "/env\\('" . $envKey . "',\\s*\\d+\\)/";
        $replacement = "env('" . $envKey . "', " . $value . ")";
        $content     = preg_replace($pattern, $replacement, $content);

        return $content;
    }

    private function refreshtokenkey(): string
    {
        $buildFile = config_path() . 'buildadmin.php';
        $content   = @file_get_contents($buildFile);
        $newKey    = bin2hex(random_bytes(16));
        $content   = preg_replace("/'key'\\s*=>\\s*'[^']*'/", "'key'     => '" . $newKey . "'", $content, 1);
        @file_put_contents($buildFile, $content);

        return $newKey;
    }

    private function writeenvexample(array|null $database = null, array|null $redisInfo = null): array
    {
        $envFile = root_path() . '.env-example';
        $lines   = [
            'APP_DEBUG = true',
            '',
            '[APP]',
            'DEFAULT_TIMEZONE = Asia/Shanghai',
            '',
            '[LANG]',
            'default_lang = zh-cn',
            '',
            '[DATABASE]',
            'TYPE = mysql',
            'HOSTNAME = ' . $database['hostname'],
            'DATABASE = ' . $database['database'],
            'USERNAME = ' . $database['username'],
            'PASSWORD = ' . $database['password'],
            'HOSTPORT = ' . $database['hostport'],
            'PREFIX = ' . ($database['prefix'] ?? 'as_'),
            'CHARSET = utf8mb4',
            'DEBUG = true',
            '',
            '[CACHE]',
            'DRIVER = redis',
            '',
            '[REDIS]',
            'HOST = ' . $redisInfo['host'],
            'PORT = ' . $redisInfo['port'],
            'PASSWORD = ' . $redisInfo['password'],
            'SELECT = 1',
            'TIMEOUT = 0',
            'NAME = ' . $redisInfo['name'],
            'TOKEN_SELECT = ' . ($redisInfo['token_select'] ?? 1),
            'CACHE_SELECT = ' . ($redisInfo['cache_select'] ?? 2),
            'QUEUE_SELECT = ' . ($redisInfo['queue_select'] ?? 3),
            '',
            '[QUEUE]',
            'DEFAULT = redis',
            '',
        ];
        if (@file_put_contents($envFile, implode("\n", $lines)) === false) {
            return ['code' => 0, 'msg' => '写入 .env-example 失败，请检查文件权限'];
        }

        return ['code' => 1, 'msg' => '.env-example 写入成功'];
    }

    private function updatesitename(string|null $siteName = null): void
    {
        if (!$siteName) {
            return;
        }

        \think\facade\Db::connect('mysql')->name('config')->where('name', 'site_name')->update(['value' => $siteName]);
    }

    private function updateadminaccount(string|null $username = null, string|null $password = null): void
    {
        $admin = \think\facade\Db::connect('mysql')->name('admin')->order('id', 'asc')->find();
        if (!$admin) {
            return;
        }

        $data = [];
        if ($username) {
            $data['username'] = $username;
            $data['nickname'] = ucfirst($username);
        }
        if ($password) {
            $data['password'] = hash_password($password);
        }

        if (!empty($data)) {
            \think\facade\Db::connect('mysql')->name('admin')->where('id', $admin['id'])->update($data);
        }
    }

    private function writeinstalllock(): array
    {
        $lockFile = public_path() . 'install.lock';
        if (@file_put_contents($lockFile, 'install-end') === false) {
            return ['code' => 0, 'msg' => '写入安装锁文件失败，请检查 public 目录权限'];
        }

        return ['code' => 1, 'msg' => '安装锁创建成功'];
    }

    private function finalizeindexfile(): array
    {
        $indexFile   = public_path() . 'index.html';
        $indexExFile = public_path() . 'index.ex.html';

        if (!file_exists($indexExFile)) {
            return ['code' => 0, 'msg' => 'index.ex.html 不存在'];
        }

        if (file_exists($indexFile)) {
            @unlink($indexFile);
        }

        if (!@rename($indexExFile, $indexFile)) {
            return ['code' => 0, 'msg' => '重命名 index.ex.html 失败，请检查权限'];
        }

        return ['code' => 1, 'msg' => '入口文件调整完成'];
    }
}
