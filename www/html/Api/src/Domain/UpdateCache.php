<?php


namespace App\Domain;

//esto no se esta usando de moemnto
use App\Domain\CacheDataRepository;

class UpdateCache
{
    /**
     * @var CacheDataRepository
     */
    private $cacheDataRepository;

    public function __construct(CacheDataRepository $cacheDataRepository)
    {

        $this->cacheDataRepository = $cacheDataRepository;
    }

    public function insertCache(string $key, array $values):void
    {
        $query = md5($key);
        echo 'creado con la inyeccion';
        $this->cacheDataRepository->insert($query, $values, 10);
    }

    public function findCache(string $key):array
    {
        return $this->cacheDataRepository->find($key);
    }
}