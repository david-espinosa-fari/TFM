<?php

namespace App\Domain;

use \Exception;
use \Throwable;

final class StationErrorException extends Exception
{
    private $detailsError;
    /**
     * StationErrorException constructor.
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