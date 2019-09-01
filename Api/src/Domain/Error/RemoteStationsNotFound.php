<?php


namespace App\Domain\Error;


use \Exception;
use Throwable;

class RemoteStationsNotFound extends Exception
{

    private const MESSAGE = 'Stations remotes not found';

    public function __construct($message = self::MESSAGE, $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}