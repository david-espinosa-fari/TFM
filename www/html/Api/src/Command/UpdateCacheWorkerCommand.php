<?php

namespace App\Command;

use App\Aplication\Station\FindAllStations;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\StationErrorException;
use App\Infraestructure\CacheDataRepositoryRedis;
use App\Infraestructure\StationRemoteRepositoryApi;
use App\Infraestructure\StationRepositoryMysql;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCacheWorkerCommand extends Command
{
    private $io;
    protected static $defaultName = 'updateCacheWorker';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
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

            if (is_array($allStations))
            {
                $this->io->success('Recreated cache!');
            }

        $this->listenerEventForUpdateCache($cacheData);




        }catch (RedisConectionErrorException $e)
        {
            $this->io->success('Error Redis Conection '.$e->getMessage().' '.$e->getCode());
        }catch (StationErrorException $e)
        {
            $this->io->success('Error '.$e->getMessage().' '.$e->getCode());
        }catch(RemoteStationsNotFound $e)
        {
            $this->io->success('Error '.$e->getMessage().' '.$e->getCode());
        }

    }

    private function listenerEventForUpdateCache(CacheDataRepositoryRedis $cacheData): void
    {

        $connection = new AMQPStreamConnection($_SERVER['HOST_WORKER_RABBIT'], 5672, $_SERVER['RABBIT_USER'],
            $_SERVER['RABBIT_PASS']);
        $channel = $connection->channel();

        $channel->exchange_declare('onUpdate', 'fanout', false, false, false);

        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

        $channel->queue_bind($queue_name, 'onUpdate');

        $this->io->success( " [*] Waiting for Events called onUpdate. To exit Consumer press CTRL+C\n");

        $callback = static function ($msg) use ($cacheData) {

            $message = json_decode($msg->body, true);
            $cacheData->insert($message['uuidStation'],$message);
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while (count($channel->callbacks))
        {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }


}
