<?php


namespace App\Domain\Error;

use \Exception;
use Throwable;

final class ApiConectionError extends Exception
{

    private const MESSAGE = 'Api conection error exception';

    public function __construct($message = self::MESSAGE, $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}