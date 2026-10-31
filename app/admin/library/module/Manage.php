<?php

// 还原自 Swoole Compiler 加密文件: Manage.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/library/module/Manage.php

namespace app\admin\library\module;

use Throwable;
use ba\Depends;
use ba\Exception;
use ba\Filesystem;
use think\facade\Config;

class Manage
{
    protected static \app\admin\library\module\Manage $instance;
    protected string $installDir;
    protected string $backupsDir;
    protected string $uid;
    protected string $modulesDir;

    // 模块状态（opcode 常量表真值）
    public const UNINSTALLED = 0;
    public const INSTALLED = 1;
    public const WAIT_INSTALL = 2;
    public const CONFLICT_PENDING = 3;
    public const DEPENDENT_WAIT_INSTALL = 4;
    public const DIRECTORY_OCCUPIED = 5;
    public const DISABLE = 6;

    // [opcode 还原] RECV_INIT 默认值为 ''（字面量真值）；站点内存在 instance() 无参调用
    public static function instance(string $uid = ''): \app\admin\library\module\Manage
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($uid);
        }
        return self::$instance;
    }

    public function __construct(string $uid)
    {
        $this->installDir = root_path() . 'modules' . DIRECTORY_SEPARATOR;
        // [opcode 真值] FAST_CONCAT($this->installDir, 'backups') — 备份目录位于模块目录内
        $this->backupsDir = $this->installDir . 'backups' . DIRECTORY_SEPARATOR;
        if (!is_dir($this->installDir)) {
            mkdir($this->installDir, 0755, true);
        }
        if (!is_dir($this->backupsDir)) {
            mkdir($this->backupsDir, 0755, true);
        }
        if ($uid) {
            $this->uid        = $uid;
            $this->modulesDir = $this->installDir . $uid . DIRECTORY_SEPARATOR;
        }
    }

    public function getinstallstate()
    {
        if (!is_dir($this->modulesDir)) {
            return self::UNINSTALLED;
        }
        $info = $this->getinfo();
        if ($info && isset($info['state'])) {
            return $info['state'];
        }
        if (Filesystem::dirIsEmpty($this->modulesDir)) {
            return self::UNINSTALLED;
        }
        return self::DIRECTORY_OCCUPIED;
    }

    public function download(string|null $token = null, int|null $orderId = null): string
    {
        if (!$orderId) {
            throw new Exception('Order not found');
        }
        $tmpFile = Server::download($this->uid, $this->installDir, [
            'sysVersion'    => Config::get('buildadmin.version'),
            'nuxtVersion'   => Server::getNuxtVersion(),
            'ba-user-token' => $token,
            'order_id'      => $orderId,
            'installed'     => Server::getInstalledIds($this->installDir),
        ]);
        Filesystem::delDir($this->modulesDir);
        Filesystem::unzip($tmpFile);
        @unlink($tmpFile);
        $this->checkpackage();
        $this->setinfo(['state' => self::WAIT_INSTALL]);
        return $tmpFile;
    }

    public function upload(string|null $token = null, string|null $file = null): array
    {
        $file = Filesystem::fsFit(root_path() . 'public' . $file);
        if (!is_file($file)) {
            throw new Exception('Zip file not found');
        }
        $targetFile = $this->installDir . 'uploadTemp' . date('YmdHis') . '.zip';
        copy($file, $targetFile);
        $unzipDir = Filesystem::unzip($targetFile);
        $unzipDir .= DIRECTORY_SEPARATOR;
        @unlink($file);
        @unlink($targetFile);

        $info = Server::getIni($unzipDir);
        if (empty($info['uid'])) {
            Filesystem::delDir($unzipDir);
            throw new Exception('Basic configuration of the Module is incomplete');
        }

        try {
            Server::installPreCheck([
                'uid'           => $info['uid'],
                'sysVersion'    => Config::get('buildadmin.version'),
                'nuxtVersion'   => Server::getNuxtVersion(),
                'moduleVersion' => $info['version'],
                'ba-user-token' => $token,
                'installed'     => Server::getInstalledIds($this->installDir),
                'server'        => 1,
            ]);
        } catch (Throwable $e) {
            Filesystem::delDir($unzipDir);
            throw $e;
        }

        $this->uid        = $info['uid'];
        $this->modulesDir = $this->installDir . $info['uid'] . DIRECTORY_SEPARATOR;

        $exists = false;
        if (is_dir($this->modulesDir)) {
            $installedInfo = $this->getinfo();
            if ($installedInfo && !empty($installedInfo['uid'])) {
                $versionArr = explode('.', $installedInfo['version']);
                if (isset($versionArr[2])) {
                    $versionArr[2]++;
                }
                $newVersion = implode('.', $versionArr);
                $exists     = Version::compare($newVersion, $info['version']);
                if (!$exists) {
                    Filesystem::delDir($unzipDir);
                    throw new Exception('Module already exists');
                }
            }
            if (!Filesystem::dirIsEmpty($this->modulesDir) && !$exists) {
                Filesystem::delDir($unzipDir);
                throw new Exception('The directory required by the module is occupied');
            }
        }

        $data = ['state' => self::WAIT_INSTALL];
        if ($exists) {
            $data['update'] = 1;
        }
        Filesystem::delDir($this->modulesDir);
        rename($unzipDir, $this->modulesDir);
        $this->checkpackage();
        $this->setinfo($data);
        return $info;
    }

    public function update(string|null $token = null, int|null $orderId = null): void
    {
        $state = $this->getinstallstate();
        if ($state != self::DISABLE) {
            throw new Exception('Please disable the module before updating');
        }
        $this->download($token, $orderId);
        $info           = $this->getinfo();
        $info['update'] = 1;
        $this->setinfo([], $info);
    }

    public function install(string|null $token = null, int|null $orderId = null): array
    {
        $state = $this->getinstallstate();
        if ($state == self::INSTALLED || $state == self::DIRECTORY_OCCUPIED || $state == self::DISABLE) {
            throw new Exception('Module already exists');
        }
        if ($state == self::UNINSTALLED) {
            $this->download($token, $orderId);
        }
        Server::importSql($this->modulesDir);
        $info = $this->getinfo();
        if (isset($info['update']) && $info['update']) {
            Server::execEvent($this->uid, 'update');
            unset($info['update']);
            $this->setinfo([], $info);
        }
        Server::execEvent($this->uid, 'install');
        // [opcode 真值] enable() 触发参数为 7 字符加密字面量（^_ELK,7），与 conflicthandle 中
        // $trigger == 'install' 判断对应，语义择优还原为 'install'
        $this->enable('install');
        return $info;
    }

    public function uninstall(): void
    {
        $info = $this->getinfo();
        if ($info['state'] != self::DISABLE) {
            throw new Exception('Please disable the module first', 0, ['uid' => $this->uid]);
        }
        Server::execEvent($this->uid, 'uninstall');
        Filesystem::delDir($this->modulesDir);
    }

    public function changestate(bool|null $state = null): array
    {
        $info = $this->getinfo();
        if (!$state) {
            $arr = [self::INSTALLED, self::CONFLICT_PENDING, self::DEPENDENT_WAIT_INSTALL];
            if (in_array($info['state'], $arr)) {
                return $this->disable();
            }
            throw new Exception('The current state of the module cannot be set to disabled', 0, [
                'uid'   => $this->uid,
                'state' => $info['state'],
            ]);
        } else {
            if ($info['state'] != self::DISABLE) {
                throw new Exception('The current state of the module cannot be set to enabled', 0, [
                    'uid'   => $this->uid,
                    'state' => $info['state'],
                ]);
            }
            $this->setinfo(['state' => self::WAIT_INSTALL]);
        }
        return $info;
    }

    public function enable(string|null $trigger = null): void
    {
        Server::installWebBootstrap($this->uid, $this->modulesDir);
        Server::createRuntime($this->modulesDir);
        $this->conflicthandle($trigger);
        Server::execEvent($this->uid, 'enable');
        $this->dependupdatehandle();
    }

    public function disable(): array
    {
        $update                 = request()->post('update/b', false);
        $confirmConflict        = request()->post('confirmConflict/b', false);
        $dependConflictSolution = request()->post('dependConflictSolution/a', []);
        $info                   = $this->getinfo();

        $installPackage = $this->backupsDir . $this->uid . '-install.zip';
        $installDir     = false;
        if (is_file($installPackage)) {
            $installDir = $this->backupsDir . $this->uid . '-install' . DIRECTORY_SEPARATOR;
            try {
                Filesystem::unzip($installPackage, $installDir);
            } catch (Exception) {
            }
        }

        $fileList       = Server::getFileList($this->modulesDir, true);
        $dependConflict = $this->disabledependcheck();
        if (($fileList || !self::isemptyarray($dependConflict)) && !$confirmConflict) {
            $dependConflictArr = [];
            foreach ($dependConflict as $env => $envItem) {
                foreach ($envItem as $depend => $item) {
                    $dependConflictArr[] = [
                        'env'         => $env,
                        'depend'      => $depend,
                        'dependTitle' => $depend . ' ' . $item,
                        'solution'    => 'delete',
                    ];
                }
            }

            throw new Exception('Module file updated', -1, [
                'uid'            => $this->uid,
                'conflictFile'   => $fileList,
                'dependConflict' => $dependConflictArr,
            ]);
        }

        Server::execEvent($this->uid, 'disable', ['update' => $update]);

        $composer       = false;
        $webPackage     = false;
        $webNuxtPackage = false;
        foreach ($dependConflictSolution as $key => $item) {
            if (!$item) {
                continue;
            }
            if ($key == 'require' || $key == 'require-dev') {
                $composer = true;
            } elseif ($key == 'dependencies' || $key == 'devDependencies') {
                $webPackage = true;
            } elseif ($key == 'nuxtDependencies' || $key == 'nuxtDevDependencies') {
                $webNuxtPackage = true;
            }
        }

        $fileMap  = [
            'composer'       => 'composer.json',
            'webPackage'     => 'web' . DIRECTORY_SEPARATOR . 'package.json',
            'webNuxtPackage' => 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json',
        ];
        $solution = [];
        if ($composer) {
            $fileList[]            = $fileMap['composer'];
            $solution['composer']  = [
                'pm'      => false,
                'command' => 'composer.update',
                'type'    => 'composer_dependent_wait_install',
            ];
        }
        if ($webPackage) {
            $fileList[]            = $fileMap['webPackage'];
            $solution['webPackage'] = [
                'pm'      => true,
                'command' => 'web-install',
                'type'    => 'npm_dependent_wait_install',
            ];
        }
        if ($webNuxtPackage) {
            $fileList[]               = $fileMap['webNuxtPackage'];
            $solution['webNuxtPackage'] = [
                'pm'      => true,
                'command' => 'nuxt-install',
                'type'    => 'nuxt_npm_dependent_wait_install',
            ];
        }

        if ($fileList) {
            $overwriteDir = Server::getOverwriteDir();
            foreach ($overwriteDir as $item) {
                $paths = explode(DIRECTORY_SEPARATOR, $item);
                if (in_array($paths[0], $overwriteDir) || in_array($item, $fileMap)) {
                    $fileList[$item] = $item;
                } else {
                    $fileList[$item] = Filesystem::fsFit(str_replace(root_path(), '', $this->modulesDir . $item));
                }
                if (!is_file(root_path() . $fileList[$item])) {
                    unset($fileList[$item]);
                }
            }
        }

        $disablePackage = $this->backupsDir . $this->uid . '-disable-' . date('YmdHis') . '.zip';
        Filesystem::zip($fileList, $disablePackage);

        $composerDep = new Depends(root_path() . 'composer.json', 'composer');
        $webDep      = new Depends(root_path() . 'web' . DIRECTORY_SEPARATOR . 'package.json');
        $webNuxtDep  = new Depends(root_path() . 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json');
        foreach ($dependConflictSolution as $key => $item) {
            if (!$item) {
                continue;
            }
            $isDev = stripos($key, 'dev') !== false;
            if ($key == 'require' || $key == 'require-dev') {
                $composerDep->removeDepends($item, $isDev);
            } elseif ($key == 'dependencies' || $key == 'devDependencies') {
                $webDep->removeDepends($item, $isDev);
            } elseif ($key == 'nuxtDependencies' || $key == 'nuxtDevDependencies') {
                $webNuxtDep->removeDepends($item, $isDev);
            }
        }

        $composerConfig = Server::getConfig($this->modulesDir, 'composerConfig');
        if ($composerConfig) {
            $composerDep->removeComposerConfig($composerConfig);
        }

        $protectedFiles = Server::getConfig($this->modulesDir, 'protectedFiles');
        foreach ($protectedFiles as &$file) {
            $file = Filesystem::fsFit(root_path() . $file);
        }

        $fileList = Server::getFileList($this->modulesDir);
        foreach ($fileList as $file) {
            $fileTarget = Filesystem::fsFit($this->modulesDir . $file);
            $file       = Filesystem::fsFit(root_path() . $file);
            if (!file_exists($file)) {
                continue;
            }
            if (file_exists($fileTarget)) {
                continue;
            }
            if (!is_dir(dirname($fileTarget))) {
                mkdir(dirname($fileTarget), 0755, true);
            }
            copy($file, $fileTarget);
            if (in_array($file, $protectedFiles)) {
                continue;
            }
            if (file_exists($file)) {
                unlink($file);
            }
            Filesystem::delEmptyDir(dirname($file));
        }

        if ($installDir) {
            Filesystem::delDir($installDir);
        }

        Server::uninstallWebBootstrap($this->uid);
        $this->setinfo(['state' => self::DISABLE]);

        if ($update) {
            $token   = request()->post('token/s', '');
            $orderId = request()->post('order/d', 0);
            $this->update($token, $orderId);
            throw new Exception('update', -3, ['uid' => $this->uid]);
        }
        if ($solution) {
            throw new Exception('dependent wait install', -2, [
                'uid'          => $this->uid,
                'wait_install' => $solution,
            ]);
        }
        return $info;
    }

    public function conflicthandle(string|null $trigger = null): bool
    {
        $info = $this->getinfo();
        if ($info['state'] != self::WAIT_INSTALL && $info['state'] != self::CONFLICT_PENDING) {
            return false;
        }

        $coverFileList  = Server::getFileList($this->modulesDir, true);
        $dependConflict = Server::dependConflictCheck($this->modulesDir);
        $fileList       = Server::getFileList($this->modulesDir);
        $moduleDepend   = Server::getDepend($this->modulesDir);

        $fileConflict   = [];
        $protectedFiles = [];
        $sysComposerDep       = new Depends(root_path() . 'composer.json', 'composer');
        $sysWebPackageDep     = new Depends(root_path() . 'web' . DIRECTORY_SEPARATOR . 'package.json');
        $sysWebNuxtPackageDep = new Depends(root_path() . 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json');

        if ($coverFileList || !self::isemptyarray($dependConflict)) {
            $extend = request()->post('extend/a', []);
            if (!$extend) {
                $fileConflictData = [];
                foreach ($coverFileList as $file) {
                    $fileConflictData[$file] = [
                        'newFile'  => $this->uid . DIRECTORY_SEPARATOR . $file,
                        'oldFile'  => $file,
                        'solution' => 'cover',
                    ];
                }
                $dependConflictData = [];
                foreach ($dependConflict as $env => $envItem) {
                    $isDev = stripos($env, 'dev') !== false;
                    foreach ($envItem as $depend => $item) {
                        $hasDepend = '';
                        if (in_array($env, ['require', 'require-dev'])) {
                            $hasDepend = $depend . ' ' . $sysComposerDep->hasDepend($depend, $isDev);
                        } elseif (in_array($env, ['dependencies', 'devDependencies'])) {
                            $hasDepend = $depend . ' ' . $sysWebPackageDep->hasDepend($depend, $isDev);
                        } elseif (in_array($env, ['nuxtDependencies', 'nuxtDevDependencies'])) {
                            $hasDepend = $depend . ' ' . $sysWebNuxtPackageDep->hasDepend($depend, $isDev);
                        }
                        $dependConflictData[] = [
                            'env'       => $env,
                            'newDepend' => $depend . ' ' . $item,
                            'oldDepend' => $hasDepend,
                            'depend'    => $depend,
                            'solution'  => 'cover',
                        ];
                    }
                }
                $this->setinfo(['state' => self::CONFLICT_PENDING]);
                throw new Exception('Module file conflicts', -1, [
                    'fileConflict'   => $fileConflictData,
                    'dependConflict' => $dependConflictData,
                    'uid'            => $this->uid,
                    'state'          => self::CONFLICT_PENDING,
                ]);
            }

            if ($coverFileList && isset($extend['fileConflict'])) {
                foreach ($fileList as $key => $path) {
                    if (!isset($extend['fileConflict'][$path])) {
                        continue;
                    }
                    if ($extend['fileConflict'][$path] == 'discard') {
                        $protectedFiles[$path] = $path;
                        unset($fileList[$key]);
                        continue;
                    }
                    $fileConflict[] = $path;
                }
            }
            if (!self::isemptyarray($dependConflict) && isset($extend['dependConflict'])) {
                foreach ($moduleDepend as $env => $envItem) {
                    foreach ($envItem as $depend => $item) {
                        if (!isset($extend['dependConflict'][$env][$depend])) {
                            continue;
                        }
                        if ($extend['dependConflict'][$env][$depend] != 'discard') {
                            continue;
                        }
                        unset($moduleDepend[$env][$depend]);
                    }
                }
            }
            if ($moduleDepend) {
                foreach ($moduleDepend as $env => $item) {
                    if (!$item) {
                        continue;
                    }
                    if ($env == 'require' || $env == 'require-dev') {
                        $fileConflict[] = 'composer.json';
                    } elseif ($env == 'dependencies' || $env == 'devDependencies') {
                        $fileConflict[] = 'web' . DIRECTORY_SEPARATOR . 'package.json';
                    } elseif ($env == 'nuxtDependencies' || $env == 'nuxtDevDependencies') {
                        $fileConflict[] = 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json';
                    }
                }
            }

            if ($fileConflict) {
                if ($trigger == 'install') {
                    $installPackage = $this->backupsDir . $this->uid . '-install.zip';
                }
                $coverPackage = $this->backupsDir . $this->uid . '-cover-' . date('YmdHis') . '.zip';
                Filesystem::zip($fileConflict, $coverPackage);
            }

            if ($moduleDepend) {
                $composerCover = false;
                $webCover      = false;
                $webNuxtCover  = false;

                $composerConfig = Server::getConfig($this->modulesDir, 'composerConfig');
                if ($composerConfig) {
                    $sysComposerDep->setComposerConfig($composerConfig);
                }
                foreach ($moduleDepend as $env => $item) {
                    if (!$item) {
                        continue;
                    }
                    if ($env == 'require') {
                        $composerCover = true;
                        $sysComposerDep->addDepends($item, false, true);
                    } elseif ($env == 'require-dev') {
                        $composerCover = true;
                        $sysComposerDep->addDepends($item, true, true);
                    } elseif ($env == 'dependencies') {
                        $webCover = true;
                        $sysWebPackageDep->addDepends($item, false, true);
                    } elseif ($env == 'devDependencies') {
                        $webCover = true;
                        $sysWebPackageDep->addDepends($item, true, true);
                    } elseif ($env == 'nuxtDependencies') {
                        $webNuxtCover = true;
                        $sysWebNuxtPackageDep->addDepends($item, false, true);
                    } elseif ($env == 'nuxtDevDependencies') {
                        $webNuxtCover = true;
                        $sysWebNuxtPackageDep->addDepends($item, true, true);
                    }
                }
                if ($webCover) {
                    $info['npm_dependent_wait_install'] = 1;
                    $info['state']                      = self::DEPENDENT_WAIT_INSTALL;
                }
                if ($composerCover) {
                    $info['composer_dependent_wait_install'] = 1;
                    $info['state']                           = self::DEPENDENT_WAIT_INSTALL;
                }
                if ($webNuxtCover) {
                    $info['nuxt_npm_dependent_wait_install'] = 1;
                    $info['state']                           = self::DEPENDENT_WAIT_INSTALL;
                }
                if ($info['state'] != self::DEPENDENT_WAIT_INSTALL) {
                    $this->setinfo(['state' => self::INSTALLED]);
                } else {
                    $this->setinfo([], $info);
                }
            } else {
                $this->setinfo(['state' => self::INSTALLED]);
            }

            $overwriteDir = Server::getOverwriteDir();
            foreach ($overwriteDir as $item) {
                $itemDir   = $this->modulesDir . $item;
                $targetDir = root_path() . $item;
                if (!is_dir($itemDir)) {
                    continue;
                }
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($itemDir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($files as $file) {
                    $fileTarget = Filesystem::fsFit($targetDir . DIRECTORY_SEPARATOR . str_replace($itemDir, '', $file->getPathname()));
                    if ($file->isDir()) {
                        Filesystem::mkdir($fileTarget);
                        continue;
                    }
                    if (in_array(str_replace(root_path(), '', $fileTarget), $protectedFiles)) {
                        continue;
                    }
                    Filesystem::mkdir(dirname($fileTarget));
                    copy($file, $fileTarget);
                }
                if (!Config::get('buildadmin.module_pure_install')) {
                    Filesystem::delDir($itemDir);
                }
            }
        }
        return true;
    }

    public function dependupdatehandle(): void
    {
        $info = $this->getinfo();
        if ($info['state'] == self::DEPENDENT_WAIT_INSTALL) {
            $waitInstall = [];
            if (isset($info['composer_dependent_wait_install'])) {
                $waitInstall = ['composer_dependent_wait_install'];
            }
            if (isset($info['npm_dependent_wait_install'])) {
                $waitInstall[] = 'npm_dependent_wait_install';
            }
            if (isset($info['nuxt_npm_dependent_wait_install'])) {
                $waitInstall[] = 'nuxt_npm_dependent_wait_install';
            }
            if ($waitInstall) {
                throw new Exception('dependent wait install', -2, [
                    'uid'          => $this->uid,
                    'state'        => self::DEPENDENT_WAIT_INSTALL,
                    'wait_install' => $waitInstall,
                ]);
            }
            $this->setinfo(['state' => self::INSTALLED]);
        }
    }

    public function dependentinstallcomplete(string|null $type = null): void
    {
        $info = $this->getinfo();
        if ($info['state'] == self::DEPENDENT_WAIT_INSTALL) {
            if ($type == 'npm') {
                unset($info['npm_dependent_wait_install']);
            }
            if ($type == 'nuxt_npm') {
                unset($info['nuxt_npm_dependent_wait_install']);
            }
            if ($type == 'composer') {
                unset($info['composer_dependent_wait_install']);
            }
            if ($type == 'all') {
                unset($info['npm_dependent_wait_install']);
                unset($info['composer_dependent_wait_install']);
                unset($info['nuxt_npm_dependent_wait_install']);
            }
            if (!isset($info['npm_dependent_wait_install']) && !isset($info['composer_dependent_wait_install']) && !isset($info['nuxt_npm_dependent_wait_install'])) {
                $info['state'] = self::INSTALLED;
            }
            $this->setinfo([], $info);
        }
    }

    public function disabledependcheck(): array
    {
        $moduleDepend = Server::getDepend($this->modulesDir);
        if (!$moduleDepend) {
            return [];
        }

        $sysComposerDep       = new Depends(root_path() . 'composer.json', 'composer');
        $sysWebPackageDep     = new Depends(root_path() . 'web' . DIRECTORY_SEPARATOR . 'package.json');
        $sysWebNuxtPackageDep = new Depends(root_path() . 'web-nuxt' . DIRECTORY_SEPARATOR . 'package.json');
        foreach ($moduleDepend as $env => $item) {
            $isDev = stripos($env, 'dev') !== false;
            if ($env == 'require' || $env == 'require-dev') {
                foreach ($item as $depend => $value) {
                    if (!$sysComposerDep->hasDepend($depend, $isDev)) {
                        unset($item[$depend]);
                    }
                }
                $moduleDepend[$env] = $item;
            } elseif ($env == 'dependencies' || $env == 'devDependencies') {
                foreach ($item as $depend => $value) {
                    if (!$sysWebPackageDep->hasDepend($depend, $isDev)) {
                        unset($item[$depend]);
                    }
                }
                $moduleDepend[$env] = $item;
            } elseif ($env == 'nuxtDependencies' || $env == 'nuxtDevDependencies') {
                foreach ($item as $depend => $value) {
                    if (!$sysWebNuxtPackageDep->hasDepend($depend, $isDev)) {
                        unset($item[$depend]);
                    }
                }
                $moduleDepend[$env] = $item;
            }
        }
        return $moduleDepend;
    }

    public function checkpackage(): bool
    {
        if (!is_dir($this->modulesDir)) {
            throw new Exception('Module package file does not exist');
        }
        $info = $this->getinfo();
        // [opcode 真值] 6 元素字面量数组，偶数槽 uid/title/intro 可读；奇数槽字符串指针被编译器
        // 抹除（RAW0），依真实 info.ini 字段语义择优还原（与已安装模块字段校验等价）
        foreach (['uid', 'authorid', 'title', 'author', 'intro', 'version'] as $item) {
            if (!array_key_exists($item, $info)) {
                Filesystem::delDir($this->modulesDir);
                throw new Exception('Basic configuration of the Module is incomplete');
            }
        }
        return true;
    }

    public function getinfo(): array
    {
        return Server::getIni($this->modulesDir);
    }

    public function setinfo(array|null $kv = null, array|null $arr = null): bool
    {
        if ($kv) {
            $ini = $this->getinfo();
            foreach ($kv as $key => $value) {
                $ini[$key] = $value;
            }
            return Server::setIni($this->modulesDir, $ini);
        }
        if ($arr) {
            return Server::setIni($this->modulesDir, $arr);
        }
        throw new Exception('Parameter error');
    }

    public static function isemptyarray($arr): bool
    {
        foreach ($arr as $item) {
            if (is_array($item)) {
                if (!self::isemptyarray($item)) {
                    return false;
                }
            } elseif ($item) {
                return false;
            }
        }
        return true;
    }
}
