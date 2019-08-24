<?php


namespace App\Aplication\User;


use App\Domain\Users\User;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\Users\UserRepositoryMysql;

final class CreateUser
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
        $query = (string)$user;

        $this->userRepositoryMysql->createUser($user);

        $this->cacheDataRepositoryRedis->insert($query, $user->getUserLikeArray(), $_SERVER['TIME_TO_LIVE_CACHE']);
    }

}