<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName ?? 'PHP App', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4 py-md-5">
    <h1 class="mb-3"><?= htmlspecialchars($appName ?? 'PHP App', ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted">已为现有 API 提供 Bootstrap 响应式页面，可直接在浏览器中调试接口。</p>
    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">管理端菜单</h5>
                    <p class="card-text">查看分类与菜品，并通过页面直接调用新增/编辑/删除接口。</p>
                    <a class="btn btn-primary" href="/admin/menus">进入 /admin/menus</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">对外菜单</h5>
                    <p class="card-text">查看可售菜单聚合结果（分类 + 在售菜品）。</p>
                    <a class="btn btn-primary" href="/api/menus">进入 /api/menus</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">客户端预下单</h5>
                    <p class="card-text">查看订单、桌码校验、菜单、购物车与提交下单。</p>
                    <a class="btn btn-primary" href="/client/preorders">进入 /client/preorders</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">支付状态</h5>
                    <p class="card-text">查询支付状态，验证支付状态接口返回结构。</p>
                    <a class="btn btn-outline-primary" href="/api/orders/payments/status">进入 /api/orders/payments/status</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
