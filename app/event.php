<?php
// 事件定义文件
use app\common\listener\EntryUpload;
use app\common\listener\PushNotifyJob;
use app\common\listener\UserLoginLog;
use app\common\listener\UserRegisterSiteBinding;

return [
    'bind' => [
        'UserLogin'     => app\common\event\UserLogin::class,
        'UserRegister'  => app\common\event\UserRegister::class,
        'UserNotify'    => app\common\event\UserNotify::class,
        'OrderNotify'   => app\common\event\OrderNotify::class,
        'ChannelNotify' => app\common\event\ChannelNotify::class,
    ],

    'listen' => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'EntryUpload'  => [EntryUpload::class,],
        'UserLogin' => [
            UserLoginLog::class,
            PushNotifyJob::class,
        ],
        'UserRegister' => [
            UserRegisterSiteBinding::class,
            PushNotifyJob::class,
        ],
        app\common\event\UserNotify::class => [
            PushNotifyJob::class,
        ],
        app\common\event\OrderNotify::class => [
            PushNotifyJob::class,
        ],
        app\common\event\ChannelNotify::class => [
            PushNotifyJob::class,
        ],
    ],

    'subscribe' => [],
];
