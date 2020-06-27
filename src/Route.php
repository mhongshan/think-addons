<?php
declare(strict_types=1);

namespace mhs\think;

use think\exception\HttpException;
use think\facade\Event;
use think\helper\Str;

class Route
{
    /**
     * @param string|null $_addons_type
     * @param string|null $_addons_name
     * @param string|null $controller
     * @param string|null $action
     * @return mixed
     */
    public static function execute($_addons_type = null, $_addons_name = null, $controller = null, $action = null)
    {
        $app = app();
        $request = $app->request;
        Event::trigger('addonsBegin', $request);
        if (empty($_addons_type) || empty($_addons_name) || empty($controller) || empty($action)) {
            throw new HttpException(500, lang('addon can not be empty'));
        }
        $request->_addon_type = $_addons_type;
        $request->_addon_name = $_addons_name;
        // 设置当前请求的控制器、操作
        $request->setController($controller)->setAction($action);
        // 获取插件基础信息
        $info = get_addons_info($_addons_name, $_addons_type);
        if (!$info) {
            throw new HttpException(404, lang('addon %s not found', [$_addons_name]));
        }
        if (!$info['status']) {
            throw new HttpException(500, lang('addon %s is disabled', [$_addons_name]));
        }
        // 监听addonModuleInit
        Event::trigger('addonModuleInit', $request);
        $class = get_addons_class($_addons_name, $controller, 'controller', $_addons_type);
        if (!$class) {
            throw new HttpException(404, lang('addon controller %s not found', [Str::studly($controller)]));
        }

        // 生成控制器对象
        $instance = invoke($class, ['app' => $app]);
        $vars = [];
        if (is_callable([$instance, $action])) {
            // 执行操作方法
            $call = [$instance, $action];
        } elseif (is_callable([$instance, '_empty'])) {
            // 空操作
            $call = [$instance, '_empty'];
            $vars = [$action];
        } else {
            // 操作不存在
            throw new HttpException(404, lang('addon action %s not found', [get_class($instance) . '->' . $action . '()']));
        }
        Event::trigger('addonsActionBegin', $call);

        return invoke($call, $vars);
    }
}