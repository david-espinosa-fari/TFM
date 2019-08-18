<?php


namespace App\Aplication\Station;


use App\Domain\CacheDataRepository;
use App\Domain\Error\ApiConectionError;
use App\Domain\Error\LocationCodeError;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\Station;
use App\Domain\StationRemoteRepository;
use App\Domain\StationRepository;

class FindAllRemoteStations
{
    private const CACHE_KEY_VALUE = 'allRemoteStations';
    /**
     * @var StationRemoteRepository
     */
    private $repository;
    /**
     * @var StationRepository
     */
    private $localRepository;
    /**
     * @var CacheDataRepository
     */
    private $cacheDataRepository;

    public function __construct(StationRemoteRepository $repository, CacheDataRepository $cacheDataRepository)
    {
        $this->repository = $repository;
        $this->cacheDataRepository = $cacheDataRepository;
    }

    public function __invoke():array
    {
        $query = md5(self::CACHE_KEY_VALUE);

        $response = $this->cacheDataRepository->find($query);

        if (!empty($response))
        {
            return $this->convertArrayToStandardResponse($response);

        }
        return $this->findWithOutCache();
    }

    public function findWithOutCache():array
    {
        $query = md5(self::CACHE_KEY_VALUE);
        $stations = $this->repository->findAllStation();

        $count = count($stations);
        for ($i=0;$i<$count;$i++)
        {
            if (!empty($stations[$i]))
            {
                $stationCache = $stations[$i]->getStationLikeArray();

                $allStationsCache[] = $stationCache;
            }
        }

        if (!empty($allStationsCache))
        {
            $this->cacheDataRepository->insert($query, $allStationsCache, 10);
        }

        return $stations;
    }

    private function convertArrayToStandardResponse(array $response):array
    {
        $stations=[];
        $count = count($response);
        for ($i=0;$i<$count;$i++)
        {
            if (!empty($response[$i]))
            {
                $station = new Station
                (
                    $response[$i]['uuidStation'],
                    $response[$i]['uuidUser'],
                    $response[$i]['latitud'],
                    $response[$i]['longitud'],
                    $response[$i]['postalCode'],
                    $response[$i]['temp'],
                    $response[$i]['humidity'],
                    $response[$i]['presion'],
                    $response[$i]['location']
                );
                $station->setHistoric($response[$i]['historic']);
                $station->setPredictions($response[$i]['predictions']);

                $stations[] = $station;
            }

        }
        return $stations;
    }
}