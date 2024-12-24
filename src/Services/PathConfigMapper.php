<?php

namespace SentinelService\RemoteConfig\Services;

use SentinelService\RemoteConfig\Contracts\PathMapperInterface;
use Illuminate\Contracts\Config\Repository;

class PathConfigMapper implements PathMapperInterface
{
    protected $config;
    protected $paths = [];
    protected $mappings = [];

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->paths = $config->get('remote.paths', []);
        $this->mappings = $config->get('remote.path_env_mapping', []);
    }

    public function shouldLoadFromRemote(string $path): bool
    {
        return in_array($path, $this->paths);
    }

    public function mapConfig(string $path, array $configs): mixed
    {
        if (!array_key_exists($path, $this->mappings)) {
            return null;
        }

        $pathMapping = $this->mappings[$path];
        $originalConfig = $this->config->get($path);

        if (is_array($originalConfig)) {
            foreach ($pathMapping as $key => $envKey) {
                if (array_key_exists($envKey, $configs) && array_key_exists($key, $originalConfig)) {
                    $originalConfig[$key] = $configs[$envKey];
                }
            }
        } else {
            if (array_key_exists('value', $pathMapping)) {
                $envKey = $pathMapping['value'];
                if (array_key_exists($envKey, $configs)) {
                    $originalConfig = $configs[$envKey];
                }
            }
        }

        return $originalConfig;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }
}
