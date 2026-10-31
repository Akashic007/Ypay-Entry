<?php

use think\facade\Route;


/**
 * 外部路由
 */
// 二维码路由
Route::rule('qrcode', 'app\api\controller\Common@qrcode', 'GET|POST');


/**
 * 用户支付相关的路由组
 */
Route::group('payment', function () {
    /**
     * 微信相关界面
     */
    Route::group('wxpay', function () {
        Route::rule('jsapiPay/:order_no', 'app\api\controller\payNotify\WxpayOfficialSpecial@jsapiPay'); // jsapi发起支付中转页面
    });

    /**
     * 支付宝相关界面
     */
    Route::group('alipay', function () {
//        Route::rule('openId','app\api\controller\payNotify\AlipaySign@openId');
        Route::rule('openId/:type', 'app\api\controller\payNotify\AlipaySign@openId');
        Route::rule('openId/:type/:order_no', 'app\api\controller\payNotify\AlipaySign@openId');
    });
    Route::rule('jsapiPay/:order_no', 'app\api\controller\payNotify\WxpayOfficialSpecial@jsapiPay'); // jsapi发起支付中转页面
    // 支付页面
    Route::rule('getPay', 'app\api\controller\Payment@getPay');
    // 订单轮询
    Route::rule('polling', 'app\api\controller\Payment@pollingOrder');
});

/**
 * 通知|回调相关的接口
 */
Route::group('notify', function () {
    /**
     * 通道测试回调
     */
    Route::rule('payTest', 'app\api\controller\payNotify\Test@channelTest'); // 通道测试支付回调
    Route::rule('demo/callback', 'app\api\controller\TestPayDemo@returnCallback'); // DEMO支付测试回调
    Route::rule('demo/notifyCallback', 'app\api\controller\TestPayDemo@notifyCallback'); // DEMO支付测试异步回调

    /**
     * 用户通道支付回调的通知集合
     */
    Route::group('pay', function () {
        // 微信进件支付通道
        Route::group('wxpaySpecial', function () {
            Route::rule(':order_no', 'app\api\controller\payNotify\WxpayOfficialSpecial@notify'); // 支付回调
            Route::rule('refund/:refund_no', 'app\api\controller\payNotify\WxpayOfficialSpecial@refund'); // 退款回调
        });
        /**
         * 软件版订单推送
         */
        Route::group('software', function () {
            Route::rule('callback', 'app\api\controller\Software@callback');
        });

        /**
         * 上游支付回调通知
         */
        Route::group('upstream', function () {
            // 支付订单回调
            Route::rule('callback/:order_no', 'app\api\controller\payNotify\Upstream@pay');
        });

        /**
         * 支付宝官方签约通道
         */
        Route::group('alipaySign', function () {

            // 商户授权码回调
            Route::rule('mchAuthCode', 'app\api\controller\payNotify\AlipaySign@mchAuthCode');

            // 支付回调通知
            Route::rule('payCallback', 'app\api\controller\payNotify\AlipaySign@payCallback');

        });

        /**
         * 支付宝官方扫码支付
         */
        Route::group('alipayScan', function () {
            // 支付回调通知
            Route::rule('payCallback', 'app\api\controller\payNotify\AlipayScan@payCallback');
        });


        /**
         * 微信官方扫码通道
         */
        Route::group('wxpayScan', function () {
            // 支付回调通知
            Route::rule('payCallback', 'app\api\controller\payNotify\Wxpay@payCallback');
        });
    });

    /**
     * 快捷登录的接口
     */
    Route::group('oAuth', function () {
        Route::rule('qqOfficial', 'app\api\controller\OAuthNotify@qqOfficial');
        Route::rule('aggregation', 'app\api\controller\OAuthNotify@aggregation');
    });

    /**
     * 系统收款回调相关的通知集合
     */
    Route::group('sysPay', function () {
        // 易支付V1版本
        Route::group('epayV1', function () {
            Route::rule('notify', 'app\api\controller\sysPayNotify\EpayV1@notifyUrl'); // 异步回调
            Route::rule('returnUrl', 'app\api\controller\sysPayNotify\EpayV1@returnUrl'); // 同步回调
        });
    });
    /**
     * 实名认证回调接口
     */
    Route::group('realName', function () {
        Route::rule('wechat', 'app\api\controller\user\RealName@callback');
    });

    /**
     * 管理员实名认证回调接口
     */
    Route::group('adminRealName', function () {
        Route::rule('wechat', 'app\admin\controller\routine\admin\RealName@callback');
    });

    /**
     * 投诉通知回调接口
     */
    Route::group('complaint', function () {
        Route::rule('wxpayServiceProvider/:ec_id', 'app\api\controller\complaintNotify\WxpayServiceProvider@callback');
    });

    /**
     * 进件通知接口
     */
    Route::group('entry', function () {
        // 富友相关的通知
        Route::group('fuiou', function () {
            Route::rule('signContract', 'app\api\controller\entryNotify\Fuiou@signContract');
        });
    });

    /**
     * 上下游接口
     */
    Route::group('supply', function () {
        // 上游相关
        Route::group('upstream', function () {
            // 上游对接状态回调
            Route::rule('dockingCallback', 'app\admin\controller\supply\Upstream@dockingCallback');
        });
    });
});





