# Laravel Remote Config

Laravel 远程配置管理包。此扩展包允许你从中央配置服务器存储和获取配置值，支持加密、缓存，并与 Laravel 的配置系统无缝集成。

## 功能特性

- 与 Laravel 配置系统无缝集成
- 支持配置值加密
- 自动缓存（可配置缓存时间）
- 请求失败重试机制
- 配置刷新命令行工具
- 本地配置值回退支持
- 符合 PSR-4 规范
- 支持路径映射配置
- 文件缓存系统

## 环境要求

- PHP ^7.4|^8.0
- Laravel ^8.0|^9.0|^10.0|^11.0
- Guzzle ^7.0

## 安装

通过 composer 安装扩展包：

```bash
composer require sentinel-service/laravel-remote-config
```

## 配置

发布配置文件：
```bash
php artisan vendor:publish --provider="SentinelService\RemoteConfig\RemoteConfigServiceProvider"
```

这将在 config 目录下创建 remote.php 配置文件。在 .env 文件中添加以下环境变量：
```dotenv
REMOTE_CONFIG_API_URL=http://your-config-api.com
REMOTE_CONFIG_SECRET=your-secret-key
REMOTE_CONFIG_CACHE_TTL=3600
```

## 配置选项

```php
return [
    // API 配置
    'api' => [
        'url' => env('REMOTE_CONFIG_API_URL'),
        'secret' => env('REMOTE_CONFIG_SECRET'),
    ],
    
    // 缓存设置
    'cache' => [
        'ttl' => env('REMOTE_CONFIG_CACHE_TTL', 3600) // 缓存时间（秒），默认1小时
    ],
    
    // 需要从远程获取的配置路径
    'paths' => [
        'aws.key',
        'services.r2'
    ],
    
    // 路径到环境变量的映射
    'path_env_mapping' => [
        'aws.key' => [
            'key' => 'AWS_ACCESS_KEY_ID',
            'secret' => 'AWS_SECRET_ACCESS_KEY'
        ],
        'services.r2' => [
            'key' => 'R2_ACCESS_KEY_ID',
            'secret' => 'R2_SECRET_ACCESS_KEY'
        ],
        // 单个值的例子
        'app.name' => [
            'value' => 'APP_NAME'
        ],
    ]
];
```

## 使用方法

### 基本用法
安装后，你可以直接使用 Laravel 的 config() 辅助函数。扩展包会自动尝试从远程服务器获取配置值：

```php
// 优先从远程获取配置，如果失败则使用本地配置
$value = config('aws.key');
```

### 路径映射
你可以在配置文件中定义路径映射，将远程配置值映射到指定的配置路径：

```php
'path_env_mapping' => [
    'aws.key' => [
        'key' => 'AWS_ACCESS_KEY_ID',
        'secret' => 'AWS_SECRET_ACCESS_KEY'
    ]
]
```

这样，当访问 `config('aws.key')` 时，扩展包会：
1. 从远程服务器获取配置
2. 根据映射关系更新配置值
3. 缓存结果

### 刷新配置
你可以使用提供的 Artisan 命令刷新远程配置：
```bash
php artisan remote-config:refresh
```

### 缓存机制
扩展包使用文件缓存系统存储远程配置：
- 缓存文件位置：`storage/framework/cache/data/remote-config.json`
- 缓存时间可通过 `REMOTE_CONFIG_CACHE_TTL` 环境变量配置
- 支持自动过期和刷新

### 错误处理
扩展包会优雅地处理各种错误情况：
- 远程服务器不可用时使用本地配置
- 缓存无效时自动刷新
- 配置映射错误时保持原值

## 安全性
在存储敏感信息（如 API 密钥）时，请确保：

1. 使用 HTTPS 协议访问远程配置 API
2. 妥善保管 REMOTE_CONFIG_SECRET
3. 使用环境变量存储敏感信息
4. 定期更换密钥和令牌

## 架构说明

扩展包采用模块化设计：

- `Repository/RemoteConfig`: 核心配置仓库，继承自 Laravel 的 Repository
- `Services/RemoteConfigService`: 处理远程配置获取
- `Services/ConfigCacheService`: 管理配置缓存
- `Services/PathConfigMapper`: 处理配置路径映射
- `Commands/RefreshConfigCommand`: 提供配置刷新命令
- `Contracts`: 定义接口规范

这种设计确保了：
- 高内聚低耦合
- 易于测试和维护
- 符合 SOLID 原则
- 灵活的扩展性