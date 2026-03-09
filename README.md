# codex_demo

一个可运行的 PHP 基础项目骨架，采用轻量分层 / MVC 风格，便于后续按业务模块扩展。

## 1. 快速启动

### 环境要求
- PHP >= 8.1

### 启动步骤
1. 复制环境变量文件：
   ```bash
   cp .env.example .env
   ```
2. 在仓库根目录启动内置 Web 服务：
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
3. 访问：
   - 首页：`http://127.0.0.1:8000/`
   - 管理端菜单模块：`http://127.0.0.1:8000/admin/menus`
   - 对外菜单接口：`http://127.0.0.1:8000/api/menus`
   - 客户端桌码预下单：`http://127.0.0.1:8000/client/preorders`

## 2. 菜单管理最小可用能力

### 2.1 数据模型字段
- `categories`：`id`, `name`, `sort`, `status`, `created_at`, `updated_at`
- `menu_items`：`id`, `category_id`, `name`, `description`, `price`, `image_url`, `status`, `stock`, `created_at`, `updated_at`

### 2.2 数据存储模式（新增）
通过 `DB_DRIVER` 配置切换存储实现：
- `DB_DRIVER=json`：使用 `storage/menu_data.json` 和 `storage/preorders.json`
- `DB_DRIVER=mysql`：使用 MySQL（Repository 会自动建表/初始化）

MySQL 主要表：
- `categories`
- `menu_items`
- `preorders`（`items` 字段为 JSON）
- `payment_records`（预留支付流水：`order_id`,`channel`,`out_trade_no`,`amount`,`status`,`raw_payload`）

### 2.3 管理端接口
- 分类：
  - `POST /admin/categories` 新增
  - `PUT /admin/categories?id=1` 编辑（支持名称、排序、启用/禁用）
  - `DELETE /admin/categories?id=1` 删除
- 菜品：
  - `POST /admin/menu-items` 新增
  - `PUT /admin/menu-items?id=1` 编辑（支持上下架、库存调整）
  - `DELETE /admin/menu-items?id=1` 删除
- 查询：
  - `GET /admin/menus` 查看分类与菜品

### 2.4 对外菜单读取接口
- `GET /api/menus`
- 仅返回启用分类下、且 `on_sale` 且库存大于 0 的菜品
- 按分类聚合，按分类 `sort` 排序

### 2.5 基础校验
- 价格必须为非负数
- 分类与菜品名称必填
- 下架或不可售（库存<=0）菜品不可加入预下单


## 3. 桌码预下单闭环（扫码/输入桌码 -> 选菜 -> 提交）

### 3.1 数据模型
- `tables`：`id`, `table_code`, `name`, `status`
- `pre_orders`：`id`, `table_id`, `order_no`, `status`, `subtotal_amount`, `remark`, `created_at`, `updated_at`
- `pre_order_items`：`id`, `pre_order_id`, `menu_item_id`, `item_name_snapshot`, `unit_price_snapshot`, `quantity`, `line_amount`

### 3.2 客户端接口
- `GET /client/table/validate?table_code=A01`：校验桌码有效性
- `GET /client/menu`：获取可售菜单
- `POST /client/cart`：创建/更新购物车（草稿单）
  - 支持传 `pre_order_id` 更新草稿
- `POST /client/preorders/submit`：提交预订单
  - 支持 `pay_now`（默认 `false`）
  - 当前版本默认“稍后支付”，订单流转到 `submitted`

### 3.3 关键约束
- 提交前会校验桌码状态，`inactive` 或不存在桌码禁止下单
- 下单行项目写入菜品名称和价格快照（`item_name_snapshot` / `unit_price_snapshot`）
- 下单数量必须为正整数


### 3.4 订单状态机（预留支付扩展）
`draft` -> `submitted` -> `pending_payment` -> `paid` -> `confirmed` -> `completed` / `cancelled`

### 3.5 支付抽象层（占位）
- `app/Services/Payment/PaymentGatewayInterface.php`
  - `createPaymentOrder(array $payload): array`
  - `queryPaymentStatus(string $outTradeNo): array`
  - `handleCallback(array $payload): array`
- 当前由 `NullPaymentGateway` 返回 `implemented=false` 作为未实现占位


## 4. 使用 GitHub Actions 临时预览（无需自建服务器）

可以通过仓库内置工作流 `.github/workflows/temporary-preview.yml`，利用 GitHub Actions 的临时 runner 启动 PHP 服务，并通过 Cloudflare 临时隧道提供可访问链接。

### 使用方法
1. 将代码推送到 GitHub 仓库。
2. 打开仓库 `Actions` 页面，选择 **Temporary PHP Preview** 工作流。
3. 点击 **Run workflow**，可选填写 `duration_minutes`（1-120，默认 30）。
4. 工作流启动后，在该次运行的 **Summary** 中查看临时预览 URL（`https://xxxx.trycloudflare.com`）。
5. 如果是 PR 触发（opened/synchronize/reopened），工作流会自动在 PR 下创建或更新评论，贴出最新预览链接。

### 注意事项
- 这是临时预览地址，仅在工作流运行期间有效。
- PR 自动评论会覆盖更新为最新一次运行产生的链接（避免同一个 PR 出现多条旧链接）。
- 工作流结束后链接失效，需要重新触发工作流获取新地址。
- 该方案适合演示与验收，不建议直接作为生产部署。
