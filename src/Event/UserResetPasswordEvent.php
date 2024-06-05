<?php

namespace App\Event;

use App\Entity\Token;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserResetPasswordEvent extends Event
{
    private ?User $user = null;
    private ?Token $token = null;

    public function __construct(User $user, Token $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Get the value of user
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Get the value of token
     */
    public function getToken(): Token
    {
        return $this->token;
    }
}
