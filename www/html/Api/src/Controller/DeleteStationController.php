<?php

namespace App\Controller;

use App\Aplication\Station\DeleteStation;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteStationController extends AbstractController
{

	/**
	 * @Route("/apiv1/stations/{uuidStation}", name="delete_station", methods={"DELETE"})
	 * @param $uuidStation
	 * @return JsonResponse
	 * @throws RedisConectionErrorException
	 */
    public function index($uuidStation)
    {

		try
		{
			$stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
			$cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

			$delete = new DeleteStation($stationRepository,$cacheData);
			$delete($uuidStation);

			return new JsonResponse(
				['Message' => 'Station deleted'],
				200,
				array(
					'Content-Type' => 'application/json',
                    'User-Agent'=>'MeteoSalleMiddel',
				));

		}
		catch (StationErrorException $e)
		{
			$jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
				array(
					'Content-Type' => 'application/json',
                    'User-Agent'=>'MeteoSalleMiddel',
				));

			$jsonResponse->setEncodingOptions(400);
			return $jsonResponse;
		}
    }
}
