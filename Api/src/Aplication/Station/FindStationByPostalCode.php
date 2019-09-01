<?php


namespace App\Aplication\Station;


use App\Domain\CacheDataRepository;
use App\Domain\Error\ApiConectionError;
use App\Domain\Error\LocationCodeError;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\Station;
use App\Domain\StationErrorException;
use App\Domain\StationRemoteRepository;
use App\Domain\StationRepository;
use App\Domain\TailMessageRepository;

final class FindStationByPostalCode
{
    private const CACHE_LOCAL_VALUE = 'local';
    private const CACHE_REMOTE_VALUE = 'remote';
    /**
     * @var StationRepository
     */
    private $repository;
    /**
     * @var StationRemoteRepository
     */
    private $remoteRepository;
    /**
     * @var CacheDataRepository
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

    public function __invoke($postalCode): array
    {
        try {
            $locationCode = $this->repository->findLocationCode($postalCode);

            $localStations = $this->findLocalStations($postalCode);
            $remoteStations = $this->findRemoteStations($locationCode);

            $allStations = array_merge($localStations, $remoteStations);

        } catch (RemoteStationsNotFound $exception) {
            $allStations = $localStations;
        } catch (ApiConectionError $exception) {
            $allStations = $localStations;
        } catch (LocationCodeError $locationCodeError) {
            throw new StationErrorException($locationCodeError->getMessage(), $locationCodeError->getCode());
        } catch (StationErrorException $exception) {
            $allStations = $localStations ?? $remoteStations;
        }
        if (empty($allStations))
        {
            throw new StationErrorException('We could not found any station for this postal code', 200);
        }
        return $allStations;
    }

    private function findRemoteStations($locationCode): array
    {
        $query = self::CACHE_REMOTE_VALUE . $locationCode;

        $response = $this->cache->find($query);

        if (!empty($response) && is_array($response)) {
            return $this->convertArrayToStandardResponse($response);
        }

        $remoteStations = $this->remoteRepository->findStationsByLocationCode($locationCode);
        $this->updateCache($query, $remoteStations);
        return $remoteStations;
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

    private function updateCache(string $key, array $stations): void
    {
        if (!empty($stations) && is_array($stations)) {
            $count = count($stations);
            for ($i = 0; $i < $count; $i++) {
                if (!empty($stations[$i])) {
                    $stationCache = $stations[$i]->getStationLikeArray();

                    $allStationsCache[] = $stationCache;
                }
            }
            $this->cache->insert($key, $allStationsCache, $_SERVER['TIME_TO_LIVE_CACHE']);
        }
    }

    private function findLocalStations($postalCode): array
    {
        $query = self::CACHE_LOCAL_VALUE . $postalCode;

        $response = $this->cache->find($query);

        if (!empty($response) && is_array($response)) {
            return $this->convertArrayToStandardResponse($response);
        }

        $localStations = $this->repository->findStationsByPostalCode($postalCode);
        $this->updateCache($query, $localStations);
        return $localStations;

    }


}