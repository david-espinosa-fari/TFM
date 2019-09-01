<?php

namespace tests\Infraestructure;

use App\Domain\Error\RedisConectionErrorException;
use App\Domain\CacheDataRepository;

use Predis\Client;
use Predis\Connection\ConnectionException;

final class CacheDataInMemoryRepository implements CacheDataRepository
{

    private $redis;

    public function __construct(string $host = null)
    {

     if ($host === 1){

         throw new RedisConectionErrorException('Error conecting to Redis ' , 500);
     }

    }

    public function find($valueToFind)
    {

        if ($valueToFind === true) {

            return true;
        }

        return false;
    }

    public function insert(string $valueToInsert, array $dataToCache, $timeExpire = null): void
    {
        if ($timeExpire !== null) {
            $timeToLive = $timeExpire;
        } else {

            $timeToLive = $_SERVER['TIME_TO_LIVE_CACHE'];
        }
        echo "Updated cache";

    }
}