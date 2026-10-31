<?php

use app\admin\model\AdminRule;
use app\admin\model\channel\Type;
use app\admin\model\UserRule;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV103 extends Migrator
{
    public function change(): void
    {
        $current = '准备开始';
        try {
            $current = '新增配置项';
            $this->addConfig();

            $current = '新增通道轮询相关的表';
            $this->createChannelPoolTable();

            $current = '更新订单表';
            $this->upOrderTable();

            $current = '新增通知模板表';
            $this->createNotifyTemplateTable();

            $current = '清理会员套餐分组数据';
            $this->clearSubData();

            $current = '新增支付通道类型';
            $this->addPayChannel();

            $current = '新增前台菜单';
            $this->addUserRule();

            $current = '新增后台菜单';
            $this->addAdminRule();

        } catch (\Exception $e) {
            throw new \ba\Exception('数据库迁移失败,' . $current . '时出问题：' . $e->getMessage());
        }

        Db::name('config')->where('name', 'version')->update(['value' => 'V1.0.3']);
    }

    private function addConfig(): void
    {
        // 新增测试收款配置
        $pay_config = Db::name('config')->where('name', 'test_pay_config')->find();
        if (!$pay_config) {
            Db::name('config')->insert([
                'name' => 'test_pay_config',
                'group' => 'payment',
                'title' => '测试支付配置',
                'tip' => '',
                'type' => 'epayConfig',
                'value' => '{}',
                'content' => '',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '',
                'weigh' => 0,
            ]);
        }

        // 新增实名认证配置项
        $admin_force_real_name = Db::name('config')->where('name', 'admin_force_real_name')->find();
        if (!$admin_force_real_name) {
            Db::name('config')->insert([
                'name' => 'admin_force_real_name',
                'group' => 'security',
                'title' => '管理员强制实名',
                'tip' => '',
                'type' => 'radio',
                'value' => 'close',
                'content' => '{"close":"\u5173\u95ed","open":"\u5f00\u542f"}',
                'rule' => '',
                'allow_del' => 0,
                'extend' => '{"baInputExtend":{"border":true}}',
                'weigh' => 0,
            ]);
        }
    }


    /**
     * 新增通道轮询相关的表
     * @return void
     */
    private function createChannelPoolTable(): void
    {
        // 轮询池表
        if (!$this->hasTable('channel_pool')) {
            $table = $this->table('channel_pool', [
                'id' => false,
                'comment' => '通道轮询池',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '池名称', 'null' => false])
                ->addColumn('pay_type', 'string', ['limit' => 30, 'default' => 0, 'comment' => '支付方式', 'null' => false])
                ->addColumn('polling_mode', 'string', ['limit' => 30, 'default' => 0, 'comment' => '轮询模式', 'null' => false])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注', 'null' => true])
                ->addColumn('weight', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '权重'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }

        // 轮询池通道表
        if (!$this->hasTable('channel_pool_account')) {
            $table = $this->table('channel_pool_account', [
                'id' => false,
                'comment' => '通道轮询池通道表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('pool_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '池ID', 'null' => false])
                ->addColumn('account_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '通道ID', 'null' => false])
                ->addColumn('weight', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '权重'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }


    private function upOrderTable(): void
    {
        // 订单表新增site_name字段
        $table = $this->table('order');
        if (!$table->hasColumn('site_name')) {
            $table->addColumn('site_name', 'string', ['limit' => 100, 'default' => '', 'comment' => '站点名称', 'null' => true])
                ->update();
        }

        // 新增notify_time字段
        if (!$table->hasColumn('notify_time')) {
            $table->addColumn('notify_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '通知时间'])
                ->update();
        }
    }

    public function createNotifyTemplateTable(): void
    {
        if (!$this->hasTable('notify_template')) {
            $table = $this->table('notify_template', [
                'id' => false,
                'comment' => '通知模板表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '模板名称', 'null' => false])
                ->addColumn('event', 'string', ['limit' => 100, 'default' => '', 'comment' => '事件名称', 'null' => false])
                ->addColumn('notify_type', 'string', ['limit' => 100, 'default' => '', 'comment' => '通知类型', 'null' => false])
                ->addColumn('title', 'text', [ 'comment' => '模板标题', 'null' => true])
                ->addColumn('content', 'text', [ 'comment' => '模板内容', 'null' => true])
                ->addColumn('vars', 'text', [ 'comment' => '模板变量', 'null' => true])
                ->addColumn('template_id', 'string', ['limit' => 100, 'default' => '', 'comment' => '模板ID', 'null' => true])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '状态', 'null' => false])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注', 'null' => true])
                ->addColumn('create_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('update_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->create();
        }
    }

    // 清理会员套餐分组数据
    public function clearSubData(): void
    {
        $groupIds = Db::name('user_subscribe_group')->column('id');

        // 会员套餐分组ID清理
        $subscribeQuery = Db::name('user_subscribe')->whereNotNull('group_id');
        if (!empty($groupIds)) {
            $subscribeQuery->whereNotIn('group_id', $groupIds);
            $subscribeQuery->update(['group_id' => 0]);
        }


        // 套餐分组绑定通道清理
        $groupChannelQuery = Db::name('user_subscribe_group_channel');
        if (!empty($groupIds)) {
            $groupChannelQuery->whereNotIn('group_id', $groupIds);
            $groupChannelQuery->delete();
        }

    }


    // 新增微信官方扫码通道和支付宝官方扫码通道
    public function addPayChannel(): void
    {
        $alipay = Type::where('type', 'alipay_scan')->find();
        if (empty($alipay)) {
            Type::create([
                'pay_type' => 'alipay',
                'type' => 'alipay_scan',
                'name' => '支付宝官方扫码支付',
                'weigh' => 0,
                'status' => 1,
            ]);
        }

        $wxpay = Type::where('type', 'wxpay_scan')->find();
        if (empty($wxpay)) {
            Type::create([
                'pay_type' => 'alipay',
                'type' => 'wxpay_scan',
                'name' => '微信官方-扫码支付',
                'weigh' => 0,
                'status' => 1,
            ]);
        }
    }


    // 前台菜单
    private function addUserRule(): void
    {
        $pollingPool = UserRule::where('name', 'pollingpool')->find();
        if (empty($pollingPool)) {
            // 上级通道
            $pm = UserRule::where('name','place')->find();
            UserRule::create([
                'pid' => $pm->id,
                'type' => 'route',
                'name' => 'pollingpool',
                'title' => '轮询池',
                'path' => '/pollingpool',
                'icon' => 'icon-dian',
                'menu_type' => 'tab',
                'component' => '/channel/pollingpool',
                'weigh' => 47,
                'status' => 1,
                'extend' => 'none'
            ]);
        }
    }

    // 后台菜单
    private function addAdminRule(): void
    {
        $btnMenus = [
            ['name' => 'index', 'title' => '查看'],
            ['name' => 'add', 'title' => '添加'],
            ['name' => 'edit', 'title' => '编辑'],
            ['name' => 'del', 'title' => '删除'],
            ['name' => 'sortable', 'title' => '快速排序'],
        ];
        $menus = [
            [
                'title' => '支付通道管理',
                'name' => 'channel',
                'icon' => 'fa fa-th-list',
                // 新增用户钱包管理相关菜单 一共3个
                'sub_menus' => [
                    ['title' => '通道轮询池', 'name' => 'channel/pool', 'icon' => 'el-icon-ChromeFilled', 'component' => '/src/views/backend/channel/pool/index.vue']
                ]
            ],
            [
                'title' => '通知管理',
                'name' => 'notify',
                'icon' => 'el-icon-BellFilled',
                'sub_menus' => [
                    ['title' => '通知模板管理', 'name' => 'notify/template', 'icon' => 'el-icon-ChatDotSquare', 'component' => '/src/views/backend/notify/template/index.vue']
                ]
            ],
            [
                'title' => '权限管理',
                'name' => 'auth',
                'icon' => 'fa fa-group',
                'sub_menus' => [
                    ['title' => '管理员实名认证管理', 'name' => 'auth/realName', 'icon' => 'fa fa-vcard', 'component' => '/src/views/backend/auth/realName/index.vue']
                ]
            ],
        ];

        $adminRule = new AdminRule();
        foreach ($menus as $menuDir) {
            // 这是菜单目录层级
            $dir = AdminRule::where('name', $menuDir['name'])->find();
            if (empty($dir)) {
                $dir = AdminRule::create([
                    'pid' => 0,
                    'type' => 'menu_dir',
                    'title' => $menuDir['title'],
                    'name' => $menuDir['name'],
                    'path' => $menuDir['name'],
                    'icon' => $menuDir['icon'],
                    'menu_type' => 'tab',
                    'keepalive' => 0,
                    'extend' => 'none',
                ]);
            }

            foreach ($menuDir['sub_menus'] as $subMenu) {
                // 这是主要的菜单
                $menu = AdminRule::where('name', $subMenu['name'])->find();
                if (empty($menu)) {
                    $menu = AdminRule::create([
                        'pid' => $dir->id,
                        'type' => 'menu',
                        'title' => $subMenu['title'],
                        'name' => $subMenu['name'],
                        'path' => $subMenu['name'],
                        'icon' => $subMenu['icon'],
                        'menu_type' => 'tab',
                        'component' => $subMenu['component'],
                        'keepalive' => 1,
                    ]);

                    // 还要新增菜单下的五个按钮菜单
                    $btnList = [];
                    foreach ($btnMenus as $btn) {
                        // 不需要判断在不在
                        $btnList[] = [
                            'pid' => $menu->id,
                            'type' => 'button',
                            'title' => $btn['title'],
                            'name' => $menu->name . '/' . $btn['name'],
                            'keepalive' => 0,
                        ];
                    }
                    $adminRule->saveAll($btnList);
                }
            }
        }
    }
}
