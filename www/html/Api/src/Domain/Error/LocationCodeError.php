<?php


namespace App\Domain\Error;

use \Exception;

final class LocationCodeError extends Exception
{

    private const MESSAGE = 'Location code not found';

    public function __construct($message = self::MESSAGE, $code = 500)
    {
        parent::__construct($message, $code);
    }
}