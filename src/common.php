<?php
declare(strict_types=1);

/**
 * 注册自动加载
 */

use think\facade\Cache;
use think\facade\Event;
use think\facade\Route;
use think\helper\Str;
use think\route\Url;

spl_autoload_register('\mhs\think\libs\Loader::autoload');

if (!function_exists('hook')) {
    /**
     * 处理插件钩子
     * @param string $event 钩子名称
     * @param array|null $params 传入参数
     * @param bool $once 是否只返回一个结果
     * @return mixed
     */
    function hook($event, $params = null, bool $once = false)
    {
        $result = Event::trigger($event, $params, $once);

        return implode('', $result);
    }
}

if (!function_exists('get_addons_info')) {
    /**
     * @param $addon
     * @param $type
     * @return array|mixed
     */
    function get_addons_info($addon, $type)
    {
        $key = 'addons:' . $type . ':' . $addon . ':info';
        $info = app()->isDebug() ? [] : (array)Cache::get($key, []);
        if (!$info) {
            switch ($type) {
                case 'apps':
                    $path = app()->addons->getPath()->getAppsPath();
                    break;
                case 'plugins':
                    $path = app()->addons->getPath()->getPluginsPath();
                    break;
                default:
                    return [];
                    break;
            }
            $file = $path . strtr($addon, '_', '-') . '/info.php';
            if (file_exists($file)) {
                $info = include "$file";
            }
            Cache::set($key, $info);
        }

        return $info;
    }
}

if (!function_exists('get_addons_app_info')) {
    /**
     * 获取apps配置信息
     * @param string $addon
     * @return array|mixed
     */
    function get_addons_app_info($addon)
    {
        return get_addons_info($addon, 'apps');
    }
}

if (!function_exists('get_addons_plugin_info')) {
    /**
     * 获取plugin配置信息
     * @param string $addon
     * @return array|mixed
     */
    function get_addons_plugin_info($addon)
    {
        return get_addons_info($addon, 'plugins');
    }
}

if (!function_exists('get_addons_class')) {
    /**
     * @param $name
     * @param null $class
     * @param string $type
     * @param string $layer
     * @return string
     */
    function get_addons_class($name, $class = null, $layer = '', $type = 'apps')
    {
        $name = trim($name);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);

            $class[count($class) - 1] = Str::studly(end($class));
            $class = implode('\\', $class);
        } else {
            $class = Str::studly(is_null($class) ? $name : $class);
        }
        $class = '\\addons\\' . $type . '\\' . strtr($name, '-', '_') . '\\' . ($layer ? $layer . '\\' : '') . $class;

        return class_exists($class) ? $class : '';
    }
}
if (!function_exists('get_addons_app_class')) {
    /**
     * @param $name
     * @param null $class
     * @param string $layer
     * @return string
     */
    function get_addons_app_class($name, $class = null, $layer = '')
    {
        return get_addons_class($name, $class, $layer, 'apps');
    }
}
if (!function_exists('get_addons_plugin_class')) {
    /**
     * @param $name
     * @param null $class
     * @param string $layer
     * @return string
     */
    function get_addons_plugin_class($name, $class = null, $layer = '')
    {
        return get_addons_class($name, $class, $layer, 'plugins');
    }
}

if (!function_exists('get_addons_instance')) {
    /**
     * 获取插件实例
     * @param string $name
     * @param string $type
     * @return mixed|null
     */
    function get_addons_instance($name, $type = 'apps')
    {
        static $_addons_instance = [];
        $key = $type . '|' . $name;
        if (isset($_addons_instance[$key])) {
            return $_addons_instance[$key];
        }
        $class = get_addons_class($name, 'Plugin', '', $type);
        if ($class && class_exists($class)) {
            $class = new $class(app());
        } else {
            $class = null;
        }

        return $_addons_instance[$key] = $class;
    }
}

if (!function_exists('addons_url')) {
    /**
     * 获取路由地址
     * @param string $url
     * @param array $params
     * @param bool $suffix
     * @param bool $domain
     * @return Url
     */
    function addons_url($url = '', $params = [], $suffix = true, $domain = false)
    {
        $url = app()->addons->getRouter()->getAddonUrl($url);

        return Route::buildUrl($url, $params)->suffix($suffix)->domain($domain);
    }
}

if (!function_exists('addons_url_cross')) {
    /**
     * @param $url
     * @param $params
     * @param $addon
     * @param string $type
     * @param bool $suffix
     * @param bool $domain
     * @return Url
     */
    function addons_url_cross($url, $params, $addon, $type = 'apps', $suffix = true, $domain = false)
    {
        $request = app()->request;
        $_addons_type = $request->_addons_type;
        $_addons_name = $request->_addons_name;
        if (!empty($type)) {
            $request->_addons_type = $type;
        }
        if (!empty($addon)) {
            $request->_addons_name = $addon;
        }
        $url = addons_url($url, $params, $suffix, $domain);
        $request->_addons_type = $_addons_type;
        $request->_addons_name = $_addons_name;

        return $url;
    }
}