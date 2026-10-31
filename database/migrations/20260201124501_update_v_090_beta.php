<?php

use app\admin\model\AdminRule;
use app\admin\model\UserRule;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV090Beta extends Migrator
{

    /**
     * @throws \ba\Exception
     */
    public function change(): void
    {
        Db::startTrans();
        try {
            // 调用所有方法
            $current = '创建代理表';
            $this->createAgent();
            $current = '创建代理套餐表';
            $this->createAgentSubscribe();
            $current = '创建代理可提现余额日志表';
            $this->createAgentWithdrawalLog();
            $current = '创建套餐进件折扣表';
            $this->createSubscribeEntryDiscount();
            $current = '创建用户进件折扣表';
            $this->createUserEntryDiscount();
            $current = '创建代理进件表';
            $this->createAgentUserEntry();
            $current = '创建上下游表';
            $this->createUpDown();
            $current = '修改订单表';
            $this->order();
            $current = '修改用户表';
            $this->user();
            $current = '新增后台菜单';
            $this->adminRule();
            $current = '新增用户菜单';
            $this->userRule();
            $current = '修改进件通道表';
            $this->entryChannel();
            $current = '修改商户进件记录表';
            $this->merchantEntry();
            $current = '修改支付通道表';
            $this->channelAccount();
            $current = '修改商户表';
            $this->merchant();
            Db::name('config')->where('name', 'version')->update(['value' => 'V0.9.0-Beta']);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new \ba\Exception('数据库迁移失败：' . $current . '时出问题');
        }
    }


    /**
     * 新增代理表
     * @return void
     */
    public function createAgent(): void
    {
        if (!$this->hasTable('agent')) {
            $table = $this->table('agent', [
                'id' => false,
                'comment' => '代理',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('subscribe_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '订阅套餐ID', 'null' => false])
                ->addColumn('invite_code', 'string', ['limit' => 100, 'default' => '', 'comment' => '邀请码', 'null' => false])
                ->addColumn('api_id', 'string', ['limit' => 100, 'default' => '', 'comment' => 'API ID', 'null' => false])
                ->addColumn('api_key', 'string', ['limit' => 100, 'default' => '', 'comment' => 'API密钥', 'null' => false])
                ->addColumn('withdrawal_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '可提现余额', 'null' => false])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();

        }
    }

    /**
     * 新增代理套餐表
     * @return void
     */
    public function createAgentSubscribe(): void
    {
        if (!$this->hasTable('agent_subscribe')) {
            $table = $this->table('agent_subscribe', [
                'id' => false,
                'comment' => '代理套餐',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '套餐名称'])
                ->addColumn('code', 'string', ['limit' => 100, 'default' => '', 'comment' => '套餐代码'])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('is_top', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否顶级套餐'])
                ->addColumn('price', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '价格'])
                ->addColumn('rebate_ratio', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '返佣比例'])
                ->addColumn('entry_discount', 'integer', ['limit' => 3, 'default' => 0, 'comment' => '进件折扣'])
                ->addColumn('weight', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '权重'])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 新增代理可提现余额日志表
     * @return void
     */
    public function createAgentWithdrawalLog(): void
    {
        if (!$this->hasTable('agent_withdrawal_money_log')) {
            $table = $this->table('agent_withdrawal_money_log', [
                'id' => false,
                'comment' => '代理可提现余额日志',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('agent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理ID', 'null' => false])
                ->addColumn('money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '金额'])
                ->addColumn('before', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变更前金额'])
                ->addColumn('after', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变更后金额'])
                ->addColumn('memo', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('type', 'integer', ['limit' => 2, 'default' => 0, 'comment' => '类型'])
                ->addColumn('content', 'string', ['limit' => 255, 'default' => '', 'comment' => '内容'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();


        }
    }

    /**
     * 新增套餐进件通道折扣表
     * @return void
     */
    public function createSubscribeEntryDiscount(): void
    {
        if (!$this->hasTable('agent_subscribe_entry_discount')) {
            $table = $this->table('agent_subscribe_entry_discount', [
                'id' => false,
                'comment' => '代理套餐和进件通道的折扣关系表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('ec_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '进件通道ID', 'null' => false])
                ->addColumn('subscribe_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '订阅套餐ID', 'null' => false])
                ->addColumn('discount', 'integer', ['limit' => 3, 'default' => 0, 'comment' => '进件折扣'])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 新增用户进件通道折扣表
     * @return void
     */
    public function createUserEntryDiscount(): void
    {
        if (!$this->hasTable('agent_user_entry_discount')) {
            $table = $this->table('agent_user_entry_discount', [
                'id' => false,
                'comment' => '代理用户和进件通道的折扣关系表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('agent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理ID', 'null' => false])
                ->addColumn('ec_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '进件通道ID', 'null' => false])
                ->addColumn('discount', 'integer', ['limit' => 3, 'default' => 0, 'comment' => '进件折扣'])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注'])
                ->addColumn('api_price', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '下游接口进件价格'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 新增代理用户进件记录表
     * @return void
     */
    public function createAgentUserEntry(): void
    {
        if (!$this->hasTable('agent_user_entry')) {
            $table = $this->table('agent_user_entry', [
                'id' => false,
                'comment' => '代理给用户的进件记录',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('agent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理ID', 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('me_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '进件记录', 'null' => false])
                ->addColumn('price', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理进件价格'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 新增上下游表
     */
    public function createUpDown(): void
    {
        // 上游表
        if (!$this->hasTable('supply_upstream')) {
            $table = $this->table('supply_upstream', [
                'id' => false,
                'comment' => '上游管理',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '上游名称', 'null' => false])
                ->addColumn('api', 'string', ['limit' => 100, 'default' => '', 'comment' => '上游API地址', 'null' => false])
                ->addColumn('api_id', 'string', ['limit' => 100, 'default' => '', 'comment' => 'API ID', 'null' => false])
                ->addColumn('api_key', 'string', ['limit' => 100, 'default' => '', 'comment' => 'API密钥', 'null' => false])
                ->addColumn('rejected', 'string', ['limit' => 255, 'default' => '', 'comment' => '拒绝原因', 'null' => true])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注', 'null' => true])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('api_status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => 'API接口状态'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }

        // 下游表
        if (!$this->hasTable('supply_downstream')) {
            $table = $this->table('supply_downstream', [
                'id' => false,
                'comment' => '下游管理',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('agent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理ID', 'null' => false])
                ->addColumn('domain', 'string', ['limit' => 100, 'default' => '', 'comment' => '下游域名', 'null' => false])
                ->addColumn('rejected', 'string', ['limit' => 255, 'default' => '', 'comment' => '拒绝原因', 'null' => true])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注', 'null' => true])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();

        }
    }

    /**
     * 订单表
     * @return void
     */
    public function order(): void
    {
        // 新增source字段,在is_test字段后面
        $table = $this->table('order');
        if (!$table->hasColumn('source')) {
            $table->addColumn('source', 'string', ['limit' => 30, 'default' => 'local', 'comment' => '来源', 'null' => false, 'after' => 'is_test'])
                ->update();
        }
    }


    /**
     * 用户表
     */
    public function user(): void
    {
        // 用户表新增agent_id字段,在subscribe_id字段后面
        $table = $this->table('user');
        if (!$table->hasColumn('agent_id')) {
            $table->addColumn('agent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理ID', 'null' => false, 'after' => 'subscribe_id'])
                ->update();
        }
    }

    /**
     * 后台菜单表
     * @return void
     * @throws Exception
     */
    public function adminRule(): void
    {

        $adminRule = new AdminRule();
        // 菜单新增
        $menus = [
            [
                'title' => '代理商管理',
                'name' => 'agent',
                'icon' => 'fa fa-user-secret',
                'sub_menus' => [
                    ['title' => '代理套餐管理', 'name' => 'agent/subscribe', 'icon' => 'fa fa-th-large', 'component' => '/src/views/backend/agent/subscribe/index.vue'],
                    ['title' => '代理管理', 'name' => 'agent/agent', 'icon' => 'el-icon-Avatar', 'component' => '/src/views/backend/agent/agent/index.vue'],
                    ['title' => '提现余额日志', 'name' => 'agent/withdrawalMoneyLog', 'icon' => 'el-icon-Money', 'component' => '/src/views/backend/agent/subscribe/index.vue'],
                    ['title' => '套餐进件通道折扣', 'name' => 'agent/subscribeEntryDiscount', 'icon' => 'fa fa-gift', 'component' => '/src/views/backend/agent/subscribeEntryDiscount/index.vue'],
                    ['title' => '代理进件通道则扣', 'name' => 'agent/userEntryDiscount', 'icon' => 'el-icon-IceTea', 'component' => '/src/views/backend/agent/userEntryDiscount/index.vue'],
                    ['title' => '代理帮用户进件', 'name' => 'agent/userEntry', 'icon' => 'fa fa-user-plus', 'component' => '/src/views/backend/agent/userEntry/index.vue'],
                    ['title' => '套餐进件通道折扣', 'name' => 'agent/subscribeEntryDiscount', 'icon' => 'fa fa-gift', 'component' => '/src/views/backend/agent/subscribeEntryDiscount/index.vue'],
                    ['title' => '套餐进件通道折扣', 'name' => 'agent/subscribeEntryDiscount', 'icon' => 'fa fa-gift', 'component' => '/src/views/backend/agent/subscribeEntryDiscount/index.vue'],
                ]
            ],
            [
                'title' => '供应链管理',
                'name' => 'supply',
                'icon' => 'fa fa-chain',
                'sub_menus' => [
                    ['title' => '上游管理', 'name' => 'supply/upStream', 'icon' => 'fa fa-compress', 'component' => '/src/views/backend/supply/upStream/index.vue'],
                    ['title' => '下游管理', 'name' => 'supply/downStream', 'icon' => 'fa fa-expand', 'component' => '/src/views/backend/supply/downStream/index.vue'],

                ]
            ]
        ];

        $btnMenus = [
            ['name' => 'index', 'title' => '查看'],
            ['name' => 'add', 'title' => '添加'],
            ['name' => 'edit', 'title' => '编辑'],
            ['name' => 'del', 'title' => '删除'],
            ['name' => 'sortable', 'title' => '快速排序'],
        ];

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

    /**
     * 用户端菜单表
     */
    public function userRule(): void
    {
        $agent = UserRule::where('name', 'agent')->find();
        if (empty($agent)) {
            $agent = UserRule::create([
                'pid' => 0,
                'type' => 'route',
                'title' => '代理中心',
                'name' => 'agent',
                'path' => 'agent',
                'icon' => 'icon-aichegujiabeifen6',
                'menu_type' => 'tab',
                'extend' => 'none',
                'weight' => -9,
                'status' => 1
            ]);
        }

        $menus = [
            ['pid' => $agent->id, 'type' => 'route', 'title' => '下级商户管理', 'name' => 'subMerchant', 'path' => '/subMerchant', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/sub/merchant.vue', 'extend' => 'none', 'weight' => 39],
            ['pid' => $agent->id, 'type' => 'route', 'title' => '下级代理管理', 'name' => 'subAgent', 'path' => '/subAgent', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/sub/agent.vue', 'extend' => 'none', 'weight' => 38],
            ['pid' => $agent->id, 'type' => 'route', 'title' => '下游支付通道', 'name' => 'downAccount', 'path' => '/downAccount', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/down/account.vue', 'extend' => 'none', 'weight' => 36],
            ['pid' => $agent->id, 'type' => 'route', 'title' => '下游管理', 'name' => 'down', 'path' => '/down', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/down.vue', 'extend' => 'none', 'weight' => 37],
            ['pid' => $agent->id, 'type' => 'route', 'title' => '佣金管理', 'name' => 'agentMoney', 'path' => '/agentMoney', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/agent/money.vue', 'extend' => 'none', 'weight' => 36],
            ['pid' => $agent->id, 'type' => 'route', 'title' => '账户管理', 'name' => 'agentAccount', 'path' => '/agentAccount', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/agent/Account.vue', 'extend' => 'none', 'weight' => 40],
            ['pid' => $agent->id, 'type' => 'route', 'title' => '代理订单管理', 'name' => 'agentOrder', 'path' => '/agentOrder', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/agent/order.vue', 'extend' => 'none', 'weight' => 35],
            ['pid' => $agent->id, 'type' => 'route', 'title' => '代理进件管理', 'name' => 'agentEntry', 'path' => '/agentEntry', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/src/views/agent/entry.vue', 'extend' => 'none', 'weight' => 34],
        ];

        $userRule = new UserRule();
        $userRule->saveAll($menus);
    }

    /**
     * 进件通道
     * @return void
     */
    public function entryChannel(): void
    {
        /**
         * 更改进件通道表平台代码字段
         */
        $table = $this->table('entry_channel');
        if ($table->hasColumn('platform_code')) {
            $table->changeColumn('platform_code', 'string', ['limit' => 50, 'null' => false, 'default' => '', 'comment' => '平台代码'])
                ->update();
        }
    }

    /**
     * 进件记录表
     */
    public function merchantEntry(): void
    {
        $table = $this->table('merchant_entry');
        // 新增supply字段
        if (!$table->hasColumn('supply')) {
            $table->addColumn('supply', 'string', ['limit' => 20, 'null' => false, 'default' => 'local', 'comment' => '供应商类型', 'after' => 'status']);
        }
        // 新增up_id字段
        if (!$table->hasColumn('up_id')) {
            $table->addColumn('up_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '上游ID', 'null' => false, 'after' => 'status']);
        }
        // 新增down_id字段
        if (!$table->hasColumn('down_id')) {
            $table->addColumn('down_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '下游ID', 'null' => false, 'after' => 'status']);
        }
        // 新增source字段
        if (!$table->hasColumn('source')) {
            $table->addColumn('source', 'string', ['limit' => 30, 'default' => 'local', 'comment' => '来源', 'null' => false, 'after' => 'status']);
        }
        // 修改audit_result字段类型 改成text
        if ($table->hasColumn('audit_result')) {
            $table->changeColumn('audit_result', 'text', ['comment' => '审核结果', 'null' => true]);
        }
        $table->update();
    }

    public function channelAccount(): void
    {
        $table = $this->table('channel_account');
        // 新增source_type字段
        if (!$table->hasColumn('source_type')) {
            $table->addColumn('source_type', 'string', ['limit' => 30, 'default' => 'local', 'comment' => '来源类型', 'null' => false, 'after' => 'extend']);
        }
        // 新增source_id字段
        if (!$table->hasColumn('source_id')) {
            $table->addColumn('source_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '来源ID', 'null' => false, 'after' => 'extend']);
        }
        // 新增source_cid字段
        if (!$table->hasColumn('source_cid')) {
            $table->addColumn('source_cid', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '源通道ID', 'null' => true, 'after' => 'extend']);
        }
        // 新增mch_id字段
        if (!$table->hasColumn('mch_id')) {
            $table->addColumn('mch_id', 'string', ['limit' => 50, 'default' => '', 'comment' => '商户ID', 'after' => 'channel_type', 'null' => true]);
        }
        $table->update();
    }


    /**
     * 商户表
     */
    public function merchant(): void
    {
        // 新增source
        $table = $this->table('merchant');
        if (!$table->hasColumn('source')) {
            $table->addColumn('source', 'string', ['limit' => 30, 'default' => 'local', 'comment' => '来源', 'null' => false, 'after' => 'status']);
        }
        $table->update();

    }
}
