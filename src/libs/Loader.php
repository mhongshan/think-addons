<?php
declare(strict_types=1);

namespace mhs\think\libs;

use mhs\think\Addons;

class Loader
{
    /**
     * @var Addons
     */
    protected $addons;

    public function __construct(Addons $addons)
    {
        $this->addons = $addons;
    }

    /**
     * 注册自动加载
     * @param string $class
     */
    public static function autoload($class)
    {
        $class = ltrim($class, '\\');
        $namespace = 'addons';
        if (strpos($class, $namespace) !== 0) {
            return false;
        }
        // 获取addons path
        $addonsPath = app()->addons->getPath()->getAddonsPath();
        $class = substr($class, strlen($namespace));
        $class = str_replace('_', '-', $class);
        $file = strtr($addonsPath . $class, '\\', '/') . '.php';
        if (file_exists($file)) {
            include $file;
            return true;
        }
        return false;
    }

    /**
     * 加载插件
     * @return bool
     */
    public function loadAddons()
    {
        if (!$this->addons->getConfigure()->get('autoload', true)) {
            return false;
        }
        $path = $this->addons->getPath();
        $paths = ['apps' => $path->getAppsPath(), 'plugins' => $path->getPluginsPath()];
        foreach ($paths as $type => $dir) {
            foreach (glob($dir . '*') as $file) {
                if (!is_dir($file) || !$this->addons->checkAddon($file)) {
                    continue;
                }
                $this->loadConfig($file . '/config.php');
                $this->loadRoute($type, basename($file), $file . '/route.php');
            }
        }

        return true;
    }

    /**
     * @param $file
     */
    public function loadConfig($file)
    {
        if (!file_exists($file)) {
            return;
        }
        $config = include $file;
        $this->addons->getConfigure()->addHooks($config['hooks'] ?? []);
        $this->addons->getConfigure()->addBinds($config['binds'] ?? []);
        $this->addons->getConfigure()->addServices($config['services'] ?? []);
    }

    /**
     * @param $type
     * @param $name
     * @param $file
     */
    public function loadRoute($type, $name, $file)
    {
        if (!file_exists($file)) {
            return;
        }
        $routes = include $file;
        $this->addons->getConfigure()->addRoutes($type, $name, $routes ?: []);
    }
}