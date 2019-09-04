<?php

namespace App\Controller;

use App\Aplication\Station\FindRemotePredictionStations;
use App\Aplication\User\CreateUserToken;
use App\Domain\Error\ApiConectionError;
use App\Domain\Service\StationsLinks;
use App\Domain\StationErrorException;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GetPredictionsController extends AbstractController
{
    /**
     * @Route("/apiv1/predictions/{postalCode}", name="get_predictions", methods={"GET"})
     * @param $postalCode
     * @return JsonResponse
     */
    public function index($postalCode, Request $request): JsonResponse
    {
        $stations = [];

        try {
            $headerToken = $request->headers->get('authorization');
            if (
                isset($headerToken) &&
                CreateUserToken::checkToken($headerToken)) {
            } else {

                throw new StationErrorException('User not Ahutorized',403);
            }

            $remoteRepository = new StationRemoteRepositoryApi();
            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            $predictions = new FindRemotePredictionStations($remoteRepository, $stationRepository);

            try {
                $stationPredictions = $predictions->findPredictionsByPostalCode($postalCode);
            } catch (ApiConectionError $exception) {
                if ($_SERVER['ENV'] === 'env') {
                    throw new StationErrorException($exception->getMessage(), 404);
                }
                throw new StationErrorException('Error conecting to remote services', 500);
            }


            $count = count($stationPredictions);

            for ($i = 0; $i < $count; $i++) {

                $station = $stationPredictions[$i]->getStationLikearray();

                $stations[] = $station;
            }

            $stationLinks = new StationsLinks();
            $jsonResponse = new JsonResponse(['predictions'=>$stations,'links'=>$stationLinks->getLinksForPostalCode($postalCode)], 200,
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
