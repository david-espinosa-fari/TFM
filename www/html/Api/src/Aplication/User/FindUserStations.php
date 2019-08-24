<?php


namespace App\Aplication\User;


use App\Domain\CacheDataRepository;
use App\Domain\Events\OnUpdateStation;
use App\Domain\Station;
use App\Domain\StationRepository;
use App\Domain\TailMessageRepository;

class FindUserStations
{
    private const VALUE_CACHE = 'findUserStations';
    /**
     * @var StationRepository
     */
    private $stationRepository;
    /**
     * @var CacheDataRepository
     */
    private $cacheDataRepository;
    /**
     * @var TailMessageRepository
     */
    private $tailsRepository;

    public function __construct(
        CacheDataRepository $cacheDataRepository,
        TailMessageRepository $tailsRepositoryRabbit,
        StationRepository $stationRepository
    )
    {

        $this->cacheDataRepository = $cacheDataRepository;
        $this->tailsRepository = $tailsRepositoryRabbit;
        $this->stationRepository = $stationRepository;
    }

    public function __invoke($uuidUser):array
    {
        $queryCache = self::VALUE_CACHE.$uuidUser;

        $response = $this->cacheDataRepository->find($queryCache);

        if (!empty($response))
        {
            return $this->convertArrayToStandardResponse($response);
        }

        return $this->findWithoutCache($uuidUser,$queryCache);

    }

    public function findWithoutCache($uuidUser,$queryCache):array
    {
        $stations = $this->stationRepository->findUserStations($uuidUser);

        $count = count($stations);
        for ($i=0;$i<$count;$i++)
        {
            if (!empty($stations[$i]))
            {
                $stationCache = $stations[$i]->getStationLikeArray();

                $event = new OnUpdateStation($stations[$i]);
                $this->tailsRepository->publishEvent($event);

                $allStationsCache[] = $stationCache;
            }
        }

        if (!empty($allStationsCache))
        {
            $this->cacheDataRepository->insert($queryCache, $allStationsCache, $_SERVER['TIME_TO_LIVE_CACHE']);
        }

        return $stations;
    }

    private function convertArrayToStandardResponse(array $response):array
    {
        $stations=[];
        $count = count($response);
        for ($i=0;$i<$count;$i++)
        {
            if (!empty($response[$i]))
            {
                $station = new Station
                (
                    $response[$i]['uuidStation'],
                    $response[$i]['uuidUser'],
                    $response[$i]['latitud'],
                    $response[$i]['longitud'],
                    $response[$i]['postalCode'],
                    $response[$i]['temp'],
                    $response[$i]['humidity'],
                    $response[$i]['presion'],
                    $response[$i]['location'],
                    $response[$i]['state']
                );
                $station->setHistoric($response[$i]['historic']);
                $station->setPredictions($response[$i]['predictions']);

                $stations[] = $station;
            }

        }
        return $stations;
    }
}