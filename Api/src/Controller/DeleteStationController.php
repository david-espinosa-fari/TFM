<?php

namespace App\Controller;

use App\Aplication\Station\DeleteStation;
use App\Aplication\User\CreateUserToken;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Service\StationsLinks;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteStationController extends AbstractController
{

    /**
     * @Route("/apiv1/stations/{uuidStation}", name="delete_station", methods={"DELETE"})
     * @param $uuidStation
     * @param Request $request
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */
    public function index($uuidStation, Request $request)
    {

        try {
            $headerToken = $request->headers->get('authorization');
            if (
                isset($headerToken) &&
                CreateUserToken::checkToken($headerToken)) {
            } else {

                throw new StationErrorException('User not Ahutorized',403);
            }
            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            $delete = new DeleteStation($stationRepository, $cacheData);
            $delete($uuidStation);

            $stationsLinks = new StationsLinks();

            return new JsonResponse(
                ['Message' => 'Station deleted','links'=>$stationsLinks->getLinksForDELETE($uuidStation)],
                200,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin'=>'*',
                ));

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
