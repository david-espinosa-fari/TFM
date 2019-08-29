<?php

namespace App\Controller;

use App\Aplication\User\CreateUser;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Users\Error\UserErrorException;
use App\Domain\Users\User;
use App\Infraestructure\CacheDataRepositoryRedis;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Infraestructure\Users\UserRepositoryMysql;
use \Exception;



final class PostUserController extends AbstractController
{

    /**
     * @Route("/apiv1/user/", name="post_user", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */
    public function index(Request $request): JsonResponse
    {

        try {
            $repository = new UserRepositoryMysql();
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            try {
                $createUser = new CreateUser($repository, $cacheData);
                $user = User::buildUser($request);
                $createUser($user);

            } catch (Exception $exception) {
                throw new UserErrorException($exception->getMessage(), $exception->getCode());
            }


            return new JsonResponse(
                ['Message' => 'User ' . $user . ' created'],
                201,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin'=>'*',

                ));
        } catch (UserErrorException $e) {
            $jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin'=>'*',
                ));

            return $jsonResponse;
        }
    }
}
