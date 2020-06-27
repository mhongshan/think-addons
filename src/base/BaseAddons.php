<?php
declare(strict_types=1);

namespace mhs\think\base;

use think\App;

abstract class BaseAddons implements IAddons
{
    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }
}