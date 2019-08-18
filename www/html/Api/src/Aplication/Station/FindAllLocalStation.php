<?php

namespace App\Aplication\Station;

use App\Domain\CacheDataRepository;
use App\Domain\Station;
use App\Domain\StationErrorException;
use App\Domain\StationRepository;

final class FindAllLocalStation
{
    private const CACHE_KEY_VALUE = 'allLocalStations';
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

	public function __invoke():array
	{
		$query = md5(self::CACHE_KEY_VALUE);

		$response = $this->cacheDataRepository->find($query);

		if (!empty($response))
		{
			return $this->convertArrayToStandardResponse($response);

		}
		return $this->findWithOutCache();
	}

	public function findWithOutCache():array
	{
        $query = md5(self::CACHE_KEY_VALUE);
		$stations = $this->repository->findAllStation();

		$count = count($stations);
		for ($i=0;$i<$count;$i++)
		{
			if (!empty($stations[$i]))
			{
				$stationCache = $stations[$i]->getStationLikeArray();
				$key = md5((string)$stations[$i]);

				$this->cacheDataRepository->insert($key, $stationCache, 10);//inserto en la cache cada registro encontrado individualmente

				$allStationsCache[] = $stationCache;
			}
		}
		if (!empty($allStationsCache))
		{
			$this->cacheDataRepository->insert($query, $allStationsCache, 10);
		}

		return $stations;
	}

	private function convertArrayToStandardResponse(array $response):array
	{
		$stations=[];
		$count = count($response);
		for ($i=0;$i<$count;$i++)
		{
			if (!empty($response[$i]))
			{
				$station = new Station
				(
					$response[$i]['uuidStation'],
					$response[$i]['uuidUser'],
					$response[$i]['latitud'],
					$response[$i]['longitud'],
					$response[$i]['postalCode'],
					$response[$i]['temp'],
					$response[$i]['humidity'],
					$response[$i]['presion'],
					$response[$i]['location']
				);
				$station->setHistoric($response[$i]['historic']);
				$station->setPredictions($response[$i]['predictions']);

				$stations[] = $station;
			}

		}
		return $stations;
	}
}