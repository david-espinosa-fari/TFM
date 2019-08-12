<?php


namespace App\Aplication\User;


use App\Domain\CacheDataRepository;
use App\Domain\Users\User;
use App\Domain\Users\UserRepository;

final class FindUser
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
        $query = md5($uuidUser);

        $response = $this->cacheDataRepository->find($query);
        if (!empty($response))
        {
            $user = new User
            (
                $response['uuidUser'],
                $response['name'],
                $response['lastname'],
                $response['password'],
                $response['userName'],
                $response['age'],
                $response['gender']
            );

        }else{

            $user = $this->repository->findUser($uuidUser);

            $this->cacheDataRepository->insert($query, $user->getUserLikeArray(), 10);
        }
        return $user;
    }
}