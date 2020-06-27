<?php
declare(strict_types=1);

namespace mhs\think\libs;


class Configure
{
    protected $config = [
        'autoload' => true,
        'addons_name' => 'addons',
        'hooks' => [],
        'binds' => [],
        'services' => [],
        'routes' => [],
        'route_domains' => [],
        'route_aliases' => []
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @param array $hooks
     */
    public function addHooks(array $hooks)
    {
        if (empty($hooks)) {
            return;
        }
        $configHooks = $this->get('hooks', []);
        foreach ($hooks as $event => $listener) {
            if (is_string($listener)) {
                $configHooks[$event][] = $listener;
            } elseif (is_array($listener)) {
                $configHooks[$event] = array_unique(array_merge($configHooks[$event] ?? [], $listener));
            }
        }
        $this->set('hooks', $configHooks);
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return array|mixed|null
     */
    public function get($name, $default = null)
    {
        return $this->config[$name] ?? $default;
    }

    public function set($name, $value, $merge = false)
    {
        if ($merge) {
            $value = array_merge($this->get($name, []), $value);
        }
        $this->config[$name] = $value;
    }

    /**
     * @param array $binds
     */
    public function addBinds(array $binds)
    {
        if (empty($binds)) {
            return;
        }
        $configBinds = $this->get('binds', []);
        foreach ($binds as $name => $bind) {
            $configBinds[$name] = $bind;
        }
        $this->set('binds', $configBinds);
    }

    /**
     * @param array $services
     */
    public function addServices(array $services)
    {
        if (empty($services)) {
            return;
        }
        $configServices = $this->get('services', []);
        foreach ($services as $service) {
            $configServices[] = $service;
        }
        $this->set('binds', array_unique($configServices));
    }

    /**
     * @param $type
     * @param $name
     * @param $routes
     */
    public function addRoutes($type, $name, $routes)
    {
        if (empty($routes)) {
            return;
        }
        $defaultRoutes = empty($this->config['routes'][$type][$name]) ? [] : (array)$this->config['routes'][$type][$name];
        $routes = array_unique(array_merge($defaultRoutes, $routes));
        $this->config['routes'][$type][$name] = $routes;
    }
}