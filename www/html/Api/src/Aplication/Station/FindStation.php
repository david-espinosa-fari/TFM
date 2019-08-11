<?php

namespace App\Aplication\Station;

use App\Domain\CacheDataRepository;
use App\Domain\Station;
use App\Domain\StationRepository;

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

	public function __construct(StationRepository $repository, CacheDataRepository $cacheDataRepository)
	{
		$this->repository = $repository;
		$this->cacheDataRepository = $cacheDataRepository;
	}

	public function __invoke($uuidStation):Station
	{
		$this->repository->findStation($uuidStation);
	}
}