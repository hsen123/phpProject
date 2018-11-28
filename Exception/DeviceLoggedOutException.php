<?php
/**
 * Created by PhpStorm.
 * User: aschattney
 * Date: 30.04.18
 * Time: 18:00
 */

namespace App\Exception;


use Symfony\Component\Security\Core\Exception\AccountStatusException;

class DeviceLoggedOutException extends AccountStatusException
{
    /**
     * @var string
     */
    const MESSAGE = 'Device logged out.';

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return DeviceLoggedOutException::MESSAGE;
    }
}