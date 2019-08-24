<?php

namespace App\Aplication\Station;

use App\Domain\CacheDataRepository;
use App\Domain\StationHistory;
use App\Domain\StationRepository;

final class AddStationHistory
{

    /**
     * @var StationRepository
     */
    private $repository;
    /**
     * @var CacheDataRepository
     */
    private $cacheDataRepository;

    public function __construct(StationRepository $repository, CacheDataRepository $cacheDataRepository)
    {
        $this->repository = $repository;
        $this->cacheDataRepository = $cacheDataRepository;
    }

    public function __invoke(StationHistory $stationHistory)
    {
        $query = md5((string)$stationHistory);
        $this->repository->addStationHistory($stationHistory);

        $this->cacheDataRepository->insert($query, $stationHistory->getStationHostoryLikeArray(), 10);


    }
}