<?php
declare(strict_types=1);

namespace mhs\think;

use mhs\think\libs\Configure;
use mhs\think\libs\Loader;
use mhs\think\libs\Path;
use mhs\think\libs\Router;
use think\App;
use think\facade\Config;
use think\facade\Event;

class Addons
{
    /**
     * @var App
     */
    protected $app;
    /**
     * @var Configure
     */
    protected $configure;
    /**
     * @var Path
     */
    protected $path;
    /**
     * @var Loader
     */
    protected $loader;
    /**
     * @var Router
     */
    protected $router;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->configure = new Configure(Config::get('addons', []));
        $this->path = new Path($this);
        $this->loader = new Loader($this);
        $this->router = new Router($this);
    }

    /**
     * @return Configure
     */
    public function getConfigure(): Configure
    {
        return $this->configure;
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
    }

    /**
     * @return Loader
     */
    public function getLoader(): Loader
    {
        return $this->loader;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * 检查插件
     * @param string $path
     * @return bool
     */
    public function checkAddon($path)
    {
        $infoFile = $path . '/info.php';
        if (!file_exists($infoFile)) {
            return false;
        }
        $info = include $infoFile;
        if ($info['status'] != 1) { // 插件未启用, 0:未安装,1:已安装已启用, 2:已安装未启用
            return false;
        }

        return true;
    }

    /**
     * 监听事件
     */
    public function listenEvent()
    {
        $hooks = $this->configure->get('hooks', []);
        Event::listenEvents($hooks);
        if (isset($hooks['AddonsInit'])) {
            foreach ($hooks['AddonsInit'] as $event) {
                Event::trigger($event);
            }
        }
    }

    /**
     * 绑定
     */
    public function bind()
    {
        foreach ($this->configure->get('binds', []) as $bind => $value) {
            app()->bind($bind, $value);
        }
    }

    /**
     * 注册服务
     */
    public function registerServices()
    {
        foreach ($this->configure->get('services', []) as $service) {
            app()->register($service);
        }
    }
}