<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html><html lang="zh-CN"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>对外菜单</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">对外菜单</h1><a href="/" class="btn btn-sm btn-outline-secondary">首页</a></div>
<?php foreach (($categories ?? []) as $category): ?>
<div class="card mb-3"><div class="card-header"><?= $h($category['name'] ?? '-') ?></div><ul class="list-group list-group-flush">
<?php foreach (($category['items'] ?? []) as $item): ?>
<li class="list-group-item d-flex justify-content-between align-items-center">
<div><strong><?= $h($item['name'] ?? '-') ?></strong><div class="small text-muted"><?= $h($item['description'] ?? '') ?></div></div>
<span class="badge text-bg-primary">¥<?= number_format((float) ($item['price'] ?? 0), 2) ?></span></li>
<?php endforeach; ?>
</ul></div>
<?php endforeach; ?>
</div></body></html>
