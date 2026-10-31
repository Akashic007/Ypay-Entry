<?php

use app\admin\model\channel\Account;
use app\admin\model\UserRule;
use think\facade\Db;
use think\migration\Migrator;

class UpdateV091Beta extends Migrator
{
    /**
     * @throws \ba\Exception
     */
    public function change(): void
    {
        Db::startTrans();
        try {
            $current = '修改支付通道表';
            $this->channelAccount();
            $current = '优化前台菜单表';
            $this->userRule();
            Db::name('config')->where('name', 'version')->update(['value' => 'V0.9.1-Beta']);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \ba\Exception('数据库迁移失败：' . $current . '时出问题');
        }
    }

    /**
     * 支付通道表
     */
    public function channelAccount(): void
    {
        // 微信特约商户通道需要把商户ID放到mch_id字段过来
        $channels = Account::where('channel_type', 'wxpay_official_special')
            ->select();

        if ($channels->count() > 0) {
            foreach ($channels as $channel) {
                $config = $channel->config;
                if ($config['mch_id']) {
                    $channel->mch_id = $config['mch_id'];
                    unset($config['mch_id']);
                    $channel->config = $config;
                    $channel->save();
                }
            }
        }
    }


    /**
     * 用户端菜单
     */
    public function userRule(): void
    {
        // 优化菜单排序
        $menus = [
            ['name' => 'dashboard', 'weight' => 100],
            ['name' => 'indexSet', 'weight' => 99],
            ['name' => 'paylist', 'weight' => 98],
            ['name' => 'place', 'weight' => 97],
            ['name' => 'Api', 'weight' => 96],
            ['name' => 'Station', 'weight' => 95],
            ['name' => 'apply', 'weight' => 94],
            ['name' => 'agent', 'weight' => 93],
        ];


        $userMenus = UserRule::where('menu_type', 'tab')
            ->select();
        foreach ($userMenus as $userMenu) {
            foreach ($menus as $menu) {
                if ($userMenu['name'] == $menu['name']) {
                    // 更新排序
                    $userMenu->weight = $menu['weight'];
                    $userMenu->save();
                }
            }
        }

    }
}
