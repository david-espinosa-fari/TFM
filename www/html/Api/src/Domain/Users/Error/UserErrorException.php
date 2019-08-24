<?php

namespace App\Domain\Users\Error;

use \Exception;
use \Throwable;

final class UserErrorException extends Exception
{
    private $detailsError;

    /**
     * UserErrorException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }

    public function setMoreInfo(Throwable $throwable): void
    {
        if ($_ENV === 'dev') {
            $this->detailsError = $throwable;
            parent::__construct($throwable->getMessage(), $throwable->getCode());
        }

    }
}