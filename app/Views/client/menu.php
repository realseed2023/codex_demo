<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>客户端菜单</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container py-4"><h1 class="h4 mb-3">客户端可售菜单 /client/menu</h1>
<?php foreach (($menu ?? []) as $c): ?><div class="card mb-2"><div class="card-header"><?= $h($c['name']) ?></div><ul class="list-group list-group-flush"><?php foreach (($c['items'] ?? []) as $i): ?><li class="list-group-item d-flex justify-content-between"><span><?= $h($i['name']) ?></span><span>¥<?= number_format((float)$i['price'],2) ?></span></li><?php endforeach; ?></ul></div><?php endforeach; ?>
</div></body></html>
