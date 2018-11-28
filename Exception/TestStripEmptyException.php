<?php

namespace App\Exception;

class TestStripEmptyException extends \Exception
{
    /**
     * @var string
     */
    const MESSAGE = 'TestStrip empty';

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return self::MESSAGE;
    }
}