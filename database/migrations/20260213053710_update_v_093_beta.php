<?php

use app\admin\model\AdminRule;
use app\admin\model\UserRule;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV093Beta extends Migrator
{
    /**
     * @throws \ba\Exception
     */
    public function change()
    {
        Db::startTrans();
        try {
            $current = '进件通道表新增订单费率字段';
            $this->entryChannel();

            $current = '代理商套餐表新增订单费率字段';
            $this->agentSubscribe();

            $current = '新增多类型用户费率表';
            $this->createUserRate();

            $current = '新增代理套餐与进件通道的费率表';
            $this->createAgentSubscribeEntryRate();

            $current = '新增用户套餐与进件通道的费率表';
            $this->userSubscribeEntryRate();

            $current = '删除前台菜单 - 下级代理管理';
            $this->userRule();

            $current = '隐藏一些后台菜单';
            $this->adminRule();

            Db::name('config')->where('name', 'version')->update(['value' => 'V0.9.3-Beta']);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \ba\Exception('数据库迁移失败：' . $current . '时出问题');
        }
    }

    /**
     * 进件通道表新增订单费率字段
     */
    private function entryChannel(): void
    {
        $table = $this->table('entry_channel');
        if (!$table->hasColumn('order_rate')) {
            $table->addColumn('order_rate', 'integer', ['default' => 0, 'comment' => '订单费率', 'limit' => 11, 'null' => false, 'after' => 'price'])
                ->update();
        }
    }

    /**
     * 代理商套餐表新增订单费率字段
     */
    private function agentSubscribe(): void
    {
        $table = $this->table('agent_subscribe');
        if (!$table->hasColumn('order_rate')) {
            $table->addColumn('order_rate', 'integer', ['default' => 0, 'comment' => '订单费率', 'limit' => 11, 'null' => false, 'after' => 'rebate_ratio'])
                ->update();
        }
    }

    /**
     * 新增多类型用户费率表
     */
    private function createUserRate(): void
    {
        if (!$this->hasTable('user_rate')){
            $table = $this->table('user_rate', [
                'id' => false,
                'comment' => '多类型用户费率表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('rate_type','string', ['limit' => 30, 'default' => 'order','comment' => '费率类型', 'null' => false])
                ->addColumn('rate','integer', ['limit' => 11, 'default' => 0, 'comment' => '费率', 'null' => false])
                ->addColumn('channel_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '通道ID', 'null' => false])
                ->addColumn('action_type','string', ['limit' => 30, 'default' => 'admin','comment' => '操作人类型', 'null' => false])
                ->addColumn('action_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '操作人ID', 'null' => false])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('remark', 'string', ['limit' => 255, 'comment' => '备注', 'null' => true, 'default' => ''])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 新增代理套餐与进件通道的费率表
     */
    private function createAgentSubscribeEntryRate(): void
    {
        if (!$this->hasTable('agent_subscribe_entry_rate')){
            $table = $this->table('agent_subscribe_entry_rate', [
                'id' => false,
                'comment' => '代理套餐与进件通道的费率表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('subscribe_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '代理套餐ID', 'null' => false])
                ->addColumn('ec_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '进件通道ID', 'null' => false])
                ->addColumn('rate','integer', ['limit' => 11, 'default' => 0, 'comment' => '费率', 'null' => false])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('remark', 'string', ['limit' => 255, 'comment' => '备注', 'null' => true, 'default' => ''])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 新增用户套餐与进件通道的费率表
     */
    private function userSubscribeEntryRate(): void
    {
        if (!$this->hasTable('user_subscribe_entry_rate')){
            $table = $this->table('user_subscribe_entry_rate', [
                'id' => false,
                'comment' => '用户套餐与进件通道的费率表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('subscribe_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户套餐ID', 'null' => false])
                ->addColumn('ec_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '进件通道ID', 'null' => false])
                ->addColumn('rate','integer', ['limit' => 11, 'default' => 0, 'comment' => '费率', 'null' => false])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('remark', 'string', ['limit' => 255, 'comment' => '备注', 'null' => true, 'default' => ''])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();

        }
    }

    /**
     * 前台菜单更新
     * @return void
     */
    private function userRule(): void
    {
        UserRule::where('name','subAgent')->delete();
    }

    /**
     * 后台菜单更新
     * @return void
     * @throws Exception
     */
    private function adminRule(): void
    {
        // 隐藏一些菜单
        $menu_name = [
            ['name'=>'user/subscribeEntryRate'], // 会员套餐通道费率
            ['name'=>'agent/subscribeEntryRate'], // 代理套餐通道费率
            ['name'=>'agent/userEntryDiscount'], // 代理用户进件通道折扣
            ['name' => 'agent/subscribeEntryDiscount'] // 代理套餐进件通道折扣
        ];

        foreach ($menu_name as $item) {
            $menu = AdminRule::where('name',$item['name'])->find();
            if ($menu) {
                $menu->status = 0; // 先隐藏吧~~
                $menu->save();
            }
        }
    }
}
