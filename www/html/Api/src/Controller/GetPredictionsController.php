<?php

namespace App\Controller;

use App\Aplication\Station\FindRemotePredictionStations;
use App\Domain\Error\ApiConectionError;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\StationErrorException;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetPredictionsController extends AbstractController
{
    /**
     * @Route("/apiv1/predictions/{postalCode}", name="get_predictions", methods={"GET"})
     * @param $postalCode
     * @return JsonResponse
     */
    public function index($postalCode):JsonResponse
    {
        $stations = [];

        try {
            $remoteRepository = new StationRemoteRepositoryApi();
            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            $predictions = new FindRemotePredictionStations($remoteRepository, $stationRepository);

            try {
                $stationPredictions = $predictions->findPredictionsByPostalCode($postalCode);
            } catch (ApiConectionError $exception) {
                if ($_SERVER['ENV']==='env'){
                    throw new StationErrorException($exception->getMessage(), 404);
                }
                throw new StationErrorException('Error conecting to remote services', 404);
            }


            $count = count($stationPredictions);

            for ($i = 0; $i < $count; $i++) {

                $station = $stationPredictions[$i]->getStationLikearray();

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
