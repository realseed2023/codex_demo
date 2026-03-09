<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html><html lang="zh-CN"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>桌码校验</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body><div class="container py-4" style="max-width:720px;">
<h1 class="h4 mb-3">桌码校验 /client/table/validate</h1>
<form method="get" class="row g-2 mb-3"><div class="col-8"><input class="form-control" name="table_code" placeholder="输入桌码，如 A01" value="<?= $h($tableCode ?? '') ?>"></div><div class="col-4"><button class="btn btn-primary w-100">校验</button></div></form>
<?php if (!empty($error ?? '')): ?><div class="alert alert-danger"><?= $h($error) ?></div><?php endif; ?>
<?php if (!empty($result ?? [])): ?><pre class="bg-light border rounded p-3 small"><?= $h(json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) ?></pre><?php endif; ?>
<a class="btn btn-outline-secondary btn-sm" href="/client/preorders">返回预下单页面</a>
</div></body></html>
