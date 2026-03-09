<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>前端业务页</title>
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(180deg, #fff7f0, #f7f8fc); color: #111827; }
        .wrap { max-width: 920px; margin: 0 auto; padding: 24px 16px 40px; }
        .hero { background: #fff; border-radius: 20px; padding: 24px; box-shadow: 0 12px 30px rgba(0,0,0,.06); }
        .hero h1 { margin: 0 0 10px; }
        .grid { display: grid; gap: 14px; margin-top: 16px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
        .card { background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 8px 20px rgba(0,0,0,.05); }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 10px; text-decoration: none; font-weight: 700; margin-top: 10px; }
        .btn-primary { background: #ff5a00; color: #fff; }
        .btn-ghost { border: 1px solid #d1d5db; color: #111827; }
    </style>
</head>
<body>
<div class="wrap">
    <section class="hero">
        <h1>🍜 全新前端业务页</h1>
        <p>已保留原有工程测试页面，以下是贴近真实门店使用场景的新路由入口。</p>
        <div class="grid">
            <article class="card">
                <h3>后台管理</h3>
                <p>菜单分类、菜品上下架、库存与价格维护。</p>
                <a class="btn btn-primary" href="/frontend/admin/">进入 /frontend/admin/</a>
            </article>
            <article class="card">
                <h3>前台点菜</h3>
                <p>扫码桌码后进入点餐页，支持分类浏览、购物车与提交订单。</p>
                <a class="btn btn-primary" href="/frontend/order?table_code=T001">体验 /frontend/order?table_code=T001</a>
            </article>
            <article class="card">
                <h3>返回旧页面</h3>
                <p>你之前已有的功能入口仍可继续使用，不受影响。</p>
                <a class="btn btn-ghost" href="/">回到首页</a>
            </article>
        </div>
    </section>
</div>
</body>
</html>
