<?php


namespace App\Aplication\Station;


use App\Domain\Error\LocationCodeError;
use App\Domain\StationErrorException;
use App\Domain\StationRemoteRepository;
use App\Domain\StationRepository;

final class FindRemotePredictionStations
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

    public function findPredictionsByPostalCode($postalCode): array
    {
        try{
            $locationCode = $this->localRepository->findLocationCode($postalCode);
            return $this->findPredictionsBy($locationCode);

        }catch (LocationCodeError $locationCodeError) {
            throw new StationErrorException($locationCodeError->getMessage(),$locationCodeError->getCode());
        }
    }

    public function findPredictionsBy($locationCode): array
    {
        return $this->remoteRepository->findPredictionsByLocationCode($locationCode);
    }

}