<?php

// 还原自 Swoole Compiler 加密文件: Index.php
// 原始编译路径: /tmp/swoole_compiler_ceopi41db2dowm6laygi/69fc74e99c6c1/api/controller/Index.php
//
// [2026-09-13 opcode 全量对照还原] 商户端控制台：
//   index:    后台初始化(管理员信息/菜单/站点配置/终端类型) —— BuildAdmin 标准模式
//   init:     商户端初始化 —— 实名配置/站点信息/用户信息/菜单/安全设置/登录页图片
//   dashboard: 商户端数据面板 —— 订单统计(总/日/周/月/年) + 销售额统计 + 渠道统计 + 按渠道分期趋势
//   getMenus:  商户端菜单树 —— UserRule 查询 + ba\Tree 组装 + meta 隐藏规则

namespace app\api\controller;

use app\admin\model\Order;
use app\common\service\SiteService;
use think\facade\Event;

class Index extends \app\common\controller\Frontend
{
    protected array $noNeedLogin = ['index', 'init'];
    protected \app\common\service\OrderStatisticsService $statisticsService;
    protected \app\admin\model\Order $orderModel;

    public function initialize(): void
    {
        parent::initialize();
        $this->orderModel = new \app\admin\model\Order();
        $this->statisticsService = new \app\common\service\OrderStatisticsService($this->orderModel);
    }

    // [2026-10-02 opcode 全量对照还原] 首页渲染器。修复前还原体被误填为 admin\Index::index 的
    // 复制品（与 View|Redirect 返回类型矛盾，前端拿不到首页）。opcode 真值（行 42-81）：
    //   42-44: 无 install.lock 且 install/index.html 存在 → redirect('/install/')
    //   47-49: is_home 配置 != 'open' → redirect('/login')
    //   52-61: home-template 模板选择（配置模板 → default 回退 → view('common/error')）
    //   64-76: get_sys_config('','basics') 去 config_group；site 插件激活时合并 site_info
    //   79-81: view('home-template/{$template}/index', $data + ['is_login' => ...])
    public function index(): \think\response\View|\think\response\Redirect
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'install.lock')
            && is_file($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'index.html')) {
            return redirect('/install/');
        }

        $isHome = get_sys_config('is_home');
        if (empty($isHome) || $isHome != 'open') {
            return redirect('/login');
        }

        $templatePath = root_path('public') . 'views' . DIRECTORY_SEPARATOR . 'home-template' . DIRECTORY_SEPARATOR;
        $template     = get_sys_config('home_template');
        if (!is_dir($templatePath . $template)) {
            if (!is_dir($templatePath . 'default')) {
                return view('common/error', []);
            }
            $template = 'default';
        }

        $basicsConfig = get_sys_config('', 'basics');
        unset($basicsConfig['config_group']);
        $data = [...$basicsConfig];

        $siteService = new SiteService();
        $site        = $siteService->findsitebydomain(request()->domain());
        if (!empty($site) && $siteService->issiteactive($site)) {
            $data = array_merge($data, (array)$site['site_info']);
        }

        $data['is_login'] = $this->auth->isLogin() ? 1 : 0;
        return view('home-template/' . $template . '/index', $data);
    }

    public function init(): void
    {
        $realNameConfig = get_sys_config('real_name_config');

        // 站点信息（实名认证未关闭时才组装）
        // [opcode 还原 2026-09-22] 原始调用链（三密钥解密）：new SiteService → findsitebydomain(request->domain())
        // → isSiteActive → 取 site_info；插件未安装时 findsitebydomain 返回 null，site 分支整体为空
        if ($realNameConfig['type'] != 'close') {
            $basics   = get_sys_config('', 'basics');
            $siteService = new SiteService();
            $site = $siteService->findsitebydomain($this->request->host());
            if (!empty($site) && $siteService->issiteactive($site)) {
                $siteInfo = (array) ($site->site_info ?? []);
            }
        }

        $userInfo = [];
        if ($this->auth->isLogin()) {
            $userInfo = $this->auth->getUser();
        }

        $loginPageImg = get_sys_config('login_page_img') ?? '';
        if ($loginPageImg['type'] != 'default') {
            if (isset($loginPageImg['text'])) {
                unset($loginPageImg['text']);
            }
        }

        $result = [
            'site'     => $siteInfo ?? [],
            'userInfo' => $userInfo ?? null,
            'userMenus' => $this->getMenus(),
            'member'   => get_sys_config('', 'member') ?? [],
            'loginReg' => get_sys_config('', 'login_reg') ?? [],
            'security' => [
                'config'          => [
                    'is_send_captcha' => get_sys_config('is_send_captcha') == 'open',
                    'is_real_name'    => $realNameConfig['type'] != 'close',
                ],
            ],
            'login_page_img' => $loginPageImg,
        ];
        unset($result['site']['config_group']);

        // 未登录不返回 userInfo
        if (!$this->auth->isLogin()) {
            unset($result['userInfo']);
        }

        $this->success('', $result);
    }

    public function dashboard(): void
    {
        $user = $this->auth->getUser();

        // 订单数统计（总/日/周/月/年）
        $orderStats = [
            'total_orders' => Order::where('user_id', $user->id)->where('is_test', 0)->count(),
            'today_orders' => Order::where('user_id', $user->id)->whereTime('create_time', 'today')->where('is_test', 0)->count(),
            'week_orders'  => Order::where('user_id', $user->id)->whereTime('create_time', 'week')->where('is_test', 0)->count(),
            'month_orders' => Order::where('user_id', $user->id)->whereTime('create_time', 'month')->where('is_test', 0)->count(),
            'year_orders'  => Order::where('user_id', $user->id)->whereTime('create_time', 'year')->where('is_test', 0)->count(),
        ];

        // 已支付订单筛选条件
        $where = ['user_id' => $user->id, 'status' => 1];

        // 销售额统计（总/日/周/月/年）
        $sales = [
            'total_sales' => Order::where($where)->where('is_test', 0)->sum('pay_money'),
            'today_sales' => Order::where($where)->whereTime('create_time', 'today')->where('is_test', 0)->sum('pay_money'),
            'week_sales'  => Order::where($where)->whereTime('create_time', 'week')->where('is_test', 0)->sum('pay_money'),
            'month_sales' => Order::where($where)->whereTime('create_time', 'month')->where('is_test', 0)->sum('pay_money'),
            'year_sales'  => Order::where($where)->whereTime('create_time', 'year')->where('is_test', 0)->sum('pay_money'),
        ];

        // 渠道统计（总/在线）
        $channels = [
            'total_channels'  => \app\admin\model\channel\Account::where('user_id', $user->id)->count(),
            'online_channels' => \app\admin\model\channel\Account::where(['user_id' => $user->id, 'online_status' => 1])->count(),
        ];

        // 按渠道类型×时间维度的数据透视
        $types        = ['total', 'today', 'yesterday'];
        $channelTypes = ['wxpay', 'alipay'];
        $channelData  = [];

        foreach ($types as $period) {
            foreach ($channelTypes as $channelType) {
                $query = Order::where('user_id', $user->id);
                $query = $query->where('type', $channelType);

                if ($period == 'today') {
                    $query->whereTime('create_time', 'today');
                } elseif ($period == 'yesterday') {
                    $query->whereTime('create_time', 'yesterday');
                } elseif ($period == 'week') {
                    $query->whereTime('create_time', 'week');
                } elseif ($period == 'month') {
                    $query->whereTime('create_time', 'month');
                } elseif ($period == 'year') {
                    $query->whereTime('create_time', 'year');
                }

                $channelData[$period][$channelType]['order_total'] = $query->count();
                $channelData[$period][$channelType]['paid_orders'] = $query->where('status', 1)->count();
                $channelData[$period][$channelType]['sales_amount'] = $query->sum('pay_money');
            }
        }

        // 销售趋势
        $salesTrend = [];
        foreach ($channelTypes as $channelType) {
            try {
                $salesTrend[$channelType] = Order::where('user_id', $user->id)
                    ->where('type', $channelType)
                    ->select();
            } catch (\InvalidArgumentException $e) {
                $salesTrend[$channelType] = [];
            }
        }

        $this->success('ok', [
            'order'       => $orderStats,
            'sales'       => $sales,
            'channel'     => $channels,
            'channelData' => $channelData,
            'salesTrend'  => $salesTrend,
        ]);
    }

    public function getMenus(): array
    {
        $menus = [];

        $realNameConfig = get_sys_config('real_name_config');
        $realNameEnabled = $realNameConfig['type'] != 'close';

        $userRule = \app\admin\model\UserRule::where('status', 1)->select() ?? null;

        // 修正：SiteService 的 opcode 方法清单中不存在 getMenus（管线误引入），
        // 菜单数据源为本方法上方已查询的 UserRule（字段 id/pid/type/title/name/path/icon/
        // menu_type/url 与下方树组装完全吻合，见文件头注释"UserRule 查询 + ba\Tree 组装"）
        $menus = $userRule ? $userRule->toArray() : [];

        if (!empty($menus)) {
            $menus = array_filter($menus, fn($m) => $m['name'] !== 'apply');
        }

        // 代理身份追加菜单
        $agent = \app\admin\model\agent\Agent::where('user_id', $this->auth->id)->find() ?? null;
        $agentRule = \app\admin\model\UserRule::where('name', 'agent')->find();
        if (!empty($agentRule) && $agentRule->status != 1) {
            $menus = array_filter($menus, fn($m) => $m['name'] !== 'agent');
        }

        // 如果用户已实名(或实名功能关闭), 移除 tableridcard
        $agentRule = \app\admin\model\UserRule::where(['name' => 'agent'])->where('id', '<>', '')->find();
        $agentMenus = array_filter($menus, fn($m) => str_contains($m['name'] ?? '', 'tableridcard'));

        $menuTree = [];
        foreach ($menus as $menu) {
            $menu['meta'] = [
                'title'    => $menu['title'],
                'icon'     => $menu['icon'],
                'affixTab' => false,
            ];

            if ($menu['type'] == 'links' && $menu['menu_type'] == 'link') {
                $menu['meta']['link'] = $menu['url'];
            }

            if ($menu['name'] == 'applyMentSform') {
                $menu['meta']['hideInMenu'] = true;
            } else {
                $menu['meta']['hideInMenu'] = false;
            }

            $menuTree[$menu['name']] = $menu;
        }

        $tree = \ba\Tree::instance()->assembleChild($menuTree, 'pid', 'name');
        return \ba\Tree::assembleTree($tree);
    }
}
