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

class Build extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('addons:build')
            ->addArgument('type', Argument::REQUIRED, 'addons type: apps:独立应用 plugins:功能增强')
            ->addOption('name', 'N', Option::VALUE_REQUIRED, 'addon name')
            ->setDescription('build an addon');
    }

    protected function execute(Input $input, Output $output)
    {
    	$type = $input->getArgument('type');
    	$name = $input->getOption('name');
    	$path = app()->addons->getPath();
    	if (!in_array($type, ['apps', 'plugins'])) {
    	    $output->error('type error: allow apps and plugins');
    	    return;
        }
    	$dir = $type == 'apps' ? $path->getAppsPath() : $path->getPluginsPath();
    	$name = str_replace(['_', '/', '\\'], '-',$name);
    	dump($name);
    	if (is_dir($dir.$name)) {
    	    $output->error('addon ['.$name.'] already exits');
    	    return;
        }
    	$dir .= $name;
    	mkdir($dir, 0755, true);
    	// 复制文件
        $source = __DIR__.'/build/';
        copy($source.'/config.php', $dir.'/config.php');
        copy($source.'/info.php', $dir.'/info.php');
        copy($source.'/route.php', $dir.'/route.php');
        copy($source.'/Plugin.stub', $dir.'/Plugin.php');
        $search = ['{name}','{type}', '{name_str}'];
        $replace = [$name, $type, str_replace('-', '_', $name)];
        file_put_contents($dir.'/info.php', str_replace($search, $replace, file_get_contents($dir.'/info.php')));
        file_put_contents($dir.'/Plugin.php', str_replace($search, $replace, file_get_contents($dir.'/Plugin.php')));

        $output->writeln('build addon '.$name.' success');
    }
}
