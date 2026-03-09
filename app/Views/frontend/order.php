<?php $h = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); ?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>扫码点餐</title>
    <style>
        :root { --brand:#ff5a00; --brand-soft:#fff1e8; --bg:#f5f6fa; --card:#fff; --line:#eceff4; --text:#111827; --muted:#6b7280; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:var(--bg); color:var(--text); }
        .page { max-width:1200px; margin:0 auto; padding:14px; display:grid; gap:14px; grid-template-columns: 1fr; }
        .top { background:linear-gradient(130deg,#ff6f28,#ff8b3d); color:#fff; border-radius:16px; padding:16px; }
        .layout { display:grid; gap:14px; grid-template-columns: 220px 1fr 320px; align-items:start; }
        .card { background:var(--card); border-radius:14px; box-shadow:0 8px 20px rgba(0,0,0,.05); }
        .cats button { width:100%; border:none; border-left:3px solid transparent; background:#fff; padding:12px; text-align:left; cursor:pointer; }
        .cats button.active { border-left-color:var(--brand); background:var(--brand-soft); color:var(--brand); font-weight:700; }
        .menu { padding:10px; }
        .item { display:grid; grid-template-columns: 1fr auto; gap:10px; border-bottom:1px solid var(--line); padding:10px 2px; }
        .item h4 { margin:0 0 4px; }
        .tag { font-size:12px; color:var(--muted); }
        .qty { display:flex; align-items:center; gap:6px; }
        .qty button { width:26px; height:26px; border:none; border-radius:50%; background:var(--brand-soft); color:var(--brand); font-size:18px; cursor:pointer; }
        .cart { padding:12px; position:sticky; top:10px; }
        .cart-row { display:flex; justify-content:space-between; margin:8px 0; font-size:14px; }
        .submit { width:100%; border:none; background:var(--brand); color:#fff; border-radius:10px; padding:12px; font-weight:700; cursor:pointer; }
        .empty { color:var(--muted); text-align:center; padding:18px 0; }
        @media (max-width: 960px) { .layout { grid-template-columns:1fr; } .cart { position:static; } }
    </style>
</head>
<body>
<div class="page">
    <header class="top">
        <h1 style="margin:0;">🍽️ 自助点餐 /frontend/order</h1>
        <div style="margin-top:6px;">桌码：<strong id="tableCodeText"><?= $h($tableCode ?: '未传入') ?></strong> · <a href="/frontend/" style="color:#fff;">前端首页</a></div>
    </header>

    <section class="layout">
        <aside class="card cats" id="categoryList"></aside>
        <main class="card menu" id="menuList"></main>
        <aside class="card cart">
            <h3 style="margin-top:0;">购物车</h3>
            <div id="cartItems"></div>
            <hr style="border:none;border-top:1px solid var(--line);">
            <div class="cart-row"><span>小计</span><strong id="subtotal">¥0.00</strong></div>
            <textarea id="remark" placeholder="口味备注（如：少辣少盐）" style="width:100%;min-height:78px;border:1px solid var(--line);border-radius:10px;padding:8px;"></textarea>
            <button class="submit" id="submitOrderBtn">提交订单</button>
            <div id="msg" class="tag" style="margin-top:8px;"></div>
        </aside>
    </section>
</div>

<script>
const tableCode = new URLSearchParams(location.search).get('table_code') || '<?= $h($tableCode) ?>';
const state = { menu: [], categoryIndex: 0, cart: {} };

function money(value) { return `¥${Number(value).toFixed(2)}`; }

async function validateTable() {
    if (!tableCode) {
        document.getElementById('msg').textContent = '请传入 table_code 后点餐。';
        return false;
    }
    const res = await fetch(`/client/table/validate?table_code=${encodeURIComponent(tableCode)}&format=json`);
    const data = await res.json();
    if (!res.ok) {
        document.getElementById('msg').textContent = data.message || '桌码校验失败';
        return false;
    }
    return true;
}

async function loadMenu() {
    const res = await fetch('/api/menus?format=json');
    const data = await res.json();
    state.menu = data.menu || [];
    renderCategories();
    renderMenu();
    renderCart();
}

function renderCategories() {
    const list = document.getElementById('categoryList');
    list.innerHTML = state.menu.map((cat, index) => `<button class="${index===state.categoryIndex?'active':''}" onclick="chooseCategory(${index})">${cat.name}</button>`).join('');
}

function chooseCategory(index) {
    state.categoryIndex = index;
    renderCategories();
    renderMenu();
}

function changeQty(itemId, step) {
    state.cart[itemId] = Math.max(0, (state.cart[itemId] || 0) + step);
    if (state.cart[itemId] === 0) delete state.cart[itemId];
    renderMenu();
    renderCart();
}

function renderMenu() {
    const container = document.getElementById('menuList');
    const cat = state.menu[state.categoryIndex];
    if (!cat) {
        container.innerHTML = '<div class="empty">暂无可售菜品</div>';
        return;
    }

    container.innerHTML = `<h3 style="margin:4px 0 10px;">${cat.name}</h3>` + cat.items.map(item => `
        <div class="item">
            <div>
                <h4>${item.name}</h4>
                <div class="tag">${item.description || '招牌菜，建议趁热食用'}</div>
                <div style="margin-top:6px;color:#ff5a00;font-weight:700;">${money(item.price)}</div>
            </div>
            <div class="qty">
                <button onclick="changeQty(${item.id}, -1)">-</button>
                <strong>${state.cart[item.id] || 0}</strong>
                <button onclick="changeQty(${item.id}, 1)">+</button>
            </div>
        </div>
    `).join('');
}

function renderCart() {
    const lines = [];
    let total = 0;

    state.menu.forEach(cat => (cat.items || []).forEach(item => {
        const qty = state.cart[item.id] || 0;
        if (!qty) return;
        const amount = qty * Number(item.price);
        total += amount;
        lines.push(`<div class="cart-row"><span>${item.name} × ${qty}</span><strong>${money(amount)}</strong></div>`);
    }));

    document.getElementById('cartItems').innerHTML = lines.length ? lines.join('') : '<div class="empty">购物车为空</div>';
    document.getElementById('subtotal').textContent = money(total);
}

async function submitOrder() {
    const items = Object.entries(state.cart).map(([menuItemId, quantity]) => ({menu_item_id: Number(menuItemId), quantity}));
    if (!items.length) {
        document.getElementById('msg').textContent = '请先选择菜品';
        return;
    }

    const payload = { table_code: tableCode, items, remark: document.getElementById('remark').value.trim(), pay_now: false };
    const res = await fetch('/client/preorders/submit', {
        method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload)
    });
    const data = await res.json();
    document.getElementById('msg').textContent = res.ok ? `下单成功，订单号：${data.data.order_no}` : (data.message || '提交失败');
    if (res.ok) {
        state.cart = {};
        renderMenu();
        renderCart();
    }
}

document.getElementById('submitOrderBtn').addEventListener('click', submitOrder);

(async function init() {
    const pass = await validateTable();
    if (!pass) return;
    document.getElementById('tableCodeText').textContent = tableCode;
    await loadMenu();
})();
</script>
</body>
</html>
