<?php
namespace SentinelService\RemoteConfig\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Config\Repository;

class RemoteConfigService
{
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config->get('remote');
    }

    /**
     * 获取所有配置
     */
    public function getConfigs()
    {
        $timestamp = time();
        $query = [
            'timestamp' => $timestamp,
            'sign' => $this->generateSign($timestamp),
        ];

        $url = $this->config['api']['url'];
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        try {
            $response = Http::retry(3, 100)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'x-api-key' => $this->config['api']['secret']
                ])
                ->get($url)
                ->throw()
                ->json('data', []);

            $configs = [];
            foreach ($response as $item) {
                if (array_key_exists('key', $item) && array_key_exists('value', $item)) {
                    $configs[$item['key']] = $item['value'];
                }
            }

            return $configs;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getConfigsByKeys(array $keys)
    {
        $timestamp = time();
        $secret = $this->config['api']['secret'];
        $url = $this->config['api']['url'];

        if (empty($url) || empty($secret)) {
            return [];
        }

        // 如果指定了键，添加到查询参数
        if (!empty($keys)) {
            $query = [];
            foreach ($keys as $key) {
                $query['keys[]'] = $key;
            }
            $url .= '?' . http_build_query($query);
        }

        try {
            $response = Http::retry(
                $this->config['retry']['times'] ?? 3,
                $this->config['retry']['sleep'] ?? 100
            )->withHeaders(['Accept' => 'application/json', 'x-api-key' => $secret])->get($url, [
                'timestamp' => $timestamp,
            ])->throw()->json('data', []);

            // 将响应数据转换为简单的键值对
            $configs = [];
            foreach ($response as $item) {
                if (isset($item['key'], $item['value'])) {
                    $configs[$item['key']] = $item['value'];
                }
            }

            return $configs;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 生成签名
     */
    protected function generateSign($timestamp)
    {
        return hash_hmac('sha256', $timestamp, $this->config['api']['secret']);
    }

    public function refreshConfigs()
    {
        return $this->getConfigs();
    }
}
