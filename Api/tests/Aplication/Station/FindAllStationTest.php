<?php


namespace App\Tests\Aplication\Station;


use App\Aplication\Station\FindAllStations;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Exception;
use PHPUnit\Framework\TestCase;

class FindAllStationTest extends TestCase
{
    /**
     * @test
     */

    public function shouldReturnArrayOnFindAllUseCase()
    {
        $this->expectException(Exception::class);

            $stations = [];
            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);

                $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            $remoteRepository = new StationRemoteRepositoryApi();
            $tails = new TailsRepositoryRabbit($_SERVER['HOST_RABBIT']);

            $findAllStations = new FindAllStations($stationRepository, $remoteRepository, $cacheData, $tails);
            $allStations = $findAllStations();

    }
}
