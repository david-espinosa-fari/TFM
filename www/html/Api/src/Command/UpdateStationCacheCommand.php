<?php

namespace App\Command;

use App\Aplication\Station\FindAllStations;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use App\Infraestructure\TailsRepositoryRabbit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateStationCacheCommand extends Command
{
    private const TIME_TO_UPDATE = 4;//HORAS
    private $oldTimeStamp;
    private $io;
    private $updateEvery;


    protected static $defaultName = 'UpdateStationCache';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'How many hours before update? default 4 ',self::TIME_TO_UPDATE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $argument = $input->getArgument('arg1');
        if (!empty($argument))
        {
            $this->updateEvery = $argument;
        }else{
            $this->updateEvery = self::TIME_TO_UPDATE;
        }

        if ($this->updateEvery) {
            $this->io->note(sprintf('Every this hours will be cache refresh: %s', $this->updateEvery));
        }


        while ($this->timeJobs())
        {
            $this->io->success('Recreating Stations cache');

        }

    }

    private function timeJobs():bool
    {
        $this->recreateCache();
        sleep((60*60)*$this->updateEvery);

        return true;

    }

    private function recreateCache(): void
    {
        try {
            $this->oldTimeStamp = time();
            $stationRepository = new StationRepositoryMysql($_SERVER['HOST_WORKER_MYSQL']);
            $stationsRemoteRepository = new StationRemoteRepositoryApi();
            $cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_WORKER_REDIS']);
            $tails = new TailsRepositoryRabbit($_SERVER['HOST_WORKER_RABBIT']);

            $findAllStations = new FindAllStations
            (
                $stationRepository,
                $stationsRemoteRepository,
                $cacheData,
                $tails
            );

            $allStations = $findAllStations->findWithOutCache(true);

            if (is_array($allStations)) {
                $this->io->success('Recreated cache!');
            }


        } catch (RedisConectionErrorException $e) {
            $this->io->success('Error Redis Conection ' . $e->getMessage() . ' ' . $e->getCode());
        } catch (StationErrorException $e) {
            $this->io->success('Error ' . $e->getMessage() . ' ' . $e->getCode());
        } catch (RemoteStationsNotFound $e) {
            $this->io->success('Error ' . $e->getMessage() . ' ' . $e->getCode());
        }
    }

}
