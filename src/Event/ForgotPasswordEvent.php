<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This forgot_password_user.event is dispatched each time a user ask to reset his password 
 */
class ForgotPasswordEvent extends Event
{
    public const NAME = 'forgot_password_user.event';

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