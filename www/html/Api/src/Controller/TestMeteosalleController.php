<?php

namespace App\Controller;

use App\Domain\Service\StationsLinks;
use App\Domain\Users\Services\UserLinks;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class TestMeteosalleController extends AbstractController
{
    /**
     * @Route("/apiv1/status", name="test_meteosalle")
     */
    public function index()
    {
        $stationsLinks = new StationsLinks();
        $userLinks = new UserLinks();
        return new JsonResponse(
            [
                'message' => 'Success, the api status is up!!!',
                'links'=>array_merge($stationsLinks->getLinksForAll(),$userLinks->getLinksForAll())
            ], 200,
            array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'MeteoSalleMiddel',
                'Access-Control-Allow-Origin'=>'*',

            ));
    }
}
