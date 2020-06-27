think-addons
--- 
```php
# config.php / addons.php
return [
    'autoload' => true,
    'addons_name' => 'addons',
    'hooks' => [
        'event' => 'listener',
        'event1' => ['listener']
    ],
    'binds' => [
        'name' => 'callable'
    ],
    'services' => [
        'service1'
    ],
    'routes' => [
        'apps' => [
             'app1' => [
                ['rule'=>'', 'route'=>'', 'method'=>'','name'=>'','options'=>[], 'pattern'=>[]]
             ]  
        ],
        'plugins' => [
        
        ]
    ],
    'route_domain' => [ // 路由域名
        'domain1' => ['apps'=>['demo'], 'plugins'=>['test']], // app/demo,plugins/test插件路由都是domain1域名
    ],
    'route_alias' => [ // 路由别名，用于重写路由，当插件路由与其他路由冲突时可以重写，原插件路由失效
        'name' => 'rule',
    ],
];
```

默认事件
```
addonsInit: 插件初始化时事件
addonsMiddleware: 插件公共中间件事件
addonsBegin: 插件请求开始
addonModuleInit: 插件开始
addonsActionBegin: 调用方法开始
```