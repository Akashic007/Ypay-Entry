<?php

use app\admin\model\Config;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV087 extends Migrator
{
    public function change()
    {
        $this->adminRule();
        $this->config();
        $this->entryChannel();
        $this->createComplaint();
        $this->user();
        Db::name('config')->where('name', 'version')->update(['value' => 'V0.8.7']);
    }

    private function adminRule(): void
    {
        // 后台菜单表 - 新增投诉管理菜单
        $exist_admin_rule = Db::name('admin_rule')->where('name', 'complaint')->find();
        if (!$exist_admin_rule) {
            $row = [
                ['id' => 255, 'pid' => 195, 'type' => 'menu', 'title' => '投诉管理', 'name' => 'complaint', 'path' => 'complaint', 'icon' => 'el-icon-Failed', 'menu_type' => 'tab', 'url' => '', 'component' => '/src/views/backend/complaint/index.vue', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 256, 'pid' => 255, 'type' => 'button', 'title' => '查看', 'name' => 'complaint/index', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 257, 'pid' => 255, 'type' => 'button', 'title' => '添加', 'name' => 'complaint/add', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 258, 'pid' => 255, 'type' => 'button', 'title' => '编辑', 'name' => 'complaint/edit', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 259, 'pid' => 255, 'type' => 'button', 'title' => '删除', 'name' => 'complaint/del', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
                ['id' => 260, 'pid' => 255, 'type' => 'button', 'title' => '快速排序', 'name' => 'complaint/sortable', 'path' => '', 'icon' => '', 'menu_type' => null, 'url' => '', 'component' => '', 'keepalive' => 0, 'extend' => 'none', 'remark' => '', 'weigh' => 0, 'status' => 1, 'create_time' => time(), 'update_time' => time()],
            ];
            Db::name('admin_rule')->insertAll($row);
        }
    }

    private function config(): void
    {
        // 配置表 - 新增用户最高欠费金额
        $max_arrearage_money = Db::name('config')->where('name', 'max_arrearage_money')->find();
        if (!$max_arrearage_money) {
            Db::name('config')->insert([
                'name' => 'max_arrearage_money',
                'group' => 'member',
                'title' => '最高欠费金额',
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
        // 配置迁移，如果有微信人脸核身的配置，则改成 腾讯云 - 微信人脸核身 ：tencent_wx_face_verify 原来是没有tencent的
        $config = Config::where('name', 'real_name_config')->find();
        $data = $config['value'];
        if ($data['type'] == 'wx_face_verify') {
            $data['type'] = 'tencent_wx_face_verify';
            $config->value = $data;
            $config->save();
        }
    }

    private function entryChannel(): void
    {
        // 进件通道表 - 新增mch_id 服务商商户号
        $table = $this->table('entry_channel');
        if (!$table->hasColumn('mch_id')) {
            $table->addColumn('mch_id', 'string', ['limit' => 100, 'default' => 0, 'comment' => '服务商商户号', 'after' => 'platform_code'])->update();
        }
    }

    private function createComplaint(): void
    {
        // 新增投诉管理表
        if (!$this->hasTable('complaint')) {
            $table = $this->table('complaint', [
                'id' => false,
                'comment' => '投诉管理',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('platform', 'string', ['limit' => 100, 'default' => '', 'comment' => '平台代码', 'null' => false])
                ->addColumn('complaint_no', 'string', ['limit' => 255, 'default' => '', 'comment' => '投诉单号', 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('merchant_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '商户ID|子商户ID', 'null' => false])
                ->addColumn('ec_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '进件通道ID', 'null' => false])
                ->addColumn('sp_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '服务商ID', 'null' => false])
                ->addColumn('is_refund', 'integer', ['limit' => 2, 'default' => 0, 'comment' => '是否申请退款,0=否,1=是'])
                ->addColumn('complaint_title', 'string', ['limit' => 255, 'default' => '', 'comment' => '投诉标题'])
                ->addColumn('complaint_detail', 'text', ['comment' => '投诉内容'])
                ->addColumn('order_no', 'string', ['limit' => 255, 'default' => '', 'comment' => '订单号'])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '投诉状态'])
                ->addColumn('wait_reply', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '是否待回复'])
                ->addColumn('res_content', 'text', ['comment' => '响应内容'])
                ->addColumn('create_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('update_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addColumn('complaint_time', 'biginteger', ['limit' => 16, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '投诉时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    private function user(): void
    {
        // 用户金额改成有符号
        $table = $this->table('user');
        if ($table->hasColumn('money')) {
            $table->changeColumn('money', 'integer', ['limit' => 11, 'signed' => true, 'comment' => '用户金额', 'null' => false, 'default' => 0])
                ->update();
        }
    }
}
