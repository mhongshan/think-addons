<?php
declare(strict_types=1);

namespace mhs\think\middleware;

use Closure;
use think\App;

class AddonsMiddleware
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 插件中间件
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        hook('addonsMiddleware', $request);

        return $next($request);
    }
}