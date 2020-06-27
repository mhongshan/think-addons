<?php
declare(strict_types=1);

namespace mhs\think\libs;

use mhs\think\Addons;

class Path
{
    /**
     * @var Addons
     */
    protected $addons;
    /**
     * @var string
     */
    protected $addonsPath = '';
    /**
     * @var string
     */
    protected $appsPath = '';
    /**
     * @var string
     */
    protected $pluginsPath = '';

    public function __construct(Addons $addons)
    {
        $this->addons = $addons;
    }

    /**
     * 检查目录
     */
    public function check()
    {
        $paths = [
            $this->getAddonsPath(),
            $this->getAppsPath(),
            $this->getPluginsPath()
        ];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * @return string
     */
    public function getAddonsPath(): string
    {
        if (!$this->addonsPath) {
            $addonsName = $this->addons->getConfigure()->get('addons_name', 'addons');
            $this->addonsPath = app()->getRootPath() . $addonsName . DIRECTORY_SEPARATOR;
        }

        return $this->addonsPath;
    }

    /**
     * @return string
     */
    public function getAppsPath(): string
    {
        if (!$this->appsPath) {
            $this->appsPath = $this->getAddonsPath() . 'apps' . DIRECTORY_SEPARATOR;
        }
        return $this->appsPath;
    }

    /**
     * @return string
     */
    public function getPluginsPath(): string
    {
        if (!$this->pluginsPath) {
            $this->pluginsPath = $this->getAddonsPath() . 'plugins' . DIRECTORY_SEPARATOR;
        }
        return $this->pluginsPath;
    }
}