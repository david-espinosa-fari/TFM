<?php

namespace App\Aplication\Station;

use App\Domain\CacheDataRepository;
use App\Domain\Events\OnUpdateStation;
use App\Domain\Station;
use App\Domain\StationRepository;
use App\Domain\TailMessageRepository;

final class FindStation
{

    /**
     * @var StationRepository
     */
    private $repository;
    /**
     * @var CacheDataRepository
     */
    private $cacheDataRepository;
    /**
     * @var TailMessageRepository
     */
    private $tailMessageRepository;

    public function __construct
    (
        StationRepository $repository,
        CacheDataRepository $cacheDataRepository,
        TailMessageRepository $tailMessageRepository
    )
    {
        $this->repository = $repository;
        $this->cacheDataRepository = $cacheDataRepository;
        $this->tailMessageRepository = $tailMessageRepository;
    }

    public function __invoke($uuidStation): Station
    {
        $response = $this->cacheDataRepository->find($uuidStation);
        if (!empty($response)) {
            $station = new Station
            (
                $response['uuidStation'],
                $response['uuidUser'],
                $response['latitud'],
                $response['longitud'],
                $response['postalCode'],
                $response['temp'],
                $response['humidity'],
                $response['presion'],
                $response['location'],
                $response['state']
            );
            $station->setTimestamp($response['timestamp']);
            $station->setHistoric($response['historic']);
            $station->setPredictions($response['predictions']);

        } else {

            $station = $this->repository->findStation($uuidStation);

            $event = new OnUpdateStation($station);
            $this->tailMessageRepository->publishEvent($event);

        }
        return $station;

    }
}