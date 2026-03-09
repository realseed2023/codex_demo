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
   - 订单支付状态占位：`http://127.0.0.1:8000/api/orders/payments/status`

## 2. 菜单管理最小可用能力

### 2.1 数据模型字段
- `categories`：`id`, `name`, `sort`, `status`, `created_at`, `updated_at`
- `menu_items`：`id`, `category_id`, `name`, `description`, `price`, `image_url`, `status`, `stock`, `created_at`, `updated_at`

> 当前以 `storage/menu_data.json` 持久化模拟数据表。

### 2.2 管理端接口
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

### 2.3 对外菜单读取接口
- `GET /api/menus`
- 仅返回启用分类下、且 `on_sale` 且库存大于 0 的菜品
- 按分类聚合，按分类 `sort` 排序

### 2.4 基础校验
- 价格必须为非负数
- 分类与菜品名称必填
- 下架或不可售（库存<=0）菜品不可加入预下单

## 3. 目录结构说明

```text
public/                # 统一入口（index.php）
routes/                # 路由定义（web.php）
app/
  Controllers/         # 控制器层
  Services/            # 业务服务层
  Repositories/        # 数据访问层
  Models/              # 领域模型
  Views/               # 视图模板（服务端渲染）
  Core/                # 路由器、autoload、通用 helper
storage/               # 轻量 JSON 持久化
config/                # 应用与数据库配置
.env.example           # 环境变量模板
```
