<?php

namespace App\Domain\Error;

use Exception;
use Throwable;

final class RedisConectionErrorException extends Exception
{
	private const MESSAGE = 'COULD NOT CONECT WHIT REDIS, EXCEPTION';

	public function __construct($message = self::MESSAGE, $code = 500, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}