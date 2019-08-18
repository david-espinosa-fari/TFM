<?php


namespace App\Aplication\Station;


use App\Domain\CacheDataRepository;
use App\Domain\Error\ApiConectionError;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\Station;
use App\Domain\StationRemoteRepository;
use App\Domain\StationRepository;

class FindAllStations
{
    private const CACHE_KEY_VALUE = 'allStations';
    private $repository;
    /**
     * @var
     */
    private $remoteRepository;
    /**
     * @var
     */
    private $cache;

    public function __construct
    (
        StationRepository $repository,
        StationRemoteRepository $remoteRepository,
        CacheDataRepository $cache
    )
    {
        $this->repository = $repository;
        $this->remoteRepository = $remoteRepository;
        $this->cache = $cache;
    }

    public function __invoke()
    {
        $query = md5(self::CACHE_KEY_VALUE);

        $response = $this->cache->find($query);

        if (!empty($response) && is_array($response))
        {
            return $this->convertArrayToStandardResponse($response);

        }
        //aqui seria lanzar el evento
        return $this->findWithOutCache();
    }

    public function findWithOutCache(): array
    {
        $query = md5(self::CACHE_KEY_VALUE);
        $localStations = new FindAllLocalStation($this->repository,$this->cache);
        $localStations = $localStations->findWithOutCache();

        try{
            $remoteStations = new FindAllRemoteStations($this->remoteRepository, $this->cache);
            $remoteStations = $remoteStations->findWithOutCache();

            $allStations = array_merge($localStations, $remoteStations);
        }catch (RemoteStationsNotFound $exception)
        {
            //log this
            $allStations = $localStations;
            //lanzar evento para volver a buscar
        }catch (ApiConectionError $exception){
            $allStations = $localStations;
        }

        if (!empty($allStations) && is_array($allStations))
        {
            $count = count($allStations);
            for ($i=0;$i<$count;$i++)
            {
                if (!empty($allStations[$i]))
                {
                    $stationCache = $allStations[$i]->getStationLikeArray();

                    $allStationsCache[] = $stationCache;
                }
            }

            $this->cache->insert($query, $allStationsCache, 10);
        }

        return $allStations;

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