<?php

namespace App\Controller;

use App\Aplication\Station\FindAllStations;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Service\StationsLinks;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class GetAllStationsController extends AbstractController
{
    /**
     * @Route("/apiv1/stations/", name="get_all_stations", methods={"GET"})
     */
    public function index(): JsonResponse
    {

        try {
            $stations = [];
            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            try {
                $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);
            } catch (RedisConectionErrorException $e) {
                throw new StationErrorException($e->getMessage(), $e->getCode());
            }
            $remoteRepository = new StationRemoteRepositoryApi();
            $tails = new TailsRepositoryRabbit($_SERVER['HOST_RABBIT']);

            $findAllStations = new FindAllStations($stationRepository, $remoteRepository, $cacheData, $tails);
            $allStations = $findAllStations();

            $count = count($allStations);

            for ($i = 0; $i < $count; $i++) {

                $station = $allStations[$i]->getStationLikearray();

                $stations[] = $station;
            }

            $stationsLinks = new StationsLinks();
            $jsonResponse = new JsonResponse(['stations'=>$stations,'links'=>$stationsLinks->getLinksForGET('{uuidStation}')], 200,
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
