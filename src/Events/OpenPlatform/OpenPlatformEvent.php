<?php
namespace Overtrue\LaravelWeChat\Events\OpenPlatform;

abstract class OpenPlatformEvent {
    public $payload;

    public function __construct(array $payload) {
        $this->payload = $payload;
    }
    public function __call($name, $args) {
        return $this->payload[substr($name, 3)] ?? null;
    }
}
