<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class NewUserEvent extends Event
{
    private ?User $user = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the value of user
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
