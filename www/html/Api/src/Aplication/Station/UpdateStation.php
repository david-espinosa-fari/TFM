<?php

namespace App\Aplication\Station;

use App\Domain\CacheDataRepository;
use App\Domain\Station;
use App\Domain\StationRepository;
use Symfony\Component\BrowserKit\Request;

class UpdateStation
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

	public function __invoke(Station $station):void
	{
		$this->repository->updateStation($station);

		$query = md5((string)$station);
		$this->cacheDataRepository->insert($query, $station->getStationLikeArray(), 10);
	}
}