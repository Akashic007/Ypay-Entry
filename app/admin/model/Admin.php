<?php

// 还原自 Swoole Compiler 加密文件: Admin.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/Admin.php

namespace app\admin\model;

class Admin extends \think\Model {
    protected $autoWriteTimestamp = true;
    protected $type = ['extend' => 'array'];
    protected $append = ['group_arr', 'group_name_arr'];
    public function getgrouparrattr($value, $row): array
    {
        return \think\facade\Db::name('admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
    }

    public function getgroupnamearrattr($value, $row): array
    {
        $groupAccess = \think\facade\Db::name('admin_group_access')
            ->where('uid', $row['id'])
            ->column('group_id');
        return AdminGroup::whereIn('id', $groupAccess)->column('name');
    }

    public function getavatarattr($value): string
    {
        return full_url($value, false, config('buildadmin.default_avatar'));
    }

    public function setavatarattr($value): string
    {
        return $value == full_url('', false, config('buildadmin.default_avatar')) ? '' : $value;
    }

    public function getlastlogintimeattr($value): string
    {
        return $value ? date('Y-m-d H:i:s', (int) $value) : '';
    }

    public function resetpassword(int|string|null $uid = null, string|null $newPassword = null): \app\admin\model\Admin
    {
        $this->where(['id' => $uid])->update(['password' => hash_password($newPassword)]);
        return $this->find((int) $uid);
    }

    public function getextendattr($value): array
    {
        return $value ?: [];
    }

    public function gettotpinfoattr($value, $row): array
    {
        $extend = is_array($row['extend'] ?? null) ? $row['extend'] : [];
        $totp   = $extend['totp'] ?? [];
        return [
            'status' => (int) ($totp['status'] ?? 0),
            'bind'   => (int) ($totp['bind'] ?? 0),
        ];
    }

    public function realname(): \think\model\relation\HasOne
    {
        // opcode: hasOne(auth\RealName,'admin_id')
return $this->hasOne(\app\admin\model\auth\RealName::class, 'admin_id');
    }

    public function setextend(int|string|null $uid = null, array|null $extend = null): bool
    {
        try {
            $admin = self::find((int) $uid);
            if (!$admin) {
                return false;
            }
            $admin->extend = $extend;
            $admin->save();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

}
