<?php
namespace Overtrue\LaravelWeChat\Controllers;

use EasyWeChat\OpenPlatform\Application;
use EasyWeChat\OpenPlatform\Server\Guard;
use Event;
use Overtrue\LaravelWeChat\Events\OpenPlatform as Events;

class OpenPlatformController extends Controller {
    public function __invoke(Application $application) {
        $server = $application->server;

        $server->on(Guard::EVENT_AUTHORIZED, function ($payload) {
            Event::fire(new Events\Authorized($payload));
        });
        $server->on(Guard::EVENT_UNAUTHORIZED, function ($payload) {
            Event::fire(new Events\Unauthorized($payload));
        });
        $server->on(Guard::EVENT_UPDATE_AUTHORIZED, function ($payload) {
            Event::fire(new Events\UpdateAuthorized($payload));
        });
        $server->on(Guard::EVENT_COMPONENT_VERIFY_TICKET, function ($payload) {
            Event::fire(new Events\VerifyTicketRefreshed($payload));
        });

        return $server->serve();
    }
}
