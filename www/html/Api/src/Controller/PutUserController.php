<?php

namespace App\Controller;

use App\Aplication\User\FindUser;
use App\Aplication\User\UpdateUser;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Users\Error\UserErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\Users\UserRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PutUserController extends AbstractController
{
    /**
     * @Route("/apiv1/user/{uuidUser}", name="put_user", methods={"PUT"})
     * @param $uuidUser
     * @param Request $request
     * @return JsonResponse
     * @throws RedisConectionErrorException
     * @throws UserErrorException
     */
    public function index($uuidUser, Request $request):JsonResponse
    {

        try
        {
            $userRepository = new UserRepositoryMysql();
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);

            $findUser = new FindUser($userRepository,$cacheData);
            $user = $findUser($uuidUser);

            if ($name = $request->get('name'))
            {
                $user->setName($name);
            }
            if ($lastname = $request->get('lastname'))
            {
                $user->setLastname($lastname);
            }
            if ($password = $request->get('password'))
            {
                $user->setPassword($password);
            }
            if ($userName = $request->get('userName'))
            {
                $user->setUserName($userName);
            }
            if ($age = $request->get('age'))
            {
                $user->setAge($age);
            }
            if ($gender = $request->get('gender'))
            {
                $user->setGender($gender);
            }


            $update = new UpdateUser($userRepository, $cacheData);
            $update($user);

            return new JsonResponse(
                ['Message' => 'User ' . $user . ' updated'],
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
