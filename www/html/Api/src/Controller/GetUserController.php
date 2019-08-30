<?php

namespace App\Controller;

use App\Aplication\User\FindUser;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Users\Error\UserErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\Users\UserRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class GetUserController extends AbstractController
{
    /**
     * @Route("/apiv1/user/{uuidUser}", name="get_user", methods={"GET"})
     * @param $uuidUser
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */
    public function index($uuidUser): JsonResponse
    {

        try {

            $userRepository = new UserRepositoryMysql();
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            $findUser = new FindUser($userRepository, $cacheData);
            $user = $findUser($uuidUser);

            $jsonResponse = new JsonResponse($user->getUserLikeArray(), 200,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin'=>'*',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        } catch (UserErrorException $e) {
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
