<?php
namespace Overtrue\LaravelWeChat;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade {
    public static function getFacadeAccessor() {
        return 'wechat.official_account';
    }
    public static function officialAccount($name = '') {
        return $name ? app('wechat.official_account.'.$name) : app('wechat.official_account');
    }
    public static function work($name = '') {
        return $name ? app('wechat.work.'.$name) : app('wechat.work');
    }
    public static function payment($name = '') {
        return $name ? app('wechat.payment.'.$name) : app('wechat.payment');
    }
    public static function miniProgram($name = '') {
        return $name ? app('wechat.mini_program.'.$name) : app('wechat.mini_program');
    }
    public static function openPlatform($name = '') {
        return $name ? app('wechat.open_platform.'.$name) : app('wechat.open_platform');
    }
}
