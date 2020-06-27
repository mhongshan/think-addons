<?php
declare(strict_types=1);

namespace mhs\think\libs;

use Closure;
use mhs\think\Addons;
use think\Route;

class Router
{
    /**
     * @var Addons
     */
    protected $addons;
    /**
     * @var array
     */
    protected $domainCache = [];
    /**
     * @var array
     */
    protected $addonsUrlCache = [];

    public function __construct(Addons $addons)
    {
        $this->addons = $addons;
    }

    public function register(Route $route)
    {
        // 注册插件公共中间件
        /** @var Path $path */
        $path = app()->addons->getPath();
        $file = $path->getAddonsPath() . 'middleware.php';
        if (is_file($file)) {
            app()->middleware->import(include "$file", 'route');
        }
        // 获取路由
        $routes = $this->addons->getConfigure()->get('routes', []);
        $routeDomains = $this->addons->getConfigure()->get('route_domains', []);
        $routeAliases = $this->addons->getConfigure()->get('route_aliases', []);
        $routes = $this->eachRoutes($routes, $routeDomains, $routeAliases);
        foreach ($routes as $domain => $routeItems) {
            $this->addRoutes($route, $routeItems, $domain);
        }
    }

    /**
     * @param array $routes
     * @param array $routeDomains
     * @param array $routeAliases
     * @return array
     */
    protected function eachRoutes($routes, $routeDomains = [], $routeAliases = [])
    {
        $items = [];
        foreach ($routes as $type => $addonRoutes) {
            foreach ($addonRoutes as $addon => $routeItems) {
                $domain = $this->getDomain($type, $addon, $routeDomains);
                !isset($items[$domain]) && $items[$domain] = [];
                $routeItems = $this->eachRouteItems($routeItems, $type, $addon, $routeAliases);
                $items[$domain] = array_merge($items[$domain], $routeItems);
            }
        }

        return $items;
    }

    /**
     * @param $type
     * @param $addon
     * @param $domains
     * @return mixed|string
     */
    protected function getDomain($type, $addon, $domains)
    {
        if (empty($domains)) {
            return '-'; // 默认域名
        }
        $key = $type . ':' . $addon;
        if (!isset($this->domainCache[$key])) {
            foreach ($domains as $domain => $items) {
                if (isset($items[$type]) && in_array($addon, (array)$items[$type])) {
                    $this->domainCache[$key] = $domain;
                    break;
                }
            }
            !isset($this->domainCache[$key]) && $this->domainCache[$key] = '-';
        }
        return $this->domainCache[$key];
    }

    /**
     * @param $items
     * @param $type
     * @param $addon
     * @param array $routeAliases
     * @return array
     */
    protected function eachRouteItems($items, $type, $addon, $routeAliases = [])
    {
        $routeItems = [];
        $execute = '\\mhs\\think\\Route@execute';
        $append = ['_addons' => 1, '_addons_type' => $type, '_addons_name' => $addon];
        foreach ($items as $item) {
            if (empty($item['route'])) { // 跳过空路由
                continue;
            }
            $routeItems[] = $this->getRouteItem($item, $execute, $append, $routeAliases);
        }

        return $routeItems;
    }

    /**
     * @param array $item
     * @param string $route
     * @param array $append
     * @param array $aliases
     * @return array
     */
    protected function getRouteItem($item, $route, $append = [], $aliases = [])
    {
        $defaultRouteItem = ['rule' => '', 'route' => '', 'method' => '*', 'name' => '', 'options' => [], 'pattern' => []];
        $item += $defaultRouteItem;
        $item['rule'] = $append['_addons_type'] . '/' . $append['_addons_name'] . '/' . ltrim($item['rule'], '/');
        if (!empty($item['name'])) {
            $item['name'] = $append['_addons_type'] . '/' . $append['_addons_name'] . '/' . $item['name'];
        } else {
            $item['name'] = $item['rule'];
        }
        if (isset($aliases[$item['name']])) {
            $item['rule'] = $aliases[$item['name']];
        }
        if (!($item['route'] instanceof Closure)) { // 闭包
            $tmp = explode('/', $item['route']);
            $append['controller'] = $tmp[0] ?? '';
            $append['action'] = $tmp[1] ?? '';
            $item['route'] = $route;
        }
        $item['append'] = $append;

        return $item;
    }

    protected function addRoutes(Route $route, $routeItems, $domain = '-')
    {
        $method = $domain == '-' ? 'group' : 'domain';
        $name = $domain == '-' ? '' : $domain;
        $route->{$method}($name, function () use ($route, $routeItems) {
            foreach ($routeItems as $item) {
                $route->rule($item['rule'], $item['route'], $item['method'])
                    ->name($item['name'] ?: $item['rule'])
                    ->option($item['options'])
                    ->pattern($item['pattern'])
                    ->append($item['append'])
                    ->completeMatch(true);
            }
        });
    }

    /**
     * 获取插件连接地址
     * @param string $url
     * @param null|string $type
     * @param null|string $addon
     * @return mixed|string
     */
    public function getAddonUrl($url = '', $type = null, $addon = null)
    {
        $request = app()->request;
        $type = $type ?: $request->_addons_type;
        $addon = $addon ?: $request->_addons_name;
        $url = $type . '/' . $addon . '/' . $url;
        if (isset($this->addonsUrlCache[$url])) {
            return $this->addonsUrlCache[$url];
        }
        $aliases = $this->addons->getConfigure()->get('route_aliases', []);
        if (isset($aliases[$url])) {
            return $this->addonsUrlCache[$url] = $aliases[$url];
        }

        return $this->addonsUrlCache[$url] = $url;
    }

}