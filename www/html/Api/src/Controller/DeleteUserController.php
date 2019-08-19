<?php

namespace App\Controller;

use App\Aplication\User\DeleteUser;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Users\Error\UserErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\Users\UserRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteUserController extends AbstractController
{
    /**
     * @Route("/apiv1/user/{uuidUser}", name="delete_user", methods={"DELETE"})
     * @param $uuidUser
     * @return JsonResponse
     * @throws RedisConectionErrorException
     */
    public function index($uuidUser)
    {

        try
        {
            $userRepository = new UserRepositoryMysql();
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            $delete = new DeleteUser($userRepository,$cacheData);
            $delete($uuidUser);

            return new JsonResponse(
                ['Message' => 'User deleted'],
                200,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent'=>'MeteoSalleMiddel',
                ));

        }
        catch (UserErrorException $e)
        {
            $jsonResponse = new JsonResponse(['Message' => $e->getMessage()], $e->getCode(),
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent'=>'MeteoSalleMiddel',
                ));

            $jsonResponse->setEncodingOptions(400);
            return $jsonResponse;
        }
    }

}
