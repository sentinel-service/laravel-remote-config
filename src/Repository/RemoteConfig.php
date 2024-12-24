<?php

namespace SentinelService\RemoteConfig\Repository;

use Illuminate\Config\Repository;
use SentinelService\RemoteConfig\Contracts\ConfigCacheInterface;
use SentinelService\RemoteConfig\Contracts\PathMapperInterface;
use SentinelService\RemoteConfig\Services\RemoteConfigService;

class RemoteConfig extends Repository
{
    protected $configService;
    protected $cacheService;
    protected $pathMapper;
    protected $loadedPaths = [];

    public function __construct(
        Repository $config,
        RemoteConfigService $configService,
        ConfigCacheInterface $cacheService,
        PathMapperInterface $pathMapper
    ) {
        parent::__construct($config instanceof Repository ? $config->all() : []);
        
        $this->configService = $configService;
        $this->cacheService = $cacheService;
        $this->pathMapper = $pathMapper;
        
        $this->initializeRemoteConfig();
    }

    protected function initializeRemoteConfig(): void
    {
        $configs = $this->cacheService->get();
        
        if (empty($configs)) {
            $configs = $this->configService->getConfigs();
            
            if (!empty($configs)) {
                $this->cacheService->save($configs);
            }
        }

        if (!empty($configs)) {
            foreach ($this->pathMapper->getPaths() as $path) {
                $this->loadConfigForPath($path, $configs);
            }
        }
    }

    protected function loadConfigForPath(string $path, array $configs): void
    {
        if (empty($configs)) {
            return;
        }

        $mappedConfig = $this->pathMapper->mapConfig($path, $configs);
        if ($mappedConfig !== null) {
            parent::set($path, $mappedConfig);
            $this->loadedPaths[$path] = true;
        }
    }

    public function get($key, $default = null)
    {
        if ($this->pathMapper->shouldLoadFromRemote($key) && !array_key_exists($key, $this->loadedPaths)) {
            $this->loadConfigForPath($key, $this->cacheService->get());
        }

        return parent::get($key, $default);
    }

    public function refreshRemoteConfig(): void
    {
        $configs = $this->configService->getConfigs();
        
        if (!empty($configs)) {
            $this->cacheService->save($configs);
            
            foreach ($this->pathMapper->getPaths() as $path) {
                $this->loadConfigForPath($path, $configs);
            }
        }
    }
}
