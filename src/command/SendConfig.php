<?php
declare (strict_types = 1);

namespace mhs\think\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\helper\Arr;

class SendConfig extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('addons:send_config')
            ->setDescription('addons send config');
    }

    protected function execute(Input $input, Output $output)
    {
    	// 获取当前配置
        $config = Config::get('addons', []);
        $defaultConfig = include __DIR__.'/../config.php';
        $config = array_merge($defaultConfig, $config);
        $path = app()->getConfigPath();
        file_put_contents($path.'addons.php', "<?php\n\rreturn ".var_export($config, true).';');
    	$output->writeln('send config success');
    }
}
