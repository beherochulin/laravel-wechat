<?php
namespace Overtrue\LaravelWeChat;

use EasyWeChat\MiniProgram\Application as MiniProgram;
use EasyWeChat\OfficialAccount\Application as OfficialAccount;
use EasyWeChat\OpenPlatform\Application as OpenPlatform;
use EasyWeChat\Payment\Application as Payment;
use EasyWeChat\Work\Application as Work;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class ServiceProvider extends LaravelServiceProvider {
    public function boot() {}

    protected function setupConfig() {
        $source = realpath(__DIR__.'/config.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('wechat.php')], 'laravel-wechat');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('wechat');
        }

        $this->mergeConfigFrom($source, 'wechat');
    }
    public function register() {
        $this->setupConfig();

        $apps = [
            'official_account' => OfficialAccount::class,
            'work' => Work::class,
            'mini_program' => MiniProgram::class,
            'payment' => Payment::class,
            'open_platform' => OpenPlatform::class,
        ];

        foreach ($apps as $name => $class) {
            if ( empty(config('wechat.'.$name)) ) continue;

            if ($config = config('wechat.route.'.$name)) {
                $this->getRouter()->group($config['attributes'], function ($router) use ($config) {
                    $router->post($config['uri'], $config['action']);
                });
            }

            if ( !empty(config('wechat.'.$name.'.app_id')) ) {
                $accounts = [
                    'default' => config('wechat.'.$name),
                ];
                config(['wechat.'.$name.'.default' => $accounts['default']]);
            } else {
                $accounts = config('wechat.'.$name);
            }

            foreach ( $accounts as $account => $config ) {
                $this->app->singleton("wechat.{$name}.{$account}", function ($laravelApp) use ($name, $account, $config, $class) {
                    $app = new $class(array_merge(config('wechat.defaults', []), $config));
                    if ( config('wechat.defaults.use_laravel_cache') ) $app['cache'] = new CacheBridge($laravelApp['cache.store']);
                    $app['request'] = $laravelApp['request'];

                    return $app;
                });
            }
            $this->app->alias("wechat.{$name}.default", 'wechat.'.$name);
            $this->app->alias("wechat.{$name}.default", 'easywechat.'.$name);

            $this->app->alias('wechat.'.$name, $class);
            $this->app->alias('easywechat.'.$name, $class);
        }
    }

    protected function getRouter() {
        if ( $this->app instanceof LumenApplication && !class_exists('Laravel\Lumen\Routing\Router') ) return $this->app;
        return $this->app->router;
    }
}
