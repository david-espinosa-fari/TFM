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
		$query = md5($uuidStation);

		$response = $this->cacheDataRepository->find($query);
		if (!empty($response))
		{
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
				$response['location']
			);
			$station->setHistoric($response['historic']);
			$station->setPredictions($response['predictions']);

		}else{

		$station = $this->repository->findStation($uuidStation);

		$this->cacheDataRepository->insert($query, $station->getStationLikeArray(), 10);
		}
		return $station;

	}
}