<?php

namespace Qihucms\PublishVideo\Commands;

use App\Plugins\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publish-video:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install publish video and precess plugin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $plugin = new Plugin();

        if ($this->installed()) {
            $this->info('Plugin table already exists');
        } else {
            // 缓存版本
            $plugin->setPluginVersion('publish-video', 100);

            $this->info('Install success');
        }
    }

    // 是否安装过
    protected function installed()
    {
        $plugin = new Plugin();
        // 验证表是否存在
        return class_exists('Qihucms\\PublishVideo\\PublishVideoServiceProvider') && $plugin->getPluginVersion('publish-video') == 100;
    }
}
