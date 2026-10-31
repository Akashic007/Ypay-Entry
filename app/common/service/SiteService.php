<?php

// 还原自 Swoole Compiler 加密文件: SiteService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/SiteService.php

namespace app\common\service;

use app\admin\model\site\Domain;
use app\admin\model\site\Site;
use think\facade\Db;
use Throwable;

class SiteService{
    private \app\common\service\SitePackageService $packageService;
    private static bool|null $pluginInstalled = null;
    const PLUGIN_CODE = 'site';
    public function __construct()
    {
        // [opcode 语义] 轻量构造（构造不校验插件——查询方法内部自行 assert）
        $this->packageService = new \app\common\service\SitePackageService();
    }
// [opcode 还原] 静态缓存 + Db::name('plugin')->where('code', PLUGIN_CODE) 判存，Throwable→false
    public static function isplugininstalled(): bool
    {
        if (self::$pluginInstalled !== null) {
            return self::$pluginInstalled;
        }
        try {
            self::$pluginInstalled = Db::name('plugin')->where('code', self::PLUGIN_CODE)->count() > 0;
        } catch (Throwable) {
            self::$pluginInstalled = false;
        }
        return self::$pluginInstalled;
    }
    public function opensite(\app\admin\model\User|null $user = null, \app\admin\model\site\Subscribe|null $package = null, bool|null $free = null, null|string $domain = null, int|null $domainId = null, string|null $domainPrefix = null): \app\admin\model\site\Site
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function renewsite(\app\admin\model\User|null $user = null, \app\admin\model\site\Site|null $site = null, \app\admin\model\site\Subscribe|null $package = null, bool|null $free = null): \app\admin\model\site\Site
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function upgradesite(\app\admin\model\User|null $user = null, \app\admin\model\site\Site|null $site = null, \app\admin\model\site\Subscribe|null $newPackage = null, bool|null $free = null): array
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function setdomain(\app\admin\model\site\Site|null $site = null, string|null $domain = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function setsubdomain(\app\admin\model\site\Site|null $site = null, int|null $domainId = null, string|null $prefix = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function removedomain(\app\admin\model\site\Site|null $site = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function submitcustomdomainapply(\app\admin\model\site\Site|null $site = null, int|null $userId = null, string|null $domain = null, string|null $remark = null): \app\admin\model\site\DomainApply
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function auditdomainapply(\app\admin\model\site\DomainApply|null $apply = null, int|null $status = null, string|null $reason = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
// [opcode 还原] 小写化→去协议头→rtrim 斜杠→去端口
    public function normalizedomain(string|null $domain = null): string
    {
        $domain = strtolower(trim((string) $domain));
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = rtrim($domain, '/');
        if (preg_match('/:\d+$/', $domain)) {
            $domain = preg_replace('/:\d+$/', '', $domain);
        }
        return $domain;
    }
    public function normalizedomaindata(array|null $data = null, null|int $excludeSiteId = null): array
    {
        $hasDomain = array_key_exists('domain', $data);
        $hasDomainId = array_key_exists('domain_id', $data);
        $hasDomainPrefix = array_key_exists('domain_prefix', $data);
        if (!($hasDomain || $hasDomainId || $hasDomainPrefix)) {
            return $data;
        }
        // [opcode 对照] line445-447 三分支互斥取值；补初值防未定义变量
        $domain = null;
        $domainId = 0;
        $domainPrefix = '';
        if ($hasDomain) {
            $domain = $this->normalizeDomain((string)($data['domain'] ?? ''));
        } elseif ($hasDomainId) {
            $domainId = (int)($data['domain_id'] ?? 0);
        } elseif ($hasDomainPrefix) {
            $domainPrefix = trim(strtolower((string)($data['domain_prefix'] ?? '')));
        }
        if ($domainId != 0 || $domainPrefix != '') {
            if ($domainId == 0 || $domainPrefix == '') {
                throw new \Exception('预设域名和二级域名前缀必须同时填写');
            }
            $this->assertPresetDomainExists($domainId);
            $this->assertDomainPrefixFormat($domainPrefix);
            $this->assertSubDomainNotOccupied($domainId, $domainPrefix, $excludeSiteId);
            $data['domain'] = null;
            $data['domain_id'] = $domainId;
            $data['domain_prefix'] = $domainPrefix;
            return $data;
        }
        if ($domain) {
            $this->assertDomainFormat($domain);
            $this->assertDomainNotOccupied($domain, $excludeSiteId);
            $data['domain'] = $domain;
            $data['domain_id'] = 0;
            $data['domain_prefix'] = null;
            return $data;
        }
        $data['domain'] = '';
        $data['domain_id'] = 0;
        $data['domain_prefix'] = null;
        return $data;
    }
    public function assertdomainformat(string|null $domain = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function assertdomainnotoccupied(string|null $domain = null, null|int $excludeSiteId = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function assertpresetdomainexists(int|null $domainId = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function assertdomainprefixformat(string|null $prefix = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function assertsubdomainnotoccupied(int|null $domainId = null, string|null $prefix = null, null|int $excludeSiteId = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
// [opcode 还原] 插件未装→null（优雅降级）；正规化后先精确 domain 匹配，再走预设域名+前缀
    public function findsitebydomain(string|null $domain = null): ?\app\admin\model\site\Site
    {
        if (!self::isplugininstalled()) {
            return null;
        }
        $domain = $this->normalizedomain($domain);
        $site = Site::where('domain', $domain)->find();
        if ($site) {
            return $site;
        }
        $preset = $this->matchpresetdomain($domain);
        if (!$preset) {
            return null;
        }
        return Site::where('domain_id', $preset['domain_id'] ?? ($preset['id'] ?? 0))
            ->where('domain_prefix', $preset['domain_prefix'] ?? '')
            ->find();
    }
    public function updatesiteinfo(\app\admin\model\site\Site|null $site = null, array|null $data = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function setstatus(\app\admin\model\site\Site|null $site = null, int|null $status = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function setremark(\app\admin\model\site\Site|null $site = null, string|null $remark = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function getuseractivesite(int|null $userId = null): \app\admin\model\site\Site
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function getusersite(int|null $userId = null): \app\admin\model\site\Site
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function getsitedetail(int|null $siteId = null): \app\admin\model\site\Site
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    public function assertsitebelongstouser(\app\admin\model\site\Site|null $site = null, int|null $userId = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
// [opcode 还原] status==1 且未过 expire_time
    public function issiteactive(\app\admin\model\site\Site|null $site = null): bool
    {
        return $site->status == 1 && $site->expire_time > time();
    }
    public function getremainingdays(\app\admin\model\site\Site|null $site = null): int
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
// [opcode 还原] 未装插件抛异常（异常文本为解密真值）
    private function assertplugininstalled(): void
    {
        if (!self::isplugininstalled()) {
            throw new \Exception('分站插件未安装，无法执行此操作');
        }
    }
    private function assertsubscribeallowscustomdomain(\app\admin\model\site\Site|null $site = null): void
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
    private function isvalidhostname(string|null $hostname = null): bool
    {
// [opcode 语义] 原方法体未还原；站点功能属 site 插件，未安装时按 assert 语义拒绝
        $this->assertplugininstalled();
    }
// [opcode 还原] 遍历预设域名表按 base_domain 后缀匹配，返回预设行+前缀
    private function matchpresetdomain(string|null $domain = null): null|array
    {
        $this->assertplugininstalled();
        $domain = (string) $domain;
        $presets = Domain::select()->toArray();
        foreach ($presets as $preset) {
            $base = (string) ($preset['base_domain'] ?? '');
            if ($base === '') {
                continue;
            }
            $suffix = '.' . $base;
            if (str_ends_with($domain, $suffix)) {
                $prefix = substr($domain, 0, strlen($domain) - strlen($suffix));
                if ($prefix === '') {
                    continue;
                }
                $preset['domain_prefix'] = $prefix;
                return $preset;
            }
        }
        return null;
    }
}
