<?php

use app\admin\model\AdminRule;
use app\admin\model\channel\Type;
use app\admin\model\EntryChannel;
use app\admin\model\merchant\EntryMerchant;
use app\admin\model\merchant\Merchant;
use app\admin\model\merchant\MerchantEntry;
use app\admin\model\ServiceProvider;
use app\admin\model\supply\Upstream;
use app\admin\model\UserRule;
use app\common\service\entry\EntryRequest;
use app\common\service\EntryService;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV100Beta extends Migrator
{


    /**
     * @return void
     * @throws Exception
     */
    public function change(): void
    {
        $current = '准备开始';
        try {
            $current = '更新进件通道表';
            $this->updateEntryChannel();

            $current = '创建用户钱包';
            $this->createUserWallet();

            $current = '新增支付宝签约通道';
            $this->addAlipaySignChannel();

            $current = '创建进件商户关联表';
            $this->createEntryMerchant();

            $current = '创建插件管理表';
            $this->createPlugin();

            $current = '新增后台菜单';
            $this->addAdminMenu();

            $current = '新增配置项';
            $this->newConfig();

            $current = '更新进件通道数据';
            $this->updateEntryChannelData();

            $current = '更新商户进件数据';
            $this->updateMerchantEntryData();

            $current = '同步更新商户进件关联表数据';
            $this->updateEntryMerchantData();

            $current = '同步更新用户钱包数据';
            $this->updateWalletData();

            $current = '更新前台菜单表';
            $this->updateUserRule();
        } catch (\Exception $e) {
            throw new \ba\Exception('数据库迁移失败,' . $current . '时出问题：' . $e->getMessage());
        }

        Db::name('config')->where('name', 'version')->update(['value' => 'V1.0.0']);
        Db::commit();
    }

    /**
     * 进件通道表更新
     * @return void
     */
    public function updateEntryChannel(): void
    {
        $table = $this->table('entry_channel');
        if (!$table->hasColumn('name')) {
            $table->addColumn('name', 'string', ['limit' => 100, 'comment' => '通道名称', 'null' => false, 'default' => '', 'after' => 'sp_id']);
        }
        if (!$table->hasColumn('source')) {
            $table->addColumn('source', 'string', ['limit' => 100, 'default' => 'local', 'null' => false, 'comment' => '通道来源', 'after' => 'platform_code']);
        }
        $table->update();
    }


    /**
     * 新增用户钱包相关表
     */
    public function createUserWallet(): void
    {
        // 新增用户钱包表
        if (!$this->hasTable('user_wallet')) {
            $table = $this->table('user_wallet', [
                'id' => false,
                'comment' => '用户钱包',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '金额（分）', 'null' => false])
                ->addColumn('withdrawal_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '可提现金额（分）', 'null' => false])
                ->addColumn('freeze_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '冻结金额（分）', 'null' => false])
                ->addColumn('total_income', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '累计收入金额（分）', 'null' => false])
                ->addColumn('total_withdrawal', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '累计提现金额（分）', 'null' => false])
                ->addColumn('pay_account', 'text', ['comment' => '打款账户设置', 'null' => true])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }

        // 新增用户钱包余额日志表
        if (!$this->hasTable('user_wallet_money_log')) {
            $table = $this->table('user_wallet_money_log', [
                'id' => false,
                'comment' => '钱包余额日志',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变更金额（分）', 'null' => false])
                ->addColumn('before_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变动前金额（分）', 'null' => false])
                ->addColumn('before_withdrawal_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变动前提现金额（分）', 'null' => false])
                ->addColumn('before_freeze_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变动前冻结金额（分）', 'null' => false])
                ->addColumn('after_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变动后金额（分）', 'null' => false])
                ->addColumn('after_withdrawal_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变动后提现金额（分）', 'null' => false])
                ->addColumn('after_freeze_money', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '变动后冻结金额（分）', 'null' => false])
                ->addColumn('type', 'integer', ['limit' => 2, 'default' => 0, 'comment' => '类型', 'null' => false])
                ->addColumn('source', 'string', ['limit' => 50, 'default' => '', 'comment' => '来源标识', 'null' => false])
                ->addColumn('remark', 'string', ['limit' => 255, 'default' => '', 'comment' => '备注', 'null' => true])
                ->addColumn('content', 'text', ['comment' => '相关内容', 'null' => true])
                ->addColumn('create_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }

        // 新增用户提现记录表
        if (!$this->hasTable('user_withdrawal_apply_log')) {
            $table = $this->table('user_withdrawal_apply_log', [
                'id' => false,
                'comment' => '用户提现记录',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '用户ID', 'null' => false])
                ->addColumn('withdraw_no', 'string', ['limit' => 100, 'default' => '', 'comment' => '提现申请单号', 'null' => false])
                ->addColumn('apply_amount', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '申请提现金额（分）', 'null' => false])
                ->addColumn('final_amount', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '最终提现金额（分）', 'null' => false])
                ->addColumn('fee', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '手续费（分）', 'null' => false])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '状态', 'null' => false])
                ->addColumn('reject_msg', 'string', ['limit' => 255, 'default' => '', 'comment' => '拒绝原因', 'null' => true])
                ->addColumn('create_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '创建时间'])
                ->addColumn('pay_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '打款时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }

    /**
     * 新增支付宝签约通道
     */
    public function addAlipaySignChannel(): void
    {
        $channel = Type::where('type', 'alipay_sign')->find();
        if (empty($channel)) {
            Type::create([
                'pay_type' => 'alipay',
                'type' => 'alipay_sign',
                'name' => '支付宝签约',
                'weigh' => 0,
                'status' => 1
            ]);
        }
    }

    /**
     * 新增商户与进件关联表
     */
    public function createEntryMerchant(): void
    {
        if (!$this->hasTable('entry_merchant')) {
            $table = $this->table('entry_merchant', [
                'id' => false,
                'comment' => '商户与进件记录关联表',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('me_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '进件记录ID', 'null' => false])
                ->addColumn('mch_id', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '商户ID', 'null' => false])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }


    /**
     * 新增插件管理表
     * @return void
     */
    public function createPlugin(): void
    {
        if (!$this->hasTable('plugin')) {
            $table = $this->table('plugin', [
                'id' => false,
                'comment' => '插件管理',
                'row_format' => 'DYNAMIC',
                'primary_key' => 'id',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table->addColumn('id', 'integer', ['comment' => 'ID', 'signed' => false, 'identity' => true, 'null' => false])
                ->addColumn('name', 'string', ['limit' => 100, 'default' => '', 'comment' => '插件名称', 'null' => false])
                ->addColumn('code', 'string', ['limit' => 100, 'default' => '', 'comment' => '插件标识码', 'null' => false])
                ->addColumn('title', 'string', ['limit' => 100, 'default' => '', 'comment' => '插件标题', 'null' => false])
                ->addColumn('version', 'string', ['limit' => 100, 'default' => '', 'comment' => '插件版本', 'null' => false])
                ->addColumn('latest_version', 'string', ['limit' => 100, 'default' => '', 'comment' => '最新版本', 'null' => false])
                ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '状态：0禁用 1启用', 'null' => false])
                ->addColumn('install_status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '安装状态：0未安装 1已安装', 'null' => false])
                ->addColumn('auth_status', 'integer', ['limit' => 1, 'default' => 0, 'comment' => '授权状态：0未授权 1已授权', 'null' => false])
                ->addColumn('config', 'text', ['comment' => '配置信息', 'null' => true])
                ->addColumn('auth_info', 'text', ['comment' => '授权信息', 'null' => true])
                ->addColumn('path_info', 'text', ['comment' => '文件路径信息', 'null' => true])
                ->addColumn('install_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '安装时间'])
                ->addColumn('update_time', 'biginteger', ['limit' => 20, 'signed' => false, 'null' => true, 'default' => null, 'comment' => '更新时间'])
                ->addIndex(['id'], ['unique' => true])
                ->create();
        }
    }


    /**
     * 新增配置
     * @return void
     * @throws Exception
     */
    public function newConfig(): void
    {
        // 新增配置项：下游对接审核开关
        $is_down_review = Db::name('config')->where('name', 'is_down_review')->find();
        if (!$is_down_review) {
            Db::name('config')->insert([
                'name' => 'is_down_review',
                'group' => 'member',
                'title' => '下游对接审核',
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
     * 进件通道数据
     * 这里主要是本地通道
     */
    public function updateEntryChannelData(): void
    {
        /**
         * 1、进件通道名称默认设置
         * 2、进件通道来源默认设置
         */
        $entryChannel = EntryChannel::where('id', '>', 0)->select();
        foreach ($entryChannel as $channel) {
            $channel->name = '本地进件通道' . $channel->id;
            $channel->source = 'local';
            $channel->save();
        }
    }


    /**
     * 更新进件商户数据
     * 1、上游进件记录、进件通道、进件服务商相关的数据需要同步下来
     *  就是有上游进件记录的，将对应的上游通道添加到本地数据库，然后将进件记录的通道ID改成新增的通道的ID
     *  如果当前系统没有服务商，则新增一个默认的服务商
     * 2、进件记录与商户的关系变成多对一，所以要新增一些已有的关联数据
     */
    public function updateMerchantEntryData(): void
    {
        $me = MerchantEntry::where('supply', 'upstream')->select();

        // 所有上有进件通道都在这里

        // 上游信息和上游通道，放在这里是为了同个上游不需要再次请求
        $up = null;
        $upChannelList = [];

        foreach ($me as $entry) {


            // 先判断本地是不是已经有通道且是上游的了
            if ($this->table('entry_channel')->hasColumn('source')) {
                $existChannel = EntryChannel::where('id', $entry->ec_id)->where('source', 'up')->find();
                if ($existChannel) continue;
            }

            // 上游存在，获取通道。判断这个通道是否在本地数据库有存在
            try {
                // 查上游
                if (empty($up) || $up->id != $entry->up_id) {
                    $up = Upstream::find($entry->up_id);
                    if (empty($up)) {
                        $entry->delete();
                        throw new Exception('上游不存在');
                    }

                    // 查上游通道
                    $er = new EntryRequest();
                    $service = new EntryService($er);
                    $upChannelList = $service->getUpChannelList($up);
                }
            } catch (Exception $e) {
                continue;
            }

            // 上游通道看看在不在
            $upGoalChannel = null; // 上游目标通道（也就是需要同步到本地的目标通道）
            foreach ($upChannelList as $channel) {
                if ($channel['id'] == $entry->ec_id) $upGoalChannel = $channel;
            }

            // 上游通道不存在的话，删掉进件记录
            if (empty($upGoalChannel)) {
                $entry->delete();
                continue;
            }

            // 同步上游通道到本地
            $localChannel = EntryChannel::where('source', 'up')
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(config, '$.up_channel_id')) = ?",
                    [(string)$upGoalChannel['id']]
                )
                ->find();

            if (empty($localChannel)) {
                // 看看有没有服务商
                $sp = ServiceProvider::where('status', 1)->find();
                if (empty($sp)) {
                    // 先创建服务商
                    $sp = ServiceProvider::create([
                        'name' => '系统默认服务商',
                        'real_name' => '你的名字',
                        'phone' => '13888888888',
                        'email' => 'entry@qq.com',
                        'logo' => '',
                        'status' => 1,
                        'remark' => '用来存放上游进件通道用的'
                    ]);
                }

                $localChannel = EntryChannel::create([
                    'sp_id' => $sp->id,
                    'name' => $upGoalChannel['name'] ?? $upGoalChannel['channel_name'] ?? ('默认进件通道' . $upGoalChannel['id']),
                    'platform_code' => $upGoalChannel['platform_code'] ?? $entry->platform_code,
                    'source' => 'up',
                    'price' => $upGoalChannel['price'] ?? 0,
                    'order_rate' => $upGoalChannel['order_rate'] ?? 0,
                    'status' => 1,
                    'config' => [
                        'up_id' => $up->id,
                        'up_channel_id' => $upGoalChannel['id'],
                        'service_name' => $upGoalChannel['sp_name'],
                        'sync_time' => time(),
                    ]
                ]);
            }

            // 改进件通道ID(ec_id字段）
            $entry->ec_id = $localChannel->id;
            $entry->save();
        }
    }

    /**
     * 进件记录和商形成了多对一的模式，所以需要新增一下关联数据
     */
    public function updateEntryMerchantData(): void
    {
        $mchList = Merchant::where('id', '>', 0)->select();
        foreach ($mchList as $mch) {
            $me = MerchantEntry::find($mch->entry_id);
            if (empty($me)) {
                $mch->delete();
                continue;
            }

            $em = EntryMerchant::where('me_id', $me->id)->where('mch_id', $mch->id)->find();
            if (empty($em)) {
                EntryMerchant::create([
                    'me_id' => $me->id,
                    'mch_id' => $mch->id
                ]);
            }

            $mch->ec_id = $me->ec_id;
            $mch->sp_id = $me->sp_id;
            $mch->save();
        }

        //  把entry_id字段定义为可空
        $this->table('merchant')
            ->changeColumn('entry_id', 'integer', ['limit' => 11, 'null' => true, 'comment' => '进件记录ID'])
            ->update();
    }


    /**
     * 钱包数据迁移
     * 目前只有代理有钱包数据
     *
     * 迁移内容：
     * 1. 代理表 withdrawal_money / pay_account → user_wallet
     * 2. agent_withdrawal_money_log → user_wallet_money_log
     * 3. agent_withdrawal_apply_log → user_withdrawal_apply_log
     *
     * 类型映射：
     *   旧 type=1（返佣）  → 新 type=3（TYPE_REBATE）
     *   旧 type=2（提现扣费）→ 新 type=6（TYPE_WITHDRAW）
     *
     * 状态映射（提现申请）：
     *   旧 0=待审核 → 新 0=待审核
     *   旧 1=已打款 → 新 3=打款完成
     *   旧 2=已驳回 → 新 2=已驳回
     */
    public function updateWalletData(): void
    {
        $typeMap = [1 => 3, 2 => 6];
        $statusMap = [0 => 0, 1 => 3, 2 => 2];

        $agents = Db::name('agent')->where('id', '>', 0)->select()->toArray();

        foreach ($agents as $agent) {
            $userId = (int)$agent['user_id'];
            $agentId = (int)$agent['id'];

            // ── 1. 计算 total_income / total_withdrawal ──
            $totalIncome = (int)Db::name('agent_withdrawal_money_log')
                ->where('agent_id', $agentId)
                ->where('type', 1)
                ->sum('money');

            $totalWithdrawal = (int)Db::name('agent_withdrawal_money_log')
                ->where('agent_id', $agentId)
                ->where('type', 2)
                ->sum('money');
            $totalWithdrawal = abs($totalWithdrawal);

            // 待审核的提现申请金额 → freeze_money
            $freezeMoney = (int)Db::name('agent_withdrawal_apply_log')
                ->where('agent_id', $agentId)
                ->where('status', 0)
                ->sum('apply_amount');

            $withdrawalMoney = (int)$agent['withdrawal_money'];
            $money = $withdrawalMoney + $freezeMoney;

            // ── 2. 创建钱包记录 ──
            $walletExists = Db::name('user_wallet')->where('user_id', $userId)->find();
            if (!$walletExists) {
                Db::name('user_wallet')->insert([
                    'user_id' => $userId,
                    'money' => $money,
                    'withdrawal_money' => $withdrawalMoney,
                    'freeze_money' => $freezeMoney,
                    'total_income' => $totalIncome,
                    'total_withdrawal' => $totalWithdrawal,
                    'pay_account' => $agent['pay_account'] ?? null,
                ]);
            }

            // ── 3. 迁移代理余额日志 → 钱包余额日志 ──
            $moneyLogs = Db::name('agent_withdrawal_money_log')
                ->where('agent_id', $agentId)
                ->order('id', 'asc')
                ->select()->toArray();

            $walletMoneyLogs = [];
            foreach ($moneyLogs as $log) {
                $newType = $typeMap[(int)$log['type']] ?? 0;
                $logMoney = (int)$log['money'];
                $logBefore = (int)$log['before'];
                $logAfter = (int)$log['after'];

                $source = $newType === 3 ? 'agent_rebate' : ($newType === 6 ? 'withdraw' : '');
                $walletMoneyLogs[] = [
                    'user_id' => $userId,
                    'money' => $logMoney,
                    'before_money' => $logBefore,
                    'before_withdrawal_money' => $logBefore,
                    'before_freeze_money' => 0,
                    'after_money' => $logAfter,
                    'after_withdrawal_money' => $logAfter,
                    'after_freeze_money' => 0,
                    'type' => $newType,
                    'source' => $source,
                    'remark' => $log['memo'] ?? '',
                    'content' => $log['content'] ?? null,
                    'create_time' => $log['create_time'],
                ];
            }

            if (!empty($walletMoneyLogs)) {
                Db::name('user_wallet_money_log')->insertAll($walletMoneyLogs);
            }

            // ── 4. 迁移代理提现申请日志 → 用户提现记录 ──
            $applyLogs = Db::name('agent_withdrawal_apply_log')
                ->where('agent_id', $agentId)
                ->order('id', 'asc')
                ->select()->toArray();

            $withdrawalApplyLogs = [];
            foreach ($applyLogs as $apply) {
                $oldStatus = (int)$apply['status'];
                $newStatus = $statusMap[$oldStatus] ?? $oldStatus;

                $withdrawalApplyLogs[] = [
                    'user_id' => $userId,
                    'withdraw_no' => $apply['withdraw_no'] ?? '',
                    'apply_amount' => (int)$apply['apply_amount'],
                    'fee' => (int)$apply['fee_amount'],
                    'final_amount' => (int)$apply['final_amount'],
                    'status' => $newStatus,
                    'reject_msg' => $apply['rejected'] ?? '',
                    'create_time' => $apply['create_time'],
                    'pay_time' => $apply['pay_time'],
                ];
            }

            if (!empty($withdrawalApplyLogs)) {
                Db::name('user_withdrawal_apply_log')->insertAll($withdrawalApplyLogs);
            }
        }

        // 删除代理申请提现日志表
        $this->table('agent_withdrawal_apply_log')->drop();

        // 删除代理可提现余额日志表
        $this->table('agent_withdrawal_money_log')->drop();
    }


    /**
     * 后台菜单表新增相关菜单
     */
    public function addAdminMenu(): void
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
                'title' => '会员管理',
                'name' => 'user',
                'icon' => 'fa fa-drivers-license',
                // 新增用户钱包管理相关菜单 一共3个
                'sub_menus' => [
                    ['title' => '用户钱包管理', 'name' => 'user/wallet', 'icon' => 'fa fa-credit-card', 'component' => '/src/views/backend/user/wallet/index.vue'],
                    ['title' => '钱包余额日志', 'name' => 'user/walletMoneyLog', 'icon' => 'el-icon-CreditCard', 'component' => '/src/views/backend/user/walletMoneyLog/index.vue'],
                    ['title' => '提现申请日志', 'name' => 'user/withdrawalApplyLog', 'icon' => 'el-icon-List', 'component' => '/src/views/backend/user/withdrawalApplyLog/index.vue'],
                ]
            ],
            [
                'title' => '进件管理',
                'name' => 'merchant',
                'icon' => 'el-icon-Avatar',
                'sub_menus' => [
                    ['title' => '进件通道管理', 'name' => 'entry/channel', 'icon' => 'fa fa-navicon', 'component' => '/src/views/backend/entry/channel/index.vue']
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

                // 进件通道菜单配置
                if ($menu->name == 'entry/channel') {
                    $entryChannelConfig = AdminRule::where('name', 'entry/channel/config/:id')->find();
                    // 新增一个进件通道配置路由
                    if (empty($entryChannelConfig)) {
                        AdminRule::create([
                            'pid' => $menu->id,
                            'type' => 'menu',
                            'title' => '进件通道配置',
                            'name' => 'entry/channel/config/:id',
                            'path' => 'entry/channel/config/:id',
                            'icon' => 'fa fa-gear',
                            'menu_type' => 'tab',
                            'component' => '/src/views/backend/entry/channel/config.vue',
                            'keepalive' => 1,
                            'extend' => 'add_rules_only'
                        ]);
                    }
                }


            }
        }

        // 服务商管理的下级菜单 - 平台配置那一块菜单删掉
        Db::name('admin_rule')->where('name', 'serviceProvider/config')->delete();
        Db::name('admin_rule')->where('name', 'serviceProvider/config/:id')->delete();

        // 删除代理余额相关的菜单
        $agentMoneyMenus = [
            'agent/withdrawalMoneyLog', 'agent/withdrawalApplyLog'
        ];
        foreach ($agentMoneyMenus as $agentMoneyMenu) {
            $menu = AdminRule::where('name', $agentMoneyMenu)->find();
            if (!empty($menu)) {
                AdminRule::where('pid', $menu->id)->delete(); // 先删除子菜单
                $menu->delete();
            }

        }

        // 新增插件管理菜单
        $pluginMenu = AdminRule::where('name', 'plugin')->find();
        if (empty($pluginMenu)) {
            AdminRule::create([
                'pid' => 0,
                'type' => 'menu',
                'title' => '插件管理',
                'name' => 'plugin',
                'path' => 'plugin',
                'icon' => 'fa fa-puzzle-piece',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/plugin/index.vue',
                'keepalive' => 0,
                'extend' => 'none'
            ]);
        }
    }


    /**
     * 更新前台菜单，以 name 为准同步 title/path/icon/menu_type/component，不存在则创建
     * pid_name 字段仅用于新建时动态查找父级 id，不写入数据库
     * @return void
     * @throws Exception
     */
    public function updateUserRule(): void
    {
        // pid_name: 父菜单的 name 值，pid=0 表示顶级菜单
        $menus = [
            // 顶级菜单
            ['name' => 'place', 'pid_name' => null, 'type' => 'route', 'title' => '通道管理', 'path' => '/place', 'icon' => 'icon-shuju', 'menu_type' => 'tab', 'component' => '', 'weigh' => 11,],
            ['name' => 'dashboard', 'pid_name' => null, 'type' => 'route', 'title' => '总览', 'path' => '/dashboard', 'icon' => 'icon-shouye', 'menu_type' => 'tab', 'component' => '', 'weigh' => 21,],
            ['name' => 'Api', 'pid_name' => null, 'type' => 'route', 'title' => 'API密钥', 'path' => '/Api', 'icon' => 'icon-renzheng', 'menu_type' => 'tab', 'component' => '/indexapi/index', 'weigh' => 9,],
            ['name' => 'indexSet', 'pid_name' => null, 'type' => 'route', 'title' => '账户设置', 'path' => '/indexSet', 'icon' => 'icon-haoyourenzheng', 'menu_type' => 'tab', 'component' => '', 'weigh' => 20,],
//            ['name' => 'apply', 'pid_name' => null, 'type' => 'route', 'title' => '开通分站', 'path' => '/apply', 'icon' => 'icon-yue', 'menu_type' => 'tab', 'component' => '/Station/apply', 'weigh' => 8,],
            ['name' => 'paylist', 'pid_name' => null, 'type' => 'route', 'title' => '财务管理', 'path' => '/paylist', 'icon' => 'icon-yue', 'menu_type' => 'tab', 'component' => '', 'weigh' => 19,],
            ['name' => 'agent', 'pid_name' => null, 'type' => 'route', 'title' => '代理中心', 'path' => 'agent', 'icon' => 'icon-aichegujiabeifen6', 'menu_type' => 'tab', 'component' => '', 'weigh' => -9,],

            // 通道管理子菜单
            ['name' => 'channelList', 'pid_name' => 'place', 'type' => 'route', 'title' => '通道管理', 'path' => '/channelList', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/channel/channelList', 'weigh' => 1,],
            ['name' => 'channelSet', 'pid_name' => 'place', 'type' => 'route', 'title' => '通道配置', 'path' => '/channelSet', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/channel/channelSet', 'weigh' => 0,],
            ['name' => 'Applicationlist', 'pid_name' => 'place', 'type' => 'route', 'title' => '进件管理', 'path' => '/Applicationlist', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/Application/Applicationlist', 'weigh' => 2,],
            ['name' => 'applyMentSform', 'pid_name' => 'place', 'type' => 'route', 'title' => '商户进件', 'path' => '/applyMentSform', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/Application/applyMentSform', 'weigh' => 3,],
            ['name' => 'Merchantonboarding', 'pid_name' => 'place', 'type' => 'route', 'title' => '商户进件', 'path' => '/Merchantonboarding', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/Application/Merchantonboarding', 'weigh' => 29,],

            // 总览子菜单
            ['name' => 'Analytics', 'pid_name' => 'dashboard', 'type' => 'route', 'title' => '仪表盘', 'path' => '/analytics', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/dashboard/console', 'weigh' => 7,],

            // 财务管理子菜单
            ['name' => 'onlineRecharge', 'pid_name' => 'paylist', 'type' => 'route', 'title' => '在线充值', 'path' => '/onlineRecharge', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/onlineRecharge/index', 'weigh' => 16,],
            ['name' => 'pricing', 'pid_name' => 'paylist', 'type' => 'route', 'title' => '套餐购买', 'path' => '/pricing', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/pricing/index', 'weigh' => 15,],
            ['name' => 'olderList', 'pid_name' => 'paylist', 'type' => 'route', 'title' => '订单日志', 'path' => '/olderList', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/olderList/index', 'weigh' => 14,],
            ['name' => 'Wallet', 'pid_name' => 'paylist', 'type' => 'route', 'title' => '钱包管理', 'path' => '/Wallet', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/Wallet/index', 'weigh' => 45,],
            ['name' => 'Withdraw', 'pid_name' => 'paylist', 'type' => 'route', 'title' => '提现管理', 'path' => '/Withdraw', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/Withdraw/index', 'weigh' => 46,],

            // 账户设置子菜单
            ['name' => 'indexSetAccount', 'pid_name' => 'indexSet', 'type' => 'route', 'title' => '账户详情', 'path' => '/account', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/indexSet/index', 'weigh' => 28,],
            ['name' => 'indexSetSecurity', 'pid_name' => 'indexSet', 'type' => 'route', 'title' => '安全设置', 'path' => '/indexSetSecurity', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/indexSet/index', 'weigh' => 27,],
            ['name' => 'dellbolt', 'pid_name' => 'indexSet', 'type' => 'route', 'title' => '通知设置', 'path' => '/dellbolt', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/indexSet/index', 'weigh' => 26,],
//            ['name' => 'tablerloginlock', 'pid_name' => 'indexSet', 'type' => 'route', 'title' => '账号绑定', 'path' => '/tablerloginlock', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/indexSet/index', 'weigh' => 25,],
            ['name' => 'tableridcard', 'pid_name' => 'indexSet', 'type' => 'route', 'title' => '实名认证', 'path' => '/tableridcard', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/indexSet/index', 'weigh' => 24,],

            // 代理中心子菜单
            ['name' => 'subMerchant', 'pid_name' => 'agent', 'type' => 'route', 'title' => '下级商户管理', 'path' => '/subMerchant', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/sub/merchant', 'weigh' => 32,],
            ['name' => 'downAccount', 'pid_name' => 'agent', 'type' => 'route', 'title' => '下游支付通道', 'path' => '/downAccount', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/down/account', 'weigh' => 36,],
            ['name' => 'down', 'pid_name' => 'agent', 'type' => 'route', 'title' => '下游管理', 'path' => '/down', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/down', 'weigh' => 39,],
            ['name' => 'agentAccount', 'pid_name' => 'agent', 'type' => 'route', 'title' => '账户管理', 'path' => '/agentAccount', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/agent/Account', 'weigh' => 41,],
            ['name' => 'agentOrder', 'pid_name' => 'agent', 'type' => 'route', 'title' => '代理订单管理', 'path' => '/agentOrder', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/agent/order', 'weigh' => 42,],
            ['name' => 'agentEntry', 'pid_name' => 'agent', 'type' => 'route', 'title' => '代理进件管理', 'path' => '/agentEntry', 'icon' => 'icon-dian', 'menu_type' => 'tab', 'component' => '/agent/entry', 'weigh' => 43,],
        ];

        // 缓存已处理菜单的真实 id，供子菜单新建时使用
        $nameToId = [];

        foreach ($menus as $item) {
            $pidName = $item['pid_name'];
            $syncData = [
                'title' => $item['title'],
                'path' => $item['path'],
                'icon' => $item['icon'],
                'menu_type' => $item['menu_type'],
                'component' => $item['component'],
            ];

            $record = UserRule::where('name', $item['name'])->find();

            if ($record) {
//                $record->save($syncData);
                UserRule::where('name', $item['name'])->save($syncData);
                $nameToId[$item['name']] = $record->id;
            } else {
                // 新建时确定 pid
                $pid = 0;
                if ($pidName !== null) {
                    if (isset($nameToId[$pidName])) {
                        $pid = $nameToId[$pidName];
                    } else {
                        $parent = UserRule::where('name', $pidName)->find();
                        $pid = $parent ? $parent->id : 0;
                    }
                }

                $newRecord = UserRule::create(array_merge($syncData, [
                    'pid' => $pid,
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'weigh' => $item['weigh'],
                    'status' => 1,
                ]));
                $nameToId[$item['name']] = $newRecord->id;
            }
        }

        // 删除 代理-》佣金管理菜单
        UserRule::where('name', 'agentMoney')->delete();
        UserRule::where('name', 'tablerloginlock')->delete(); // 删除没啥用的子菜单
    }
}
