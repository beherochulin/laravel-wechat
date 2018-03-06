<?php
namespace Overtrue\LaravelWeChat\Events;

use Illuminate\Queue\SerializesModels;
use Overtrue\Socialite\User;

class WeChatUserAuthorized {
    use SerializesModels;

    public $user;
    public $isNewSession;
    public $account;

    public function __construct(User $user, $isNewSession = false, string $account) {
        $this->user = $user;
        $this->isNewSession = $isNewSession;
        $this->account = $account;
    }

    public function getUser() {
        return $this->user;
    }
    public function getAccount() {
        return $this->account;
    }
    public function isNewSession() {
        return $this->isNewSession;
    }
    public function broadcastOn() {
        return [];
    }
}
