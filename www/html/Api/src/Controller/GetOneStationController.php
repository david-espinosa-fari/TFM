<?php

namespace App\Controller;

use App\Aplication\Station\FindStation;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetOneStationController extends AbstractController
{

	/**
	 * @Route("/apiv1/stations/{uuidStation}", name="get_one_station", methods={"GET"})
	 * @param $uuidStation
	 * @return JsonResponse
	 * @throws RedisConectionErrorException
	 */
	public function index($uuidStation):JsonResponse
	{

		try{

			$stationRepository = new StationRepositoryMysql();
			$cacheData = new CacheDataRepositoryRedis();

			$findStation = new FindStation($stationRepository, $cacheData);

			$station = $findStation($uuidStation);

			return new JsonResponse(
				$station->getStationLikeArray(),
				200,
				array(
					'Content-Type' => 'application/json',
				));

		}
		catch (StationErrorException $e)
		{
			$jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
				array(
					'Content-Type' => 'application/json',
				));

			return $jsonResponse;
		}
	}
}
