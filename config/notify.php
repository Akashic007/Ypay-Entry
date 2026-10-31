<?php

/**
 * 通知配置
 */

return [
    /**
     * 通知模板事件
     */
    'template' => [
        // 用户相关
        ['name' => '用户登陆提醒', 'event' => 'user_login', 'event_type' => 'user',
            'vars' => [
                'site_name' => '站点名称', 'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'login_time' => '登录时间', 'ip' => 'ID地址'
            ]],
        ['name' => '用户注册提醒', 'event' => 'user_register', 'event_type' => 'user',
            'vars' => [
                'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'reg_time' => '注册时间', 'ip' => 'ID地址', 'site_name' => '站点名称'
            ]],
        ['name' => '用户密码重置提醒', 'event' => 'user_reset_password', 'event_type' => 'user',
            'vars' => [
                'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'reset_time' => '重置时间', 'site_name' => '站点名称'
            ]],

        // 用户余额相关
        ['name' => '用户余额不足提醒', 'event' => 'user_balance_insufficient', 'event_type' => 'user_money',
            'vars' => [
                'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'money' => '余额', 'site_name' => '站点名称'
            ]],


        // 订单相关
        ['name' => '创建订单提醒', 'event' => 'order_create', 'event_type' => 'order',
            'vars' => [
                'site_name' => '站点名称', 'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'order_id' => '订单ID', 'order_no' => '订单编号', 'order_amount' => '订单金额', 'name' => '商品名称',
                'create_time' => '创建时间',
                'channel_id' => '通道ID', 'pay_type' => '支付类型', 'channel_type' => '通道类型',
            ]],
        ['name' => '订单支付成功提醒', 'event' => 'order_pay_success', 'event_type' => 'order',
            'vars' => [
                'site_name' => '站点名称', 'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'order_id' => '订单ID', 'order_no' => '订单编号', 'order_amount' => '订单金额', 'name' => '商品名称',
                'create_time' => '创建时间', 'pay_time' => '支付时间',
                'channel_id' => '通道ID', 'pay_type' => '支付类型', 'channel_type' => '通道类型',
            ]],
        ['name' => '订单回调提醒', 'event' => 'order_callback', 'event_type' => 'order',
            'vars' => [
                'site_name' => '站点名称', 'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'order_id' => '订单ID', 'order_no' => '订单编号', 'order_amount' => '订单金额', 'name' => '商品名称',
                'create_time' => '创建时间', 'pay_time' => '支付时间', 'notify_time' => '通知时间',
                'channel_id' => '通道ID', 'pay_type' => '支付类型', 'channel_type' => '通道类型',
            ]],


        // 通道相关
        ['name' => '创建通道', 'event' => 'create_channel', 'event_type' => 'channel',
            'vars' => [
                'site_name' => '站点名称', 'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'create_time' => '创建时间',
                'channel_id' => '通道ID', 'pay_type' => '支付类型', 'channel_type' => '通道类型',
            ]],
        ['name' => '更新通道', 'event' => 'update_channel', 'event_type' => 'channel',
            'vars' => [
                'site_name' => '站点名称', 'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'create_time' => '创建时间', 'update_time' => '更新时间',
                'channel_id' => '通道ID', 'pay_type' => '支付类型', 'channel_type' => '通道类型',
            ]],
        ['name' => '通道掉线', 'event' => 'channel_offline', 'event_type' => 'channel',
            'vars' => [
                'site_name' => '站点名称', 'username' => '用户名', 'nickname' => '昵称', 'user_id' => '用户ID',
                'create_time' => '创建时间', 'offline_time' => '掉线时间',
                'channel_id' => '通道ID', 'pay_type' => '支付类型', 'channel_type' => '通道类型',
            ]]
    ],

    /**
     * 通知通道类型
     */
    'type' => [
        // 短信通道
        [
            'name' => '短信',
            'type' => 'sms',
        ],

        // 邮件通道
        [
            'name' => '邮件',
            'type' => 'ems',
        ],
//        [
//            'name'=>'WxPusher',
//            'type' => 'wxPusher'
//        ]
    ],
];