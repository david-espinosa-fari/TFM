<?php

namespace App\Controller;

use App\Aplication\Station\FindStation;
use App\Aplication\Station\UpdateStation;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PutStationController extends AbstractController
{//se puede actualizar all, menos el uuidStation

	/**
	 * @Route("/apiv1/stations/{uuidStation}", name="put_station", methods={"PUT"})
	 * @param $uuidStation
	 * @param Request $request
	 * @return JsonResponse
	 * @throws RedisConectionErrorException
	 */
    public function index($uuidStation, Request $request):JsonResponse
    {

		try
		{
			$stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
			$cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

			$findStation = new FindStation($stationRepository,$cacheData);
			$station = $findStation($uuidStation);

			if ($uuuidUser = $request->get('uuidUser'))
			{
				$station->setUuidUser($uuuidUser);
			}
			if ($lat = $request->get('latitud'))
			{
				$station->setLatitud($lat);
			}
			if ($long = $request->get('longitud'))
			{
				$station->setLongitud($long);
			}
			if ($postalCode = $request->get('postalCode'))
			{
				$station->setPostalCode($postalCode);
			}
			if ($temp = $request->get('temp'))
			{
				$station->setTemp($temp);
			}
			if ($humidity = $request->get('humidity'))
			{
				$station->setUuidUser($uuuidUser);
			}
			if ($presion = $request->get('presion'))
			{
				$station->setUuidUser($uuuidUser);
			}
			if ($location = $request->get('location'))
			{
				$station->setUuidUser($uuuidUser);
			}

			$update = new UpdateStation($stationRepository, $cacheData);
			$update($station);

			return new JsonResponse(
				['Message' => 'Station ' . $station . ' updated'],
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
				));

			$jsonResponse->setEncodingOptions(400);
			return $jsonResponse;
		}
    }

}
