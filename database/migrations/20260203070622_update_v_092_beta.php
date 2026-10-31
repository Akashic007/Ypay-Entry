<?php

use app\admin\model\AdminRule;
use app\admin\model\UserRule;
use think\facade\Db;
use think\migration\Migrator;
use think\migration\db\Column;

class UpdateV092Beta extends Migrator
{
    /**
     * @throws \ba\Exception
     */
    public function change()
    {
        Db::startTrans();
        try {
            $current = '修复代理套餐表';
            $this->agentSubscribe();

            $current = '排序会员菜单';
            $this->userRule();

            $current = '新增收款信息字段';
            $this->agent();

            $current = '创建代理商提现申请日志表';
            $this->createAgentWithdrawalApplyLog();

            $current = '创建代理商提现申请菜单';
            $this->adminRule();

            Db::name('config')->where('name', 'version')->update(['value' => 'V0.9.2-Beta']);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \ba\Exception('数据库迁移失败：' . $current . '时出问题');
        }
    }

    /**
     * 代理套餐表
     */
    public function agentSubscribe(): void
    {
        // 修复字段名称错误：weight -> weigh
        $table = $this->table('agent_subscribe');
        if ($table->hasColumn('weight')) {
            $table->renameColumn('weight', 'weigh')->update();
        }
    }

    /**
     * 用户菜单表
     */
    public function userRule(): void
    {
        // 修复排序，上个迁移文件排序的字段写错了
        // 优化菜单排序
        $menus = [
            ['name' => 'dashboard', 'weigh' => 100],
            ['name' => 'indexSet', 'weigh' => 99],
            ['name' => 'paylist', 'weigh' => 98],
            ['name' => 'place', 'weigh' => 97],
            ['name' => 'Api', 'weigh' => 96],
            ['name' => 'Station', 'weigh' => 95],
            ['name' => 'apply', 'weigh' => 94],
            ['name' => 'agent', 'weigh' => 93],
        ];


        $userMenus = UserRule::where('menu_type', 'tab')
            ->select();
        foreach ($userMenus as $userMenu) {
            foreach ($menus as $menu) {
                if ($userMenu['name'] == $menu['name']) {
                    // 更新排序
                    $userMenu->weigh = $menu['weigh'];
                    $userMenu->save();
                }
            }
        }
    }

    /**
     * 代理商表
     */
    public function agent(): void
    {

        // 新增收款信息字段
        $table = $this->table('agent');
        if (!$table->hasColumn('pay_account')) {
            $table->addColumn('pay_account', 'text', ['comment' => '收款账号', 'null' => true, 'after' => 'remark'])
                ->update();
        }
    }

    public function createAgentWithdrawalApplyLog(): void
    {
        if (!$this->hasTable('agent_withdrawal_apply_log')) {
            $table = $this->table('agent_withdrawal_apply_log', [
                'id' => false,
                'comment' => '代理商提现申请日志',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('agent_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理ID', 'null' => false])
                ->addColumn('withdraw_no', 'string', ['limit' => 32, 'comment' => '提现单号', 'null' => false, 'default' => ''])
                ->addColumn('apply_amount', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '申请金额'])
                ->addColumn('fee_amount', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '手续费金额'])
                ->addColumn('final_amount', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '实际到账金额'])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('rejected', 'string', ['limit' => 255, 'comment' => '拒绝理由', 'null' => true, 'default' => ''])
                ->addColumn('remark', 'string', ['limit' => 255, 'comment' => '备注', 'null' => true, 'default' => ''])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('pay_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '打款时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 后台菜单
     * @return void
     * @throws Exception
     */
    public function adminRule(): void
    {
        $pMenu = AdminRule::where('name', 'agent')->find();

        // 代理商提现申请
        $withdrawApply = AdminRule::where('name', 'agent/withdrawalApplyLog')->find();
        if (!$withdrawApply) {
            $withdrawApply = AdminRule::create([
                'pid' => $pMenu->id,
                'type' => 'menu',
                'title' => '提现申请',
                'name' => 'agent/withdrawalApplyLog',
                'path' => 'agent/withdrawalApplyLog',
                'icon' => 'local-withdrawal',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/agent/withdrawalApplyLog/index.vue',
                'keepalive' => 1,
            ]);
            $btnMenus = [
                ['name' => 'index', 'title' => '查看'],
                ['name' => 'add', 'title' => '添加'],
                ['name' => 'edit', 'title' => '编辑'],
                ['name' => 'del', 'title' => '删除'],
                ['name' => 'sortable', 'title' => '快速排序'],
            ];

            foreach ($btnMenus as $btn) {
                // 不需要判断在不在
                $btnList[] = [
                    'pid' => $withdrawApply->id,
                    'type' => 'button',
                    'title' => $btn['title'],
                    'name' => $withdrawApply->name . '/' . $btn['name'],
                    'keepalive' => 0,
                ];
            }
            $adminRule = new AdminRule();
            $adminRule->saveAll($btnList);
        }
    }
}
