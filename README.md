# YPay Entry — 多商户聚合支付系统

基于 ThinkPHP 8.1(框架) + BuildAdmin(管理后台) 的多商户聚合支付进件系统：商户进件 / 支付通道 / 订单流转 / 支付回调 / 钱包提现 / 会员订阅。商户端与后台均为 Vue3 SPA，与 PHP 接口同域部署。

---

## 一、运行环境

| 组件 | 要求 |
|------|------|
| PHP | **8.2**(必须 ≥ 8.2;扩展:`pdo_mysql redis bcmath openssl curl gd zip iconv mbstring json simplexml libxml fileinfo`;常驻进程另需 CLI 的 `pcntl posix sockets`) |
| MySQL | 8.x,utf8mb4 |
| Redis | 必须(承载 token / cache / queue 三项职责) |
| nginx | 任意稳定版(或 Apache) |
| 进程守护 | Supervisor(可选但强烈建议，用于队列与通道监控) |

> 本包为明文 PHP，**不要安装 swoole_loader**。

## 二、目录结构

```
项目根/
├── public/          ★ Web 根目录(index.html/.php 所在;SPA 资源、后台 admin.html、安装向导)
│   └── views/home-template/   首页 PHP 模板(default/Home_One/Home_Two/newTemple/payhub)
├── app/             应用代码(admin/api/worker/supply/common + 各模块控制器)
├── config/  route/  extend/  modules/(sms、mail)  database/(install.sql+migrations)
├── vendor/          Composer 依赖(本包已内置，无需 composer install)
├── runtime/         运行时(日志/缓存/session，需对 web 用户可写)
└── think            CLI 入口(队列/安装命令)
```

## 三、部署步骤

### 1. 放置代码与权限
```bash
chown -R www:www /path/to/site
chmod 755 runtime && chown -R www:www runtime
```

### 2. 配置 .env
```bash
cp .env-example .env   # 填写数据库与 Redis;密码等留空回退值在 config/database.php 中已无硬编码
```
- `[DATABASE]` 表前缀固定 `as_`(与 install.sql 一致，勿改)
- Redis 三库分工(库号可在 `[REDIS]` 段调整):`1`=token 会话、`2`=cache、`3`=queue

### 3. 创建数据库并导入
```bash
mysql -u<用户> -p<密码> -e "CREATE DATABASE your_db DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u<用户> -p<密码> your_db < database/install.sql   # 69 张表(42 基线 + 27 补全)，含初始种子数据
```
或访问 `http://<域名>/install/` 走安装向导(向导会建表并设置管理员密码)。安装完成后需删除或锁住 `public/install/` 入口(写 install.lock)。

### 4. nginx 站点配置(关键)

Web 根指向 `public/`，伪静态决定 SPA 与 PHP 接口的共存方式:

```nginx
server {
    listen 80; listen 443 ssl; http2 on;
    server_name yourdomain.com;
    index index.php index.html index.htm;          # ← index.php 必须排在 index.html 前(首页功能生效的前提)
    root /path/to/site/public;

    # 固定回调前缀恒进 PHP
    location /qrcode            { rewrite ^(.*)$ /index.php?s=$1 last; }
    location /api/ajax/preview  { rewrite ^(.*)$ /index.php?s=$1 last; }
    location /payment/          { rewrite ^(.*)$ /index.php?s=$1 last; }
    location /notify/           { rewrite ^(.*)$ /index.php?s=$1 last; }

    location / {
        # 带 server=1 标记的请求(SPA 发起 API 时自动附带)交给 PHP
        set $server 0;
        if ($request_uri ~* "server=1") { set $server 1; }
        if ($http_server)                { set $server 1; }
        if ($server = 1) { rewrite ^(.*)$ /index.php?s=/$1 last; }
        try_files $uri $uri/ /index.html;         # 其余路径命中 SPA
    }

    include enable-php-82.conf;                   # fastcgi_pass 到 PHP 8.2 pool
    # 多站点共享 fpm 时，建议为本站使用独立 pool(见附录)
}
```

要点：
- 支付/回调前缀(`/notify/`、`/payment/`、`/qrcode`、`/api/ajax/preview`)必须直达 PHP，不依赖 `server=1` 标记。
- 若开启 nginx `fastcgi_intercept_errors`，PHP 返回的 404 会被包装成 SPA 页(表象"整站静态")；排查接口 404 时先核对路由完整路径(如 `/notify/payTest`，多写一段即 404)。
- 测试 URL 与路由大小写需一致(多级控制器用点形式:`/api/user.Withdraw/info`)。

### 5. 常驻进程(Supervisor 两个程序即可)

```ini
; 队列消费者
[program:queue-worker]
command=php /path/to/site/think queue:work --sleep 2 -q
directory=/path/to/site
user=www
autostart=true ; autorestart=true
numprocs=1
process_name=%(program_name)s_%(process_num)02d      ; ← 宝塔面板 UI 需要 _NN 后缀，否则进程不显示

; 通道监控(轮询在线免挂通道并向队列推送任务)
[program:channel-monitor]
command=php /path/to/site/think channel:monitor
directory=/path/to/site
user=www
autostart=true ; autorestart=true
numprocs=1
process_name=%(program_name)s_%(process_num)02d
```

> worker 的 WebSocket/HTTP 服务属 BuildAdmin 框架模板项(非必需)，默认无需常驻。

### 6. 冒烟验证(部署后逐项检查)

| 检查项 | 预期 |
|--------|------|
| `GET /` | 200(首页模板渲染;若 `is_home=close` 则 302 → `/login`，属正常) |
| `GET /api/index/init?server=1` | `{"code":1,...}` JSON |
| `GET /api/common/captcha?server=1&id=<随机串>` | 200 出 PNG(登录验证码) |
| `GET /admin.html` 登录 | 管理员账号密码为安装时设置;菜单可出(无 token 时后台会跳登录页) |
| `POST /notify/pay/payTest` | 200(测试回调入口) |
| 队列 | `redis-cli -n 3 llen '{queues:default}'` 为 0(有积压时由 queue-worker 消费) |

## 四、运行与运维速查

- **业务日志**:`runtime/log/<年月>/<日>.log`(TP8 按 月/日 归档)；fpm 慢日志:pool 配置内独立 slowlog。
- **登录验证码**：系统配置 `captcha_config` 的 `type` 推荐 `img`(图形验证码，表 `as_captcha`;登录接口校验块位于 `Login::login`，开启状态由 `is_login_reg_captcha` 控制。`geet` 需前端接入极验 v4 组件后才可切换)。
- **token 清理**：Redis 库 1 的 token 键无 TTL(靠 `expire_time` 字段惰性判断，过期键常驻)，历史会话需定期清理。
- **队列观察**:`redis-cli -n 3 llen '{queues:default}'`;`supervisorctl status` 确认 queue-worker/channel-monitor RUNNING。
- **首页开关与模板**:后台"主题设置→首页模板"对应 `home_template`;关闭 `is_home` 时首页直接跳商户登录。
- **出站拦截**:YAuth/插件商店等远程授权校验默认被本地拦截器拦截(提示"通道已被拦截"属设计行为，非故障;关闭开关在对应库类 `NetworkGuard::ENABLED` 或 `PluginAuthClient`/`YAuthClient` 的方法拦截处)。

## 附录:php-fpm 独立池(建议)

当同机承载多个站点、某站存在长耗时请求时，本站建议使用独立 pool(独立 socket + `max_children`)，避免被其他站拖垮返回 502/404：

```ini
; /www/server/php/82/etc/php-fpm.d/mysite.conf
[mysite]
listen = /tmp/php-cgi-mysite.sock
pm = dynamic
pm.max_children = 80
slowlog = /var/log/php-fpm-slow-mysite.log
```
站点 nginx include 对应 `fastcgi_pass unix:/tmp/php-cgi-mysite.sock;` 的配置文件(注意 php-fpm.conf 需含 `include=/www/server/php/82/etc/php-fpm.d/*.conf`，否则池文件不生效)。
