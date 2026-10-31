<?php

use think\facade\Db;
use think\migration\Migrator;

class UpdateV085 extends Migrator
{
    public function change()
    {
        $this->order();
        $this->createUserLogTable();
        $this->config();
        $this->adminRule();
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.5']);
    }

    private function order(): void
    {
        // 新增是否测试订单字段
        $order = $this->table('order');
        if (!$order->hasColumn('is_test')) {
            $order->addColumn('is_test', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否测试订单', 'after' => 'extend_param'])->update();
        }
    }

    private function createUserLogTable(): void
    {
        if (!$this->hasTable('user_log')) {
            $table = $this->table('user_log', [
                'id' => false,
                'comment' => '用户日志',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID'])
                ->addColumn('url', 'string', ['limit' => 500, 'default' => '', 'comment' => '操作URL'])
                ->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '操作标题'])
                ->addColumn('user_agent', 'string', ['limit' => 500, 'default' => '', 'comment' => '用户代理User-Agent'])
                ->addColumn('ip', 'string', ['limit' => 50, 'default' => '', 'comment' => 'IP'])
                ->addColumn('data', 'text', ['comment' => '操作数据'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }


    private function config(): void
    {
        Db::name('config')->where('name', 'config_group')->update(['value' => '[{"key":"basics","value":"\u57fa\u7840\u914d\u7f6e"},{"key":"login_reg","value":"\u767b\u5f55|\u6ce8\u518c"},{"key":"member","value":"\u4f1a\u5458\u914d\u7f6e"},{"key":"security","value":"\u5b89\u5168\u914d\u7f6e"},{"key":"payment","value":"\u652f\u4ed8\u914d\u7f6e"},{"key":"extend","value":"\u529f\u80fd\u589e\u5f3a"}]']);

        Db::name('config')->where('name', 'aliyun_ocr')->update([
            'name' => 'ocr_config',
            'title' => '图片识别OCR配置',
            'type' => 'ocrConfig',
            'value' => '{"type":"close"}'
        ]);
        // 新增最低订单费率
        $exist_min_rate_amount = Db::name('config')->where('name', 'min_rate_amount')->find();
        if (!$exist_min_rate_amount) {
            Db::name('config')->insert([
                'name' => 'min_rate_amount',
                'group' => 'payment',
                'title' => '最低订单费率',
                'tip' => '',
                'type' => 'number',
                'value' => 0.01,
                'content' => "",
                'rule' => '',
                'allow_del' => 0,
                'extend' => '{"baInputExtend":{"min":0,"step":0.01}}',
                'weigh' => 0,
            ]);
        }

        // 新增扣除通道测试支付费率
        $exist_deduct_test_order_rate = Db::name('config')->where('name', 'deduct_test_order_rate')->find();
        if (!$exist_deduct_test_order_rate) {
            Db::name('config')->insert([
                'name' => 'deduct_test_order_rate',
                'group' => 'payment',
                'title' => '扣除通道测试支付费率',
                'tip' => '',
                'type' => 'radio',
                'value' => 'close',
                'content' => '{"close":"\u5173\u95ed","open":"\u5f00\u542f"}',
                'extend' => '{"baInputExtend":{"border":true}}',
                'allow_del' => 0,
                'weigh' => 0
            ]);
        }

    }

    private function adminRule(): void
    {
        $exist_admin_rule = Db::name('admin_rule')->where('name', 'userLog')->find();
        if (!$exist_admin_rule) {
            $row = [
                ['id' => 249, 'pid' => 21, 'type' => 'menu', 'title' => '会员日志', 'name' => 'userLog', 'path' => 'userLog', 'icon' => 'fa fa-calendar-o', 'menu_type' => 'tab', 'url' => '', 'component' => '/src/views/backend/userLog/index.vue', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 250, 'pid' => 249, 'type' => 'button', 'title' => '查看', 'name' => 'userLog/index', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 251, 'pid' => 249, 'type' => 'button', 'title' => '添加', 'name' => 'userLog/add', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 252, 'pid' => 249, 'type' => 'button', 'title' => '编辑', 'name' => 'userLog/edit', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 253, 'pid' => 249, 'type' => 'button', 'title' => '删除', 'name' => 'userLog/del', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 254, 'pid' => 249, 'type' => 'button', 'title' => '快速排序', 'name' => 'userLog/sortable', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
            ];
            Db::name('admin_rule')->insertAll($row);
        }
    }
}
