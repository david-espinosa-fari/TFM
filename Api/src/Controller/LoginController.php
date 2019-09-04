<?php

namespace App\Controller;

use App\Aplication\User\CreateUserToken;
use App\Aplication\User\FindUser;
use App\Aplication\User\CheckUserPassword;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Users\Error\UserErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\Users\UserRepositoryMysql;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \Firebase\JWT\JWT;

class LoginController extends AbstractController
{
    /**
     * @Route("/apiv1/user/login/", name="login", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if(isset($_SESSION))
        {
            session_unset();
            session_destroy();
            session_cache_expire(30);
            session_start();
            session_regenerate_id(true);
        }else{
            session_start();
        }
        try {
            $userName = $request->get('userName');
            $password = $request->get('password');
            if (!isset($userName, $password))
            {
                throw new UserErrorException('Provied user and password please. ',401);
            }

            $userRepository = new UserRepositoryMysql();
            try{
                $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_REDIS']);
            } catch (RedisConectionErrorException $e) {
                throw new UserErrorException($e->getMessage(),$e->getCode());
            }

            $findUser = new FindUser($userRepository, $cacheData);
            $user = $findUser($userName);

            new CheckUserPassword($user,$password);

            $token = new CreateUserToken($user);

            $jsonRespone = new JsonResponse([
                'Message' => 'Login Correct',
                'token'=>$token(),
                'uuidUser'=>(string)$user], 200,
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'MeteoSalleMiddel',
                    'Access-Control-Allow-Origin'=>'*',
                ));
            $jsonRespone->setEncodingOptions(400);

            return $jsonRespone;


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
