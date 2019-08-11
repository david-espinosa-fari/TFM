<?php

namespace App\Aplication\Station;

use App\Domain\CacheDataRepository;
use App\Domain\StationRepository;

final class DeleteStation
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

	public function __invoke($uuidStation)
	{
		$query = md5($uuidStation);
		$this->repository->deleteStation($uuidStation);

		$this->cacheDataRepository->insert($query,'',0);
	}
}