<?php

namespace modules\mail;

use Throwable;
use app\common\library\Menu;
use app\admin\model\AdminRule;

class Mail
{

    /**
     * 安装
     * @throws Throwable
     */
    public function install(): void
    {
        $pMenu = AdminRule::where('name', 'mail')->value('id');
        if (!$pMenu) {
            $menu = [
                [
                    'type'      => 'menu_dir',
                    'title'     => '邮件发送管理',
                    'name'      => 'mail',
                    'path'      => 'mail',
                    'icon'      => 'el-icon-Message',
                    'menu_type' => 'tab',
                ]
            ];
            Menu::create($menu);
            $pMenu = AdminRule::where('name', 'mail')->value('id');
        }
        $menu = [
            [
                'type'      => 'menu',
                'title'     => '模板管理',
                'name'      => 'mail/template',
                'path'      => 'mail/template',
                'icon'      => 'el-icon-Document',
                'menu_type' => 'tab',
                'component' => '/src/views/backend/mail/template/index.vue',
                'keepalive' => '1',
                'pid'       => $pMenu ? $pMenu : 0,
                'children'  => [
                    ['type' => 'button', 'title' => '查看', 'name' => 'mail/template/index'],
                    ['type' => 'button', 'title' => '添加', 'name' => 'mail/template/add'],
                    ['type' => 'button', 'title' => '编辑', 'name' => 'mail/template/edit'],
                    ['type' => 'button', 'title' => '删除', 'name' => 'mail/template/del'],
                ],
            ],
        ];
        Menu::create($menu);
    }

    /**
     * 卸载
     * @throws Throwable
     */
    public function uninstall(): void
    {
        Menu::delete('mail', true);
    }

    /**
     * 启用
     * @throws Throwable
     */
    public function enable(): void
    {
        Menu::enable('mail');
    }

    /**
     * 禁用
     * @throws Throwable
     */
    public function disable(): void
    {
        Menu::disable('mail');
    }
}