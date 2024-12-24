<?php

namespace SentinelService\RemoteConfig\Services;

use SentinelService\RemoteConfig\Contracts\ConfigCacheInterface;
use Illuminate\Contracts\Config\Repository;

class ConfigCacheService implements ConfigCacheInterface
{
    protected $config;
    protected $cacheFile;
    protected $ttl;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->cacheFile = storage_path('framework/cache/data/remote-config.json');
        $this->ttl = $config->get('remote.cache.ttl', 3600);
    }

    public function get(): array
    {
        if (!file_exists($this->cacheFile)) {
            return [];
        }

        $content = file_get_contents($this->cacheFile);
        $cache = json_decode($content, true);

        if (!array_key_exists('expires_at', $cache) || !array_key_exists('data', $cache)) {
            return [];
        }

        if (time() > $cache['expires_at']) {
            return [];
        }

        return $cache['data'];
    }

    public function save(array $configs): void
    {
        if (!file_exists(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0755, true);
        }

        $cache = [
            'expires_at' => time() + $this->ttl,
            'data' => $configs
        ];

        file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    }

    public function clear(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function isValid(): bool
    {
        if (!file_exists($this->cacheFile)) {
            return false;
        }

        $content = file_get_contents($this->cacheFile);
        $cache = json_decode($content, true);

        return array_key_exists('expires_at', $cache) 
            && array_key_exists('data', $cache) 
            && time() <= $cache['expires_at'];
    }
}
