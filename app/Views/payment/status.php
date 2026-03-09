<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html><html lang="zh-CN"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>支付接口联调</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">支付接口联调</h1><a href="/" class="btn btn-sm btn-outline-secondary">首页</a></div>
<div id="apiMsg" class="small"></div>
<div class="row g-3">
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">查询状态 /api/orders/payments/status</div><div class="card-body">
<form method="get" class="row g-2 mb-2"><div class="col-8"><input class="form-control" name="out_trade_no" placeholder="输入 out_trade_no" value="<?= $h($outTradeNo ?? '') ?>"></div><div class="col-4"><button class="btn btn-primary w-100">查询</button></div></form>
<pre class="bg-light border rounded p-2 small mb-0"><?= $h(json_encode($status ?? [], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) ?></pre>
</div></div></div>
<div class="col-12 col-lg-6"><div class="card"><div class="card-header">创建支付意图 /api/orders/payments</div><div class="card-body">
<form class="api-form" data-url="/api/orders/payments" data-method="POST">
<input class="form-control mb-2" type="number" name="order_id" placeholder="order_id" required>
<input class="form-control mb-2" type="number" step="0.01" name="amount" placeholder="amount" required>
<input class="form-control mb-2" name="channel" placeholder="channel (wxpay/alipay)" value="wxpay">
<button class="btn btn-primary w-100">创建支付意图</button></form>
</div></div></div>
<div class="col-12"><div class="card"><div class="card-header">支付回调模拟 /api/orders/payments/callback</div><div class="card-body">
<form class="api-form" data-url="/api/orders/payments/callback" data-method="POST">
<textarea class="form-control mb-2" rows="4" name="callback_json" placeholder='{"out_trade_no":"demo","status":"paid"}' required></textarea>
<button class="btn btn-outline-primary">发送回调</button></form>
</div></div></div>
</div>
</div>
<script>
document.querySelectorAll('.api-form').forEach(form => form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(form);
  let payload = Object.fromEntries(fd.entries());
  if (payload.callback_json) {
    try { payload = JSON.parse(payload.callback_json); } catch { alert('callback_json 不是有效 JSON'); return; }
  }
  const res = await fetch(form.dataset.url, {method: form.dataset.method, headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
  const body = await res.json();
  document.getElementById('apiMsg').innerHTML = `<div class="alert ${res.ok?'alert-success':'alert-danger'}">${res.status} ${JSON.stringify(body)}</div>`;
}));
</script>
</body></html>
