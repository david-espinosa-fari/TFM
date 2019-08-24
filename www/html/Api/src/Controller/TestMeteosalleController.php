<?php

namespace App\Controller;

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
        return new JsonResponse(
            ['message' => 'Success, the api status is up!!!'], 200,
            array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'MeteoSalleMiddel',
            ));
    }
}
