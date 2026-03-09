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
   - 客户端桌码预下单：`http://127.0.0.1:8000/client/preorders`
   - 订单支付状态占位：`http://127.0.0.1:8000/api/orders/payments/status`

## 2. 目录结构说明

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
config/                # 应用与数据库配置
.env.example           # 环境变量模板
```

## 3. 主要模块边界

### 3.1 管理端模块（菜单管理）
- 路由：
  - `GET /admin/menus`：菜单列表（示例数据）
  - `POST /admin/menus`：菜单新增占位
- 分层：
  - `AdminMenuController` -> `MenuService` -> `MenuRepository`

### 3.2 客户端模块（桌码预下单）
- 路由：
  - `GET /client/preorders`：预下单列表（示例数据）
  - `POST /client/preorders`：预下单创建占位
- 分层：
  - `ClientPreorderController` -> `PreorderService` -> `PreorderRepository`

### 3.3 订单支付模块（占位）
- 当前仅预留接口和状态流转，不接入支付渠道。
- 路由：
  - `POST /api/orders/payments`：创建支付意图（`pending`）
  - `GET /api/orders/payments/status`：查看支付状态及可流转状态
- 后续可扩展为：
  - 渠道适配层（如微信/支付宝）
  - 回调验签
  - 支付状态机持久化

## 4. 路由与入口

- `public/index.php`：统一入口，加载环境变量、注册 autoload、注入路由并分发请求。
- `routes/web.php`：集中管理页面与 API 路由。

## 5. 配置

- `config/app.php`：应用名、环境、调试开关、基础 URL。
- `config/database.php`：数据库连接参数（读取 `.env`）。

> 说明：当前结构是可运行的最小骨架，未引入第三方框架，便于你后续迁移到 Laravel/Symfony 或继续按此架构扩展。
