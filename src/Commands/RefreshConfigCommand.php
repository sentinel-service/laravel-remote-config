<?php
namespace SentinelService\RemoteConfig\Commands;

use Illuminate\Console\Command;
use SentinelService\RemoteConfig\Repository\RemoteConfig;

class RefreshConfigCommand extends Command
{
    protected $signature = 'remote-config:refresh';
    protected $description = 'Refresh remote configurations';

    protected $remoteConfig;

    public function __construct(RemoteConfig $remoteConfig)
    {
        parent::__construct();
        $this->remoteConfig = $remoteConfig;
    }

    public function handle()
    {
        try {
            $this->remoteConfig->refreshRemoteConfig();
            $this->info('Remote configurations refreshed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to refresh remote configurations: ' . $e->getMessage());
            return 1;
        }
    }
}
