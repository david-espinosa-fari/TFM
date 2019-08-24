<?php

namespace App\Controller;

use App\Aplication\User\FindUserStations;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetUserStationsController extends AbstractController
{
    /**
     * @Route("/apiv1/user/{uuidUser}/stations", name="get_user_stations", methods={"GET"})
     * @param $uuidUser
     * @return JsonResponse
     */
    public function index($uuidUser): ?JsonResponse
    {
        try {
            $stations = [];

            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            try {
                $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);
                $tails = new TailsRepositoryRabbit($_SERVER['HOST_RABBIT']);

            } catch (RedisConectionErrorException $e) {
                throw new StationErrorException($e->getMessage(), $e->getCode());
            }

            $findUserStations = new FindUserStations($cacheData, $tails, $stationRepository);
            $allStations = $findUserStations($uuidUser);

            $count = count($allStations);

            for ($i = 0; $i < $count; $i++) {

                $station = $allStations[$i]->getStationLikearray();

                $stations[] = $station;
            }
            $jsonResponse = new JsonResponse($stations, 200,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        } catch (StationErrorException $e) {
            $jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        }
    }
}
