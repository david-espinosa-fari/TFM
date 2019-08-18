<?php

namespace App\Infraestructure;

//use App\Domain\Error\JsonErrorResponse;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\CacheDataRepository;

use Predis\Client;
use Predis\Connection\ConnectionException;

final class CacheDataRepositoryRedis implements CacheDataRepository
{

	private $redis;

	public function __construct(string $host)
	{
		try
		{
			$this->redis = new Client(['schema' => 'tcp', 'host' => $host, 'port' => 6379]);
			//$this->redis = new Client(['schema' => 'tcp', 'host' => 'localhost', 'port' => 6379]);

			$this->redis->connect();

		} catch (ConnectionException $exception)
		{
			throw new RedisConectionErrorException('Error conecting to Redis '.$exception->getMessage(), 500, $exception);
		}
	}

	public function find($hashMd5FromQuery)
	{
		//$this->redis->flushall(); // elimina toda la cache
		if ($this->redis->exists($hashMd5FromQuery))
		{
			//echo "Obtenido de cache Redis /n";
			return json_decode($this->redis->get($hashMd5FromQuery), true);
		}

		return false;
	}

	public function insert($keyHashMd5, $dataToCache, $timeExpire=null): void
	{
        $timeToLive = $_SERVER['TIME_TO_LIVE_CACHE'];

		$jsonEncode = json_encode($dataToCache,false);

		$this->redis->set($keyHashMd5, $jsonEncode);//if key exist update values
		$this->redis->expire($keyHashMd5, $timeToLive);
		//echo "Updated cache";

	}
}