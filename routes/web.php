<?php

declare(strict_types=1);

use App\Controllers\AdminMenuController;
use App\Controllers\ClientPreorderController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Controllers\OrderPaymentController;

return [
    ['GET', '/', HomeController::class . '@index'],

    // 管理端模块（菜单管理）
    ['GET', '/admin/menus', AdminMenuController::class . '@index'],

    ['POST', '/admin/categories', AdminMenuController::class . '@createCategory'],
    ['PUT', '/admin/categories', AdminMenuController::class . '@updateCategory'],
    ['DELETE', '/admin/categories', AdminMenuController::class . '@deleteCategory'],

    ['POST', '/admin/menu-items', AdminMenuController::class . '@createMenuItem'],
    ['PUT', '/admin/menu-items', AdminMenuController::class . '@updateMenuItem'],
    ['DELETE', '/admin/menu-items', AdminMenuController::class . '@deleteMenuItem'],

    // 对外菜单接口
    ['GET', '/api/menus', MenuController::class . '@index'],

    // 客户端模块（桌码预下单）
    ['GET', '/client/preorders', ClientPreorderController::class . '@index'],
    ['POST', '/client/preorders', ClientPreorderController::class . '@store'],

    // 订单支付模块占位（仅接口与状态预留，暂不接支付渠道）
    ['POST', '/api/orders/payments', OrderPaymentController::class . '@createPaymentIntent'],
    ['GET', '/api/orders/payments/status', OrderPaymentController::class . '@paymentStatus'],
];
