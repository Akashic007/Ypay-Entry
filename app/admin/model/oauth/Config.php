<?php

// 还原自 Swoole Compiler 加密文件: Config.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/admin/model/oauth/Config.php

namespace app\admin\model\oauth;

class Config extends \think\Model {
    protected $name = 'oauth_config';
    protected $autoWriteTimestamp = false;
    protected $type = ['config' => 'array'];
    protected static function onafterinsert($model): void
    {
        if (is_null($model->weigh)) {
            $pk = $model->getPk();
            // [opcode 对照] strlen($model[$pk]) >= 19（雪花长ID）→ weigh=表行数；否则 weigh=主键值（同 UserRule 模式）
            if (19 <= strlen($model[$pk])) {
                $model->where($pk, $model[$pk])->update(['weigh' => $model->count()]);
            } else {
                $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
            }
        }
    }

    public function getconfigattr($value)
    {
        return $value ? (array) json_decode($value, true) : [];
    }

    // ⚠️ 语义重建：getlist/getloginmethod/getmethodconfig 原始方法体断链，
    // 按 as_oauth_config 表结构（name/title/type/value/group/weigh/config）重建。
    // getloginmethod 返回登录页可用的第三方登录方式清单（value=open 且非 string 类型配置），
    // 当前表内仅 aggregation 上游配置，自然返回空列表 = 登录页无第三方按钮（与数据一致）。
    public function getlist(): array
    {
        // [opcode 还原] 按 weigh 倒序 select 后 reduce 按 group 分组
        return self::order('weigh', 'desc')->select()->reduce(function ($carry, $item) {
            $carry[$item['group']][] = $item;
            return $carry;
        }, []);
    }

    public function getloginmethod(): array
    {
        // [opcode 还原] 取 login_method 配置项的 config 值，异常回退空数组
        try {
            $list = self::where(['name' => 'login_method', 'group' => 'config'])->find()->toArray();
            return $list['config'];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getmethodconfig(string|null $group = null): array
    {
        // [opcode 还原] 指定 group 配置项的 name=>value 映射
        $rows = self::where('group', $group)->select()->toArray();
        $config = [];
        foreach ($rows as $row) {
            $config[$row['name']] = $row['value'];
        }
        return $config;
    }

}
