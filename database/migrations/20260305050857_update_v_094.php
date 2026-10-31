<?php

use app\admin\model\AdminRule;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV094 extends Migrator
{
    /**
     * 更新版本
     * @return void
     * @throws Exception
     */
    public function change():void
    {
        $this->createUserSubscribeGroup();
        $this->createUserSubscribeGroupChannel();
        $this->userSubscribe();
        $this->merchantEntry();
        $this->adminRule();
        Db::startTrans();
        try {
            Db::name('config')->where('name', 'version')->update(['value' => 'V0.9.4']);
            Db::commit();
        }catch (Exception $e){
            Db::rollback();
        }
    }

    /**
     * 新增会员套餐分组表
     */
    public function createUserSubscribeGroup():void
    {

        if (!$this->hasTable('user_subscribe_group')){
            $table = $this->table('user_subscribe_group', [
                'id' => false,
                'comment' => '会员套餐分组',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '套餐名称'])
                ->addColumn('desc', 'string', ['limit' => 255, 'default' => '', 'comment' => '描述'])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态'])
                ->addColumn('weigh', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '权重'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }else{
            $table = $this->table('user_subscribe_group');
            if ($table->hasColumn('weight')){
                $table->renameColumn('weight', 'weigh')->update();
            }
        }
    }


    /**
     * 新增支付通道类型与会员套餐分组关联表
     */
    public function createUserSubscribeGroupChannel():void
    {
        if (!$this->hasTable('user_subscribe_group_channel')){
            $table = $this->table('user_subscribe_group_channel', [
                'id' => false,
                'comment' => '会员套餐分组与支付通道类型关联表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('group_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '套餐分组ID', 'null' => false])
                ->addColumn('channel_type', 'string', ['limit' => 100, 'default' => '', 'null' => false, 'comment' => '通道类型'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 会员套餐表新增group_id字段
     */
    public function userSubscribe():void
    {

        $table = $this->table('user_subscribe');
        if (!$table->hasColumn('group_id')) {
            $table->addColumn('group_id', 'integer', ['limit' => 11, 'comment' => '套餐分组ID', 'null' => true, 'after' => 'id'])
                ->update();
        }
    }

    /**
     * 进件列表 audit_result字段设置为可为空
     */
    public function merchantEntry():void
    {
        $table = $this->table('merchant_entry');
        if ($table->hasColumn('audit_result')) {
            $table->changeColumn('audit_result', 'text', ['null' => true,  'comment' => '审核结果'])
                ->update();
        }
    }


    /**
     * 后台菜单
     * @return void
     * @throws Exception
     */
    public function adminRule():void
    {
        $pMenu = AdminRule::where('name', 'store')->find();
        $userSubscribeGroup = AdminRule::where('name', 'user/subscribeGroup')->find();

        if (!$userSubscribeGroup){
            $userSubscribeGroup = AdminRule::create([
                'pid' => $pMenu->id,
                'type' => 'menu',
                'title' => '会员套餐分组',
                'name' => 'user/subscribeGroup',
                'path' => 'user/subscribeGroup',
                'icon' => 'el-icon-CopyDocument',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/user/subscribeGroup/index.vue',
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
                    'pid' => $userSubscribeGroup->id,
                    'type' => 'button',
                    'title' => $btn['title'],
                    'name' => $userSubscribeGroup->name . '/' . $btn['name'],
                    'keepalive' => 0,
                ];
            }
            $adminRule = new AdminRule();
            $adminRule->saveAll($btnList);
        }
    }
}
