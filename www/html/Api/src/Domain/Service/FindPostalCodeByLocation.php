<?php
namespace App\Domain\Service;

use App\Domain\StationRepository;

class FindPostalCodeByLocation
{
    /**
     * @var StationRepository
     */
    private $stationRepository;

    public function __construct(StationRepository $stationRepository)
    {
        $this->stationRepository = $stationRepository;
    }

    public function __invoke($locationCode)
    {
        return $this->stationRepository->findPostalCodeByLocation($locationCode);
    }
}