<?php

namespace App\Controller;

use App\Aplication\User\CreateUserToken;
use App\Aplication\User\FindUser;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Users\Error\UserErrorException;
use App\Domain\Users\Services\UserLinks;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\Users\UserRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GetUserController extends AbstractController
{
    /**
     * @Route("/apiv1/user/{uuidUser}", name="get_user", methods={"GET"})
     * @param $uuidUser
     * @param Request $request
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */
    public function index($uuidUser, Request $request): JsonResponse
    {
        try {
            $headerToken = $request->headers->get('Authorization');
            //var_dump($headerToken);
            if (
                isset($headerToken) &&
                CreateUserToken::checkToken($headerToken)) {
            } else {
                throw new UserErrorException('User not Authorized', 403);
            }


            $userRepository = new UserRepositoryMysql();
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            $findUser = new FindUser($userRepository, $cacheData);
            $user = $findUser($uuidUser);

            $userReturn = $user->getUserLikeArray();
            unset($userReturn['password']);

            $userLinks = new UserLinks();
            $jsonResponse = new JsonResponse(['user' => $userReturn, 'links' => $userLinks->getLinksForGet($uuidUser)], 200,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin' => '*',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        } catch (UserErrorException $e) {
            $jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin' => '*',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        }
    }
}
