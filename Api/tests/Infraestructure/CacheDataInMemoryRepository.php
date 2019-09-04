<?php

namespace tests\Infraestructure;

use App\Domain\Error\RedisConectionErrorException;
use App\Domain\CacheDataRepository;

use Predis\Client;
use Predis\Connection\ConnectionException;

final class CacheDataInMemoryRepository implements CacheDataRepository
{

    private $redis;

    public function __construct()
    {

    }

    public function find($valueToFind)
    {

          return null;
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