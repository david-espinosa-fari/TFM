<?php


namespace App\Aplication\User;


use App\Domain\CacheDataRepository;
use App\Domain\Users\User;
use App\Domain\Users\UserRepository;

final class UpdateUser
{

    /**
     * @var UserRepository
     */
    private $repository;
    /**
     * @var CacheDataRepository
     */
    private $cacheDataRepository;

    public function __construct(UserRepository $repository, CacheDataRepository $cacheDataRepository)
    {
        $this->repository = $repository;
        $this->cacheDataRepository = $cacheDataRepository;
    }

    public function __invoke(User $user):void
    {
        $this->repository->updateUser($user);

        $query = md5((string)$user);
        $this->cacheDataRepository->insert($query, $user->getUserLikeArray(), 10);
    }
}