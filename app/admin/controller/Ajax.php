<?php

// 还原自 Swoole Compiler 加密文件: Ajax.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/controller/Ajax.php

namespace app\admin\controller;

class Ajax extends \app\common\controller\Backend {
    protected array $noNeedPermission = ['*'];
    protected array $noNeedLogin = ['terminal'];
    public function initialize(): void
    {
        parent::initialize();
    }

    public function upload(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('upload'));
        $file       = $this->request->file('file');
        $driver     = $this->request->param('driver', 'local');
        $topic      = $this->request->param('topic', 'default');
        $disks      = $this->request->param('disks', 'public');
        $purpose    = $this->request->param('purpose', 'default');
        $diskConfig = \think\facade\Config::get('filesystem.disks.' . $disks);
        \think\facade\Config::set($diskConfig, 'upload_disks');
        if ($purpose == 'entry') {
            $size    = $file->getSize();
            $maxSize = 2097152;
            if ($maxSize < $size) {
                $this->error('进件资料文件不能超过2M');
            }
        }
        try {
            $upload     = new \app\common\library\Upload();
            $attachment = $upload
                ->setFile($file)
                ->setDriver($driver)
                ->setTopic($topic)
                ->upload(null, $this->auth->id);
            $attachment->disk = $disks ?? 'public';
            $attachment->save();
            if ($purpose == 'entry') {
                event('EntryUpload', $attachment);
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment->toArray() ?? []
        ]);
    }

    public function area(): void
    {
        $this->success('', get_area());
    }

    public function buildsuffixsvg(): \think\Response
    {
        $suffix     = $this->request->param('suffix', 'file');
        $background = $this->request->param('background');
        $content    = build_suffix_svg((string)$suffix, (string)$background);
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/svg+xml');
    }

    public function getdatabaseconnectionlist(): void
    {
        $quickSearch     = $this->request->get("quickSearch/s", '');
        $connections     = config('database.connections');
        $desensitization = [];
        foreach ($connections as $key => $connection) {
            $connection        = \ba\TableManager::getConnectionConfig($key);
            $desensitization[] = [
                'type'     => $connection['type'],
                'database' => substr_replace($connection['database'], '****', 1, strlen($connection['database']) > 4 ? 2 : 1),
                'key'      => $key,
            ];
        }

        if ($quickSearch) {
            $desensitization = array_filter($desensitization, function ($item) use ($quickSearch) {
                return preg_match("/{$quickSearch}/i", $item['key']);
            });
            $desensitization = array_values($desensitization);
        }

        $this->success('', [
            'list' => $desensitization,
        ]);
    }

    public function gettablepk(null|string $table = null, null|string $connection = null): void
    {
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $table = \ba\TableManager::tableName($table, true, $connection);
        if (!\ba\TableManager::phinxAdapter(false, $connection)->hasTable($table)) {
            $this->error(__('Data table does not exist'));
        }

        $tablePk = \think\facade\Db::connect(\ba\TableManager::getConnection($connection))
            ->table($table)
            ->getPk();
        $this->success('', ['pk' => $tablePk]);
    }

    public function gettablelist(): void
    {
        $quickSearch  = $this->request->get("quickSearch/s", '');
        $connection   = $this->request->request('connection');// 数据库连接配置标识
        $samePrefix   = $this->request->request('samePrefix/b', true);// 是否仅返回项目数据表（前缀同项目一致的）
        $excludeTable = $this->request->request('excludeTable/a', []);// 要排除的数据表数组（表名无需带前缀）

        $outTables = [];
        $dbConfig  = \ba\TableManager::getConnectionConfig($connection);
        $tables    = \ba\TableManager::getTableList($connection);

        if ($quickSearch) {
            $tables = array_filter($tables, function ($comment) use ($quickSearch) {
                return preg_match("/{$quickSearch}/i", $comment);
            });
        }

        $pattern = '/^' . $dbConfig['prefix'] . '/i';
        foreach ($tables as $table => $comment) {
            if ($samePrefix && !preg_match($pattern, $table)) continue;

            $table = preg_replace($pattern, '', $table);
            if (!in_array($table, $excludeTable)) {
                $outTables[] = [
                    'table'      => $table,
                    'comment'    => $comment,
                    'connection' => $connection,
                    'prefix'     => $dbConfig['prefix'],
                ];
            }
        }

        $this->success('', [
            'list' => $outTables,
        ]);
    }

    public function gettablefieldlist(): void
    {
        $table      = $this->request->param('table');
        $clean      = $this->request->param('clean', true);
        $connection = $this->request->request('connection');
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $connection = \ba\TableManager::getConnection($connection);
        $tablePk    = \think\facade\Db::connect($connection)->name($table)->getPk();
        $this->success('', [
            'pk'        => $tablePk,
            'fieldList' => \ba\TableManager::getTableColumns($table, $clean, $connection),
        ]);
    }

    public function changeterminalconfig(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('Change terminal config'));
        if (\ba\Terminal::changeTerminalConfig()) {
            $this->success();
        } else {
            $this->error(__('Failed to modify the terminal configuration. Please modify the configuration file manually:%s', ['/config/buildadmin.php']));
        }
    }

    public function clearcache(): void
    {
        \app\admin\model\AdminLog::instance()->setTitle(__('Clear cache'));
        $type = $this->request->post('type');
        $authKey = \think\facade\Cache::get('auth_key');
        if ($type == 'tp' || $type == 'all') {
            \think\facade\Cache::clear();
            \think\facade\Cache::set('auth_key', $authKey);
        } else {
            $this->error(__('Parameter error'));
        }
        \think\facade\Event::trigger('cacheClearAfter', $this->app);
        $this->success(__('Cache cleaned~'));
    }

    public function terminal(): void
    {
        (new \ba\Terminal())->exec();
    }

    public function preview(): \think\response\File
    {
        $url = $this->request->param('url');
        if (empty($url)) {
            $this->error('资源URL为空');
        }
        $token = $this->request->param('token');
        if (empty($token)) {
            $this->error('TOKEN为空');
        }
        try {
            $user = \app\common\facade\Token::get($token);
            if (empty($user)) {
                $this->error('TOKEN信息为空');
            }
            if ($user['type'] != 'admin') {
                $this->error('请使用后台TOKEN请求');
            }
            \app\common\facade\Token::tokenExpirationCheck($user);
        } catch (\Throwable $e) {
            $this->error('登录已过期');
        }
        $attachment = \app\common\model\Attachment::where('url', $url)->find();
        if (empty($attachment)) {
            $this->error('文件不存在');
        }
        $path = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $url;
        if (!is_file($path)) {
            $this->error('文件不存在');
        }
        return download($path)->force(false);
    }

}
