<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html><html lang="zh-CN"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>管理端菜单</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-3 py-md-4">
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">管理端菜单 /admin/menus</h1><a class="btn btn-sm btn-outline-secondary" href="/">首页</a></div>
<div class="alert alert-info small">页面为后端渲染，表单通过 fetch 调用原 API（POST/PUT/DELETE），适配移动端。</div>
<div id="apiMsg" class="small"></div>
<div class="row g-3">
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">分类（<?= count($menus['categories'] ?? []) ?>）</div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>ID</th><th>名称</th><th>排序</th><th>状态</th></tr></thead><tbody>
<?php foreach (($menus['categories'] ?? []) as $c): ?><tr><td><?= (int)$c['id'] ?></td><td><?= $h($c['name']) ?></td><td><?= (int)$c['sort'] ?></td><td><?= $h($c['status']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></div>
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">可售菜单预览 /api/menus</div><div class="card-body"><pre class="small bg-light border rounded p-2 mb-0"><?= $h(json_encode($publicMenu ?? [], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) ?></pre></div></div></div>

<div class="col-12 col-lg-6"><div class="card"><div class="card-header">新增分类</div><div class="card-body">
<form class="api-form" data-method="POST" data-url="/admin/categories">
<input class="form-control mb-2" name="name" placeholder="分类名称" required>
<div class="row g-2"><div class="col-6"><input class="form-control" type="number" name="sort" placeholder="排序"></div><div class="col-6"><select class="form-select" name="status"><option value="enabled">enabled</option><option value="disabled">disabled</option></select></div></div>
<button class="btn btn-primary mt-2 w-100">提交</button></form></div></div></div>

<div class="col-12 col-lg-6"><div class="card"><div class="card-header">编辑/删除分类</div><div class="card-body">
<form class="api-form" data-method="PUT" data-url="/admin/categories">
<input class="form-control mb-2" type="number" name="id" placeholder="分类 ID" required>
<input class="form-control mb-2" name="name" placeholder="新名称（可选）">
<button class="btn btn-warning w-100">更新分类</button></form>
<form class="api-form mt-2" data-method="DELETE" data-url="/admin/categories">
<input class="form-control mb-2" type="number" name="id" placeholder="分类 ID" required>
<button class="btn btn-outline-danger w-100">删除分类</button></form></div></div></div>

<div class="col-12"><div class="card"><div class="card-header">菜品（<?= count($menus['menu_items'] ?? []) ?>）</div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>ID</th><th>分类</th><th>名称</th><th>价格</th><th>状态</th><th>库存</th></tr></thead><tbody>
<?php foreach (($menus['menu_items'] ?? []) as $i): ?><tr><td><?= (int)$i['id'] ?></td><td><?= (int)$i['category_id'] ?></td><td><?= $h($i['name']) ?></td><td>¥<?= number_format((float)$i['price'],2) ?></td><td><?= $h($i['status']) ?></td><td><?= (int)$i['stock'] ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></div>

<div class="col-12 col-lg-6"><div class="card"><div class="card-header">新增菜品</div><div class="card-body"><form class="api-form" data-method="POST" data-url="/admin/menu-items">
<div class="row g-2"><div class="col-6"><input class="form-control" name="name" placeholder="菜品名" required></div><div class="col-6"><input class="form-control" type="number" name="category_id" placeholder="分类ID" required></div></div>
<div class="row g-2 mt-1"><div class="col-6"><input class="form-control" type="number" step="0.01" name="price" placeholder="价格" required></div><div class="col-6"><input class="form-control" type="number" name="stock" placeholder="库存" value="0"></div></div>
<input class="form-control mt-2" name="description" placeholder="描述"><select class="form-select mt-2" name="status"><option value="on_sale">on_sale</option><option value="off_sale">off_sale</option></select>
<button class="btn btn-primary mt-2 w-100">提交</button></form></div></div></div>

<div class="col-12 col-lg-6"><div class="card"><div class="card-header">编辑/删除菜品</div><div class="card-body">
<form class="api-form" data-method="PUT" data-url="/admin/menu-items">
<input class="form-control mb-2" type="number" name="id" placeholder="菜品 ID" required>
<input class="form-control mb-2" name="name" placeholder="新名称（可选）">
<input class="form-control mb-2" type="number" step="0.01" name="price" placeholder="新价格（可选）">
<input class="form-control mb-2" type="number" name="stock" placeholder="新库存（可选）">
<button class="btn btn-warning w-100">更新菜品</button></form>
<form class="api-form mt-2" data-method="DELETE" data-url="/admin/menu-items"><input class="form-control mb-2" type="number" name="id" placeholder="菜品 ID" required><button class="btn btn-outline-danger w-100">删除菜品</button></form>
</div></div></div>
</div></div>
<script>
document.querySelectorAll('.api-form').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());
    const id = data.id; delete data.id;
    Object.keys(data).forEach(k => data[k] === '' && delete data[k]);
    const url = form.dataset.url + ((id && form.dataset.method !== 'POST') ? `?id=${encodeURIComponent(id)}` : '');
    const res = await fetch(url, {method: form.dataset.method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(data)});
    const body = await res.json();
    const box = document.getElementById('apiMsg');
    box.innerHTML = `<div class="alert ${res.ok?'alert-success':'alert-danger'}">${res.status} ${JSON.stringify(body)}</div>`;
    if (res.ok) setTimeout(()=>location.reload(), 700);
  });
});
</script>
</body></html>
