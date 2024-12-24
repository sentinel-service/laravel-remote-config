<?php

namespace SentinelService\RemoteConfig;

use Illuminate\Support\ServiceProvider;
use SentinelService\RemoteConfig\Repository\RemoteConfig;
use SentinelService\RemoteConfig\Services\RemoteConfigService;
use SentinelService\RemoteConfig\Services\ConfigCacheService;
use SentinelService\RemoteConfig\Services\PathConfigMapper;
use SentinelService\RemoteConfig\Contracts\ConfigCacheInterface;
use SentinelService\RemoteConfig\Contracts\PathMapperInterface;

class RemoteConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // 注册配置文件
        $this->mergeConfigFrom(__DIR__.'/../config/remote.php', 'remote');

        // 注册远程配置服务
        $this->app->singleton(RemoteConfigService::class);
        
        // 注册配置缓存服务
        $this->app->singleton(ConfigCacheInterface::class, ConfigCacheService::class);
        
        // 注册路径映射服务
        $this->app->singleton(PathMapperInterface::class, PathConfigMapper::class);

        // 注册远程配置实例
        $this->app->singleton('remote.config', function ($app) {
            return new RemoteConfig(
                $app['config'],
                $app[RemoteConfigService::class],
                $app[ConfigCacheInterface::class],
                $app[PathMapperInterface::class]
            );
        });

        // 扩展配置系统
        if (!$this->app->configurationIsCached()) {
            $this->app->extend('config', function ($config, $app) {
                return $app['remote.config'];
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // 发布配置文件
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/remote.php' => config_path('remote.php'),
            ], 'remote-config');
        }
    }
}
