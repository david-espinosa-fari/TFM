<?php


namespace App\Aplication\Station;


use App\Domain\StationRemoteRepository;
use App\Domain\StationRepository;

class FindRemotePredictionStations
{
    /**
     * @var StationRemoteRepository
     */
    private $remoteRepository;
    /**
     * @var StationRepository
     */
    private $localRepository;

    public function __construct(
        StationRemoteRepository $remoteRepository,
        StationRepository $localRepository
    )
    {
        $this->remoteRepository = $remoteRepository;
        $this->localRepository = $localRepository;
    }

    public function findPredictionsBy($locationCode):array
    {
        return $this->remoteRepository->findPredictionsByLocationCode($locationCode);
    }

    public function findPredictionsByPostalCode($postalCode):array
    {
        $locationCode = $this->localRepository->findLocationCode($postalCode);
        return $this->findPredictionsBy($locationCode);
    }

}