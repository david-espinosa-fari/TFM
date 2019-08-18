<?php

namespace App\Command;

use App\Aplication\Station\FindAllLocalStation;
use App\Aplication\Station\FindAllStations;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCacheWorkerCommand extends Command
{
    protected static $defaultName = 'updateCacheWorker';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        try{
        $stationRepository = new StationRepositoryMysql($_SERVER['HOST_WORKER_MYSQL']);
        $stationsRemoteRepository = new StationRemoteRepositoryApi();

        $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_WORKER_REDIS']);

            $findAllStations = new FindAllStations
            (
                $stationRepository,
                $stationsRemoteRepository,
                $cacheData
            );
            $allStations = $findAllStations->findWithOutCache();


        }catch (RedisConectionErrorException $e)
        {
            $io->success('Error Redis Conection '.$e->getMessage().' '.$e->getCode());
        }catch (StationErrorException $e)
        {
            $io->success('Error '.$e->getMessage().' '.$e->getCode());
        }catch(RemoteStationsNotFound $e)
        {
            $io->success('Error '.$e->getMessage().' '.$e->getCode());
        }

        if (is_array($allStations))
        {
            echo 'Samples stationes ';
            echo json_encode($allStations[0]);
            echo json_encode(array_pop($allStations));

        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
