<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商家后台</title>
    <style>
        :root { --brand:#ff5a00; --bg:#f6f7fb; --card:#fff; --line:#e5e7eb; --text:#111827; --muted:#6b7280; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:var(--bg); color:var(--text); }
        .wrap { max-width:1100px; margin:0 auto; padding:20px 14px 28px; }
        .header { background: linear-gradient(135deg,#ff6f28,#ff8b3d); color:#fff; border-radius:18px; padding:20px; box-shadow: 0 12px 28px rgba(255,90,0,.24); }
        .header a { color:#fff; font-weight:700; }
        .kpi { display:grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-top:12px; }
        .kpi .item { background: rgba(255,255,255,.16); border-radius:12px; padding:10px; }
        .grid { display:grid; gap:14px; margin-top:14px; grid-template-columns: repeat(auto-fit, minmax(300px,1fr)); }
        .card { background:var(--card); border-radius:14px; padding:14px; box-shadow: 0 8px 20px rgba(0,0,0,.05); }
        .title { margin:0 0 10px; font-size:17px; }
        input,select,textarea,button { width:100%; padding:10px; border:1px solid var(--line); border-radius:10px; font-size:14px; margin-bottom:8px; }
        button { background:var(--brand); border:none; color:#fff; font-weight:700; cursor:pointer; }
        button.secondary { background:#eef2ff; color:#374151; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th,td { border-bottom:1px solid var(--line); padding:8px 4px; text-align:left; }
        .msg { margin-top:10px; font-size:13px; }
        .ok { color:#059669; }
        .error { color:#dc2626; }
    </style>
</head>
<body>
<div class="wrap">
    <section class="header">
        <h1 style="margin:0">🍱 商家后台 /frontend/admin/</h1>
        <p style="margin:8px 0 0">偏业务化运营界面，沿用原有 API，方便直接接入真实点餐场景。</p>
        <div class="kpi">
            <div class="item"><div>分类数</div><strong><?= count($menus['categories'] ?? []) ?></strong></div>
            <div class="item"><div>菜品数</div><strong><?= count($menus['menu_items'] ?? []) ?></strong></div>
            <div class="item"><div>在售分类</div><strong><?= count($publicMenu ?? []) ?></strong></div>
        </div>
        <div style="margin-top:8px;"><a href="/frontend/">前端首页</a> · <a href="/admin/menus">旧管理页</a></div>
    </section>

    <div id="apiMsg" class="msg"></div>

    <section class="grid">
        <article class="card">
            <h3 class="title">新增分类</h3>
            <form class="api-form" data-method="POST" data-url="/admin/categories">
                <input name="name" placeholder="如：热销推荐" required>
                <input name="sort" type="number" placeholder="排序（数字越小越靠前）">
                <select name="status"><option value="enabled">enabled</option><option value="disabled">disabled</option></select>
                <button>创建分类</button>
            </form>
        </article>
        <article class="card">
            <h3 class="title">新增菜品</h3>
            <form class="api-form" data-method="POST" data-url="/admin/menu-items">
                <input name="name" placeholder="如：招牌牛肉饭" required>
                <input name="category_id" type="number" placeholder="分类 ID" required>
                <input name="price" type="number" step="0.01" placeholder="价格" required>
                <input name="stock" type="number" placeholder="库存" value="30">
                <textarea name="description" placeholder="一句描述卖点"></textarea>
                <select name="status"><option value="on_sale">on_sale</option><option value="off_sale">off_sale</option></select>
                <button>创建菜品</button>
            </form>
        </article>
        <article class="card">
            <h3 class="title">编辑 / 删除分类</h3>
            <form class="api-form" data-method="PUT" data-url="/admin/categories">
                <input name="id" type="number" placeholder="分类 ID" required>
                <input name="name" placeholder="新名称（可选）">
                <input name="sort" type="number" placeholder="新排序（可选）">
                <select name="status"><option value="">状态（可选）</option><option value="enabled">enabled</option><option value="disabled">disabled</option></select>
                <button class="secondary">更新分类</button>
            </form>
            <form class="api-form" data-method="DELETE" data-url="/admin/categories">
                <input name="id" type="number" placeholder="分类 ID" required>
                <button>删除分类</button>
            </form>
        </article>
        <article class="card">
            <h3 class="title">编辑 / 删除菜品</h3>
            <form class="api-form" data-method="PUT" data-url="/admin/menu-items">
                <input name="id" type="number" placeholder="菜品 ID" required>
                <input name="name" placeholder="新名称（可选）">
                <input name="price" type="number" step="0.01" placeholder="新价格（可选）">
                <input name="stock" type="number" placeholder="新库存（可选）">
                <select name="status"><option value="">状态（可选）</option><option value="on_sale">on_sale</option><option value="off_sale">off_sale</option></select>
                <button class="secondary">更新菜品</button>
            </form>
            <form class="api-form" data-method="DELETE" data-url="/admin/menu-items">
                <input name="id" type="number" placeholder="菜品 ID" required>
                <button>删除菜品</button>
            </form>
        </article>
    </section>

    <section class="card" style="margin-top:14px;">
        <h3 class="title">分类与菜品概览</h3>
        <table>
            <thead><tr><th>分类ID</th><th>分类名</th><th>状态</th><th>菜品数</th></tr></thead>
            <tbody>
            <?php foreach (($menus['categories'] ?? []) as $c):
                $count = 0;
                foreach (($menus['menu_items'] ?? []) as $item) {
                    if ((int) $item['category_id'] === (int) $c['id']) { $count++; }
                }
            ?>
                <tr><td><?= (int) $c['id'] ?></td><td><?= $h($c['name']) ?></td><td><?= $h($c['status']) ?></td><td><?= $count ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card" style="margin-top:14px;">
        <h3 class="title">菜品列表</h3>
        <table>
            <thead><tr><th>菜品ID</th><th>分类ID</th><th>菜品名</th><th>价格</th><th>库存</th><th>状态</th></tr></thead>
            <tbody>
            <?php foreach (($menus['menu_items'] ?? []) as $item): ?>
                <tr>
                    <td><?= (int) $item['id'] ?></td>
                    <td><?= (int) $item['category_id'] ?></td>
                    <td><?= $h($item['name']) ?></td>
                    <td>¥<?= number_format((float) $item['price'], 2) ?></td>
                    <td><?= (int) $item['stock'] ?></td>
                    <td><?= $h($item['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</div>
<script>
document.querySelectorAll('.api-form').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const data = Object.fromEntries(new FormData(form).entries());
        const id = data.id;
        delete data.id;
        Object.keys(data).forEach((key) => data[key] === '' && delete data[key]);
        const url = form.dataset.url + ((id && form.dataset.method !== 'POST') ? `?id=${encodeURIComponent(id)}` : '');

        const response = await fetch(url, {
            method: form.dataset.method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const body = await response.json();
        const box = document.getElementById('apiMsg');
        box.className = `msg ${response.ok ? 'ok' : 'error'}`;
        box.textContent = `${response.status} ${JSON.stringify(body)}`;
        if (response.ok) {
            setTimeout(() => location.reload(), 600);
        }
    });
});
</script>
</body>
</html>
