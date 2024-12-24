<?php

namespace SentinelService\RemoteConfig\Contracts;

interface PathMapperInterface
{
    /**
     * 检查是否需要从远程加载配置
     */
    public function shouldLoadFromRemote(string $path): bool;

    /**
     * 根据路径映射处理配置
     */
    public function mapConfig(string $path, array $configs): mixed;

    /**
     * 获取所有需要远程加载的路径
     */
    public function getPaths(): array;
}
