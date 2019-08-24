<?php

namespace App\Aplication\Station;

use App\Domain\CacheDataRepository;
use App\Domain\Events\OnUpdateStation;
use App\Domain\Station;
use App\Domain\StationErrorException;
use App\Domain\StationRepository;
use App\Domain\TailMessageRepository;

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
    /**
     * @var TailMessageRepository
     */
    private $tailMessageRepository;

    public function __construct(
	    StationRepository $repository,
        CacheDataRepository $cacheDataRepository,
        TailMessageRepository $tailMessageRepository
    )
	{
		$this->repository = $repository;
		$this->cacheDataRepository = $cacheDataRepository;
        $this->tailMessageRepository = $tailMessageRepository;
    }

	public function __invoke():array
	{
		$query = self::CACHE_KEY_VALUE;

		$response = $this->cacheDataRepository->find($query);

		if (!empty($response))
		{
			return $this->convertArrayToStandardResponse($response);

		}
		return $this->findWithOutCache();
	}

	public function findWithOutCache():array
	{
        $query = self::CACHE_KEY_VALUE;
		$stations = $this->repository->findAllStation();

		$count = count($stations);
		for ($i=0;$i<$count;$i++)
		{
			if (!empty($stations[$i]))
			{
                $event = new OnUpdateStation($stations[$i]);
                $this->tailMessageRepository->publishEvent($event);

                $stationCache = $stations[$i]->getStationLikeArray();
                $allStationsCache[] = $stationCache;
			}
		}
		if (!empty($allStationsCache))
		{
			$this->cacheDataRepository->insert($query, $allStationsCache, $_SERVER['TIME_TO_LIVE_CACHE']);
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
					$response[$i]['location'],
					$response[$i]['state']
				);
				$station->setHistoric($response[$i]['historic']);
				$station->setPredictions($response[$i]['predictions']);

				$stations[] = $station;
			}

		}
		return $stations;
	}
}