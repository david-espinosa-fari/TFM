<?php

namespace App\Controller;

use App\Aplication\Station\CreateStation;
use App\Domain\Station;
use App\Domain\StationErrorException;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PostStationController extends AbstractController
{
    /**
     * @Route("/apiv1/stations/", name="post_station", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $station = Station::buildStation($request);

            $repository = new StationRepositoryMysql($_SERVER['HOST_MYSQL']);
            $tails = new TailsRepositoryRabbit($_SERVER['HOST_RABBIT']);
            try {
                $createStation = new CreateStation($repository, $tails);
                $createStation($station);

            } catch (Exception $exception) {
                throw new StationErrorException($exception->getMessage(), $exception->getCode());
            }

            return new JsonResponse(
                ['Message' => 'Station ' . $station . ' created'],
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
