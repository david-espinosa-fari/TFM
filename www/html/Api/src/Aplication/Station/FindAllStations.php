<?php


namespace App\Aplication\Station;


use App\Domain\CacheDataRepository;
use App\Domain\Error\ApiConectionError;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\Station;
use App\Domain\StationRemoteRepository;
use App\Domain\StationRepository;
use App\Domain\TailMessageRepository;

final class FindAllStations
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
    /**
     * @var TailMessageRepository
     */
    private $tailMessageRepository;

    public function __construct
    (
        StationRepository $repository,
        StationRemoteRepository $remoteRepository,
        CacheDataRepository $cache,
        TailMessageRepository $tailMessageRepository
    )
    {
        $this->repository = $repository;
        $this->remoteRepository = $remoteRepository;
        $this->cache = $cache;
        $this->tailMessageRepository = $tailMessageRepository;
    }

    public function __invoke()
    {
        $query = self::CACHE_KEY_VALUE;

        $response = $this->cache->find($query);

        if (!empty($response) && is_array($response)) {
            return $this->convertArrayToStandardResponse($response);

        }
        return $this->findWithOutCache(false);
    }

    private function convertArrayToStandardResponse(array $response): array
    {
        $stations = [];
        $count = count($response);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($response[$i])) {
                $station = new Station
                (
                    $response[$i]['uuidStation'],
                    $response[$i]['uuidUser'],
                    $response[$i]['latitud'],
                    $response[$i]['longitud'],
                    $response[$i]['temp'],
                    $response[$i]['humidity'],
                    $response[$i]['presion'],
                    $response[$i]['location'],
                    $response[$i]['state'],
                    $response[$i]['postalCode']
                );
                $station->setTimestamp($response[$i]['timestamp']);
                $station->setHistoric($response[$i]['historic']);
                $station->setPredictions($response[$i]['predictions']);

                $stations[] = $station;
            }

        }
        return $stations;
    }

    public function findWithOutCache($withoutCache = false): array
    {
        //$query = self::CACHE_KEY_VALUE;
        $localStations = new FindAllLocalStation($this->repository, $this->cache, $this->tailMessageRepository);
        if ($withoutCache) {
            $localStations = $localStations->findWithOutCache();
        } else {
            $localStations = $localStations();
        }

        try {
            $remoteStations = new FindAllRemoteStations($this->remoteRepository, $this->cache, $this->tailMessageRepository);

            if ($withoutCache) {
                $remoteStations = $remoteStations->findWithOutCache();

            } else {
                $remoteStations = $remoteStations();
            }

            $allStations = array_merge($localStations, $remoteStations);
        } catch (RemoteStationsNotFound $exception) {
            $allStations = $localStations;
        } catch (ApiConectionError $exception) {
            $allStations = $localStations;
        }

        if (!empty($allStations) && is_array($allStations)) {
            $count = count($allStations);
            for ($i = 0; $i < $count; $i++) {
                if (!empty($allStations[$i])) {
                    $stationCache = $allStations[$i]->getStationLikeArray();

                    $allStationsCache[] = $stationCache;
                }
            }

            //$this->cache->insert($query, $allStationsCache, $_SERVER['TIME_TO_LIVE_CACHE']);
        }

        return $allStations;

    }

}