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

	public function find($valueToFind)
	{
        $key = md5($valueToFind);
        //$this->redis->flushall(); // elimina toda la cache
        if ($this->redis->exists($key))
        {
            echo"Obtenido de cache Redis /n";
            return json_decode($this->redis->get($key), true);
		}

		return false;
	}

	public function insert(string $valueToInsert, array $dataToCache, $timeExpire=null): void
	{
	    if ($timeExpire !==null)
        {
            $timeToLive =  $timeExpire;
        }else{

            $timeToLive = $_SERVER['TIME_TO_LIVE_CACHE'];
        }

		$jsonEncode = json_encode($dataToCache,false);
        $key = md5($valueToInsert);

        $this->redis->set($key, $jsonEncode);//if key exist update values
		$this->redis->expire($key, $timeToLive);
		echo "Updated cache";

	}
}