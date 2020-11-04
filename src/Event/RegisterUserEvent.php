<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This register_user.event is dispatched each time a user is registered 
 * in the system
 */
class RegisterUserEvent extends Event
{
    public const NAME = 'register_user.event';

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}