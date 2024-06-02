<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserUpdatedEvent extends Event
{
    private ?User $user = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }
}
