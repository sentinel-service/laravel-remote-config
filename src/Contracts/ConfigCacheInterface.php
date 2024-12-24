<?php

namespace SentinelService\RemoteConfig\Contracts;

interface ConfigCacheInterface
{
    /**
     * 获取缓存的配置
     */
    public function get(): array;

    /**
     * 保存配置到缓存
     */
    public function save(array $config): void;

    /**
     * 清除缓存
     */
    public function clear(): void;

    /**
     * 检查缓存是否有效
     */
    public function isValid(): bool;
}
