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
