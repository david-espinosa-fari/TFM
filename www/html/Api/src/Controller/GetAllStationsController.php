<?php

namespace App\Controller;

use App\Aplication\Station\FindAllStation;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class GetAllStationsController extends AbstractController
{
    /**
     * @Route("/apiv1/stations/", name="get_all_stations", methods={"GET"})
     */
	public function index():JsonResponse
	{

		try{
			$stations=[];
			$stationRepository = new StationRepositoryMysql();
			$cacheData = new CacheDataRepositoryRedis();


			$findAllStations = new FindAllStation($stationRepository,$cacheData);
			$allStations = $findAllStations();

			$count = count($allStations);

			for ($i=0;$i<$count;$i++)
			{

				$station = $allStations[$i]->getStationLikearray();

				$stations[]=$station;
			}
			$jsonResponse = new JsonResponse($stations,200,
				array(
					'Content-Type' => 'application/json',
				));

			$jsonResponse->setEncodingOptions(400);
			return $jsonResponse;
		}
		catch (StationErrorException $e)
		{
			$jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
				array(
					'Content-Type' => 'application/json',
				));

			$jsonResponse->setEncodingOptions(400);
			return $jsonResponse;
		}
	}
}
