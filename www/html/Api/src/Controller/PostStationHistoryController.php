<?php

namespace App\Controller;

use App\Aplication\Station\AddStationHistory;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Domain\StationHistory;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PostStationHistoryController extends AbstractController
{

    /**
     * @Route("/apiv1/stations/{uuidStation}/history", name="post_station_history", methods={"POST"})
     * @param $uuidStation
     * @param Request $request
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */

    public function index($uuidStation, Request $request): JsonResponse
    {
        try {

            $stationHistory = StationHistory::buildStationHistory($uuidStation, $request);

            $repository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            $cacheDataRepository = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            $addStationHistory = new AddStationHistory($repository, $cacheDataRepository);
            $addStationHistory($stationHistory);


            return new JsonResponse(
                ['Message' => 'history for the station ' . $stationHistory . ' updated'],
                201,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                ));

        } catch (StationErrorException $e) {
            $jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                ));

            return $jsonResponse;
        }

    }

}
