<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>客户端预下单</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-3 py-md-4">
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">客户端预下单 /client/preorders</h1><a class="btn btn-sm btn-outline-secondary" href="/">首页</a></div>
<div id="apiMsg" class="small"></div>
<div class="row g-3">
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">桌码校验</div><div class="card-body"><form method="get" action="/client/table/validate" class="row g-2"><div class="col-8"><input class="form-control" name="table_code" placeholder="A01"></div><div class="col-4"><button class="btn btn-primary w-100">跳转校验</button></div></form></div></div></div>
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">状态流转</div><div class="card-body"><pre class="small bg-light border rounded p-2 mb-0"><?= $h(json_encode($statusFlow ?? [], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) ?></pre></div></div></div>
<div class="col-12"><div class="card"><div class="card-header">可售菜单 /client/menu</div><div class="card-body"><pre class="small bg-light border rounded p-2 mb-0"><?= $h(json_encode($menu ?? [], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) ?></pre></div></div></div>
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">创建/更新购物车 /client/cart</div><div class="card-body">
<form class="api-form" data-method="POST" data-url="/client/cart">
<input class="form-control mb-2" name="table_code" placeholder="桌码（如 A01）" required>
<textarea class="form-control mb-2" name="items_json" rows="4" placeholder='items JSON: [{"menu_item_id":1,"quantity":1}]' required></textarea>
<input class="form-control mb-2" name="remark" placeholder="备注（可选）">
<button class="btn btn-primary w-100">提交购物车</button></form></div></div></div>
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">提交预订单 /client/preorders/submit</div><div class="card-body">
<form class="api-form" data-method="POST" data-url="/client/preorders/submit">
<input class="form-control mb-2" name="table_code" placeholder="桌码（如 A01）" required>
<textarea class="form-control mb-2" name="items_json" rows="4" placeholder='items JSON: [{"menu_item_id":1,"quantity":1}]' required></textarea>
<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="pay_now" value="1" id="payNow"><label class="form-check-label" for="payNow">pay_now</label></div>
<input class="form-control mb-2" name="remark" placeholder="备注（可选）">
<button class="btn btn-success w-100">提交预订单</button></form></div></div></div>
<div class="col-12"><div class="card"><div class="card-header">历史预订单（<?= count($preorders ?? []) ?>）</div><div class="card-body"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>ID</th><th>桌号</th><th>状态</th><th>金额</th><th>订单号</th></tr></thead><tbody>
<?php foreach (($preorders ?? []) as $o): ?><tr><td><?= (int)$o['id'] ?></td><td><?= $h($o['table_code']) ?></td><td><?= $h($o['status']) ?></td><td>¥<?= number_format((float)$o['subtotal_amount'],2) ?></td><td><?= $h($o['order_no']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></div>
</div></div>
<script>
document.querySelectorAll('.api-form').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    let items = [];
    try { items = JSON.parse(fd.get('items_json')); } catch { alert('items_json 不是有效 JSON'); return; }
    const payload = {table_code: fd.get('table_code'), items, remark: fd.get('remark') || ''};
    if (fd.get('pay_now')) payload.pay_now = true;
    const res = await fetch(form.dataset.url, {method: form.dataset.method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)});
    const body = await res.json();
    const box = document.getElementById('apiMsg');
    box.innerHTML = `<div class="alert ${res.ok?'alert-success':'alert-danger'}">${res.status} ${JSON.stringify(body)}</div>`;
    if (res.ok) setTimeout(()=>location.reload(), 700);
  });
});
</script>
</body></html>
