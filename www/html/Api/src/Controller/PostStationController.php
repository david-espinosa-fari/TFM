<?php

namespace App\Controller;

use App\Aplication\Station\CreateStation;
use App\Domain\Station;
use App\Domain\StationErrorException;
use App\Infraestructure\StationRepositoryMysql;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PostStationController extends AbstractController
{

	//Ejemplo de peticion
	/*
	 * POST http://meteosalle.local/apiv1/stations/
	 * uuidStation=StationInsert22&
	 * uuidUser=uuidUserValue&
	 * latitud=44.666&
	 * longitud=55.7657&
	 * postalCode=08720&
	 * temp=30&
	 * humidity=89&
	 * presion=14.7&
	 * location=VilaFranca
	 * */

	/**
	 * @Route("/apiv1/stations/", name="post_station", methods={"POST"})
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request): JsonResponse
	{
		try
		{

			$station = Station::buildStation($request);

			$repository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);

			try
			{
				$createStation = new CreateStation($repository);
				$createStation($station);

			}
			catch (Exception $exception)
			{
				throw new StationErrorException($exception->getMessage(), $exception->getCode());
			}

			return new JsonResponse(
				['Message' => 'Station ' . $station . ' created'],
				201,
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

			return $jsonResponse;
		}

	}
}
