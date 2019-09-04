<?php

namespace App\Controller;

use App\Aplication\Station\FindStation;
use App\Aplication\Station\UpdateStation;
use App\Aplication\User\CreateUserToken;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Service\StationsLinks;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PutStationController extends AbstractController
{

    /**
     * @Route("/apiv1/stations/{uuidStation}", name="put_station", methods={"PUT"})
     * @param $uuidStation
     * @param Request $request
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */
    public function index($uuidStation, Request $request): JsonResponse
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
            $tails = new TailsRepositoryRabbit($_SERVER['HOST_RABBIT']);

            $findStation = new FindStation($stationRepository, $cacheData, $tails);
            $station = $findStation($uuidStation);
            if ($uuuidUser = $request->get('uuidUser')) {
                $station->setUuidUser($uuuidUser);
            }
            if ($lat = $request->get('latitud')) {
                $station->setLatitud($lat);
            }
            if ($long = $request->get('longitud')) {
                $station->setLongitud($long);
            }
            if ($postalCode = $request->get('postalCode')) {
                $station->setPostalCode($postalCode);
            }
            if ($temp = $request->get('temp')) {
                $station->setTemp($temp);
            }
            if ($humidity = $request->get('humidity')) {
                $station->setHumidity($humidity);
            }
            if ($presion = $request->get('presion')) {
                $station->setPresion($presion);
            }
            if ($location = $request->get('location')) {
                $station->setLocation($location);
            }
            if ($state = $request->get('state')) {
                $station->setState($state);
            }

            $update = new UpdateStation($stationRepository, $tails);
            $update($station);

            $stationsLinks = new StationsLinks();
            return new JsonResponse(
                [
                    'Message' => 'Station ' . $station . ' updated',
                    'links'=>$stationsLinks->getLinksForPUT((string)$station)
                ],
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
