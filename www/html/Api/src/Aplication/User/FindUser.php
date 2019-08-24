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

    public function __invoke($uuidUser):user
    {
        $response = $this->cacheDataRepository->find($uuidUser);
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

            $this->cacheDataRepository->insert($uuidUser, $user->getUserLikeArray(), $_SERVER['TIME_TO_LIVE_CACHE']);
        }
        return $user;
    }
}