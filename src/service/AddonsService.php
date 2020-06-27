<?php
declare(strict_types=1);

namespace mhs\think\service;

use mhs\think\Addons;
use mhs\think\command\Build;
use mhs\think\command\SendConfig;
use think\facade\Lang;
use think\Route;
use think\Service;

class AddonsService extends Service
{
    public function register()
    {
        $lang = Lang::getLangSet();
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        file_exists($file) && Lang::load($file);
        $this->app->bind('addons', Addons::class);
        /** @var Addons $addons */
        $addons = $this->app->addons;
        $addons->getPath()->check(); // 检查目录
        $addons->getLoader()->loadAddons(); // 加载插件
        $addons->listenEvent();
        $addons->bind();
        $addons->registerServices();
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            app()->addons->getRouter()->register($route);
        });
        $this->commands([
            SendConfig::class,
            Build::class
        ]);
    }
}