<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class ConfirmationException extends AccountStatusException
{
    /** @var string */
    private $confirmationToken = "";

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'You need to confirm your account.';
    }

    /**
     * @param string $confirmationToken
     */
    public function setConfirmationToken(string $confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
    }

    /**
     * @return string
     */
    public function getConfirmationToken(): string
    {
        return $this->confirmationToken;
    }

}
