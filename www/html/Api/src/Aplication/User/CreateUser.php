<?php


namespace App\Aplication\User;


use App\Domain\Users\Error\UserErrorException;
use App\Domain\Users\User;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\Users\UserRepositoryMysql;

class CreateUser
{
    /**
     * @var UserRepositoryMysql
     */
    private $userRepositoryMysql;
    /**
     * @var CacheDataRepositoryRedis
     */
    private $cacheDataRepositoryRedis;

    public function __construct(UserRepositoryMysql $userRepositoryMysql, CacheDataRepositoryRedis $cacheDataRepositoryRedis)
    {

        $this->userRepositoryMysql = $userRepositoryMysql;
        $this->cacheDataRepositoryRedis = $cacheDataRepositoryRedis;
    }

    public function __invoke(User $user)
    {
        $query = md5((string)$user);

        $this->userRepositoryMysql->createUser($user);

        $this->cacheDataRepositoryRedis->insert($query,$user->getUserLikeArray(),15);
    }

}