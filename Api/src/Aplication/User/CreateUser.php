<?php


namespace App\Aplication\User;


use App\Domain\CacheDataRepository;
use App\Domain\Users\User;
use App\Domain\Users\UserRepository;

final class CreateUser
{

    private $userRepositoryMysql;

    private $cacheDataRepositoryRedis;

    public function __construct(UserRepository $userRepositoryMysql, CacheDataRepository $cacheDataRepositoryRedis)
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