<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Remote Config Settings
    |--------------------------------------------------------------------------
    |
    | 这里配置远程配置服务的设置
    |
    */

    // API 配置
    'api' => [
        'url' => env('REMOTE_CONFIG_API_URL', 'http://localhost/api/v1/configs'),
        'secret' => env('REMOTE_CONFIG_SECRET'),
    ],

    // 缓存配置
    'cache' => [
        'ttl' => env('REMOTE_CONFIG_CACHE_TTL', 3600), // 默认缓存1小时
    ],

    // 需要从远程获取配置的路径
    'paths' => [
        'filesystems.disks.r2',
        'filesystems.disks.s3',
        // 其他需要远程获取的路径
    ],
     // 路径与环境变量的映射关系
     'path_env_mapping' => [
        'filesystems.disks.r2' => [
            'key' => 'CLOUDFLARE_R2_ACCESS_KEY_ID',
            'secret' => 'CLOUDFLARE_R2_SECRET_ACCESS_KEY',
            'bucket' => 'CLOUDFLARE_R2_BUCKET',
            'endpoint' => 'CLOUDFLARE_R2_ENDPOINT',
            'url' => 'CLOUDFLARE_R2_URL',
        ],
        'filesystems.disks.s3' => [
            'key' => 'AWS_ACCESS_KEY_ID',
            'secret' => 'AWS_SECRET_ACCESS_KEY',
            'region' => 'AWS_DEFAULT_REGION',
            'bucket' => 'AWS_BUCKET',
            'url' => 'AWS_URL',
            'endpoint' => 'AWS_ENDPOINT',
            'use_path_style_endpoint' => 'AWS_USE_PATH_STYLE_ENDPOINT',
        ],
        // 单个值的例子
        'app.name' => [
            'value' => 'APP_NAME'
        ],
        // 可以添加更多映射...
    ],

];
