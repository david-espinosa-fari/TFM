<?php

namespace App\Controller;

use App\Aplication\Station\FindAllStations;
use App\Aplication\Station\FindStationByPostalCode;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetStationsByPostalCodeController extends AbstractController
{
    /**
     * @Route("/apiv1/stations/postalcode/{postalCode}", name="get_stations_by_postal_code", methods={"GET"})
     */
    public function index($postalCode)
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
            $tails = new TailsRepositoryRabbit($_SERVER['HOST_RABBIT']);

            $findStationsByPostalCode = new FindStationByPostalCode($stationRepository,$remoteRepository,$cacheData);
            $allStations = $findStationsByPostalCode($postalCode);

            //var_dump($allStations);
            $count = count($allStations);

            for ($i=0;$i<$count;$i++)
            {

                $allStations[$i]->setPostalCode($postalCode);
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
                    'User-Agent'=>'MeteoSalleMiddel',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        }
    }
}
