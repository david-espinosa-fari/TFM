<?php

namespace App\Controller;

use App\Aplication\Station\FindAllLocalStation;
use App\Aplication\Station\FindAllStations;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRemoteRepositoryApi;
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
			$stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
			try{
                $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);
            }catch (RedisConectionErrorException $e){
			    throw new StationErrorException($e->getMessage(),$e->getCode());
            }
            $remoteRepository = new StationRemoteRepositoryApi();

			$findAllStations = new FindAllStations($stationRepository,$remoteRepository,$cacheData);
			$allStations = $findAllStations();

			//var_dump($allStations);
			$count = count($allStations);

			for ($i=0;$i<$count;$i++)
			{

				$station = $allStations[$i]->getStationLikearray();

				$stations[]=$station;
			}
			$jsonResponse = new JsonResponse($stations,200,
				array(
					'Content-Type' => 'application/json',
                    'User-Agent'=>'MeteoSalleMiddel',
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
