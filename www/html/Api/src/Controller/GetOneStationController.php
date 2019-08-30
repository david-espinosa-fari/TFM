<?php

namespace App\Controller;

use App\Aplication\Station\FindStation;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Service\StationsLinks;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class GetOneStationController extends AbstractController
{

    /**
     * @Route("/apiv1/stations/{uuidStation}", name="get_one_station", methods={"GET"})
     * @param $uuidStation
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */
    public function index($uuidStation): JsonResponse
    {

        try {

            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);
            $tails = new TailsRepositoryRabbit($_SERVER['HOST_RABBIT']);

            $findStation = new FindStation($stationRepository, $cacheData, $tails);
            $station = $findStation($uuidStation);

            $stationsLinks = new StationsLinks();
            $jsonResponse = new JsonResponse(array_merge($station->getStationLikeArray(),['links'=>$stationsLinks->getLinksForGET($uuidStation)]), 200,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin'=>'*',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        } catch (StationErrorException $e) {
            $jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin'=>'*',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        }
    }
}
