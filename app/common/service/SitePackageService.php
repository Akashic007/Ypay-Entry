<?php

// 还原自 Swoole Compiler 加密文件: SitePackageService.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/common/service/SitePackageService.php

namespace app\common\service;

use app\common\service\SiteService;
use think\Collection;

class SitePackageService{
// [opcode 还原] 插件未装→空 Collection；Subscribe 按 weigh desc，onlyEnabled 时 status=1
    public function getpackagelist(bool|null $onlyEnabled = null): \think\Collection
    {
        if (!SiteService::isplugininstalled()) {
            return new \think\Collection();
        }
        $query = \app\admin\model\site\Subscribe::order('weigh', 'desc');
        if ($onlyEnabled) {
            $query->where('status', 1);
        }
        return $query->select();
    }

// [opcode 还原] 插件校验（异常文本为解密真值）+ find + 上下架校验
    public function getpackage(int|null $packageId = null, bool|null $onlyEnabled = null): \app\admin\model\site\Subscribe
    {
        if (!SiteService::isplugininstalled()) {
            throw new \Exception('分站插件未安装');
        }
        $query = \app\admin\model\site\Subscribe::where('id', $packageId);
        if ($onlyEnabled) {
            $query->where('status', 1);
        }
        $package = $query->find();
        if (!$package) {
            throw new \Exception('分站套餐不存在或已下架');
        }
        return $package;
    }

    public function canupgrade(\app\admin\model\site\Subscribe|null $oldPackage = null, \app\admin\model\site\Subscribe|null $newPackage = null): bool
    {
    return (0 <= null);
    }

    public function getdailyvaluefen(\app\admin\model\site\Subscribe|null $package = null): int
    {
    // DECLARE_CONST $T3 $Sh44154
    if (!($T2)) {
        return 0;
        // DECLARE_CONST $T3 $Sh44154
        return (int)(null);
    }
    }

    public function calculateremainingvaluefen(\app\admin\model\site\Subscribe|null $package = null, int|null $remainingSeconds = null): int
    {
    if ($T4) {
        // DECLARE_CONST $T5 $Sh44156
        if (!($T4)) {
            return 0;
            return (int)(null);
        }
    }
    }

    public function calculatedaysfromvaluefen(int|null $valueFen = null, \app\admin\model\site\Subscribe|null $targetPackage = null): int
    {
    if ($T3) {
        // DECLARE_CONST $T4 $Sh44161
        if (!($T3)) {
            return 0;
            if (!($T3)) {
                return 0;
                return (int)(null);
            }
        }
    }
    }

    public function calculateexpiretime(int|null $baseTime = null, int|null $days = null): int
    {
    if (!($T2)) {
        new InvalidArgumentException();
        throw $T2;
        return ($Sh44163 + $T3);
    }
    }

    public function calculateupgradebonusdays(\app\admin\model\site\Subscribe|null $oldPackage = null, \app\admin\model\site\Subscribe|null $newPackage = null, int|null $remainingSeconds = null): int
    {
    return null;
    }

    public function assertpackageavailable(\app\admin\model\site\Subscribe|null $package = null): void
    {
    // DECLARE_CONST $T2 $Sh44169
    // IS_NOT_EQUAL $T2 1
    if (!($T1)) {
        new Exception();
        throw $T1;
        // DECLARE_CONST $T2 $Sh44169
        if (!($T1)) {
            new Exception();
            throw $T1;
            return;
        }
    }
    }

}
