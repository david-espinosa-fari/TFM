<?php


namespace App\Aplication\User;


use App\Domain\CacheDataRepository;
use App\Domain\Users\UserRepository;

final class DeleteUser
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

    public function __invoke($uuidUser)
    {
        $query = $uuidUser;
        $this->repository->deleteUser($uuidUser);

        $this->cacheDataRepository->insert($query, [''], 0);
    }
}