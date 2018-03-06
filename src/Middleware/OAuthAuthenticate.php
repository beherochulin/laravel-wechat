<?php
namespace Overtrue\LaravelWeChat\Middleware;

use Closure;
use Event;
use http\Env\Request;
use Overtrue\LaravelWeChat\Events\WeChatUserAuthorized;

class OAuthAuthenticate {
    public function handle($request, Closure $next, $account = 'default', $scopes = null) {
        if ( is_array($scopes) || (\is_string($account) && str_is('snsapi_*', $account)) ) { // $account 与 $scopes 写反的情况
            list($account, $scopes) = [$scopes, $account];
            $account || $account = 'default';
        }

        $isNewSession = false;
        $sessionKey = \sprintf('wechat.oauth_user.%s', $account);
        $config = config(\sprintf('wechat.official_account.%s', $account), []);
        $officialAccount = app(\sprintf('wechat.official_account.%s', $account));
        $scopes = $scopes ?: array_get($config, 'oauth.scopes', ['snsapi_base']);

        if ( is_string($scopes) ) $scopes = array_map('trim', explode(',', $scopes));

        $session = session($sessionKey, []);

        if ( !$session ) {
            if ( $request->has('code') ) {
                session([$sessionKey => $officialAccount->oauth->user() ?? []]);
                $isNewSession = true;

                Event::fire(new WeChatUserAuthorized(session($sessionKey), $isNewSession, $account));

                return redirect()->to($this->getTargetUrl($request));
            }

            session()->forget($sessionKey);

            return $officialAccount->oauth->scopes($scopes)->redirect($request->fullUrl());
        }

        Event::fire(new WeChatUserAuthorized(session($sessionKey), $isNewSession, $account));

        return $next($request);
    }
    protected function getTargetUrl($request) {
        $queries = array_except($request->query(), ['code', 'state']);

        return $request->url().(empty($queries) ? '' : '?'.http_build_query($queries));
    }
}
