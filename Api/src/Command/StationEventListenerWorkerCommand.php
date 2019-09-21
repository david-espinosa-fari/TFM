<?php

namespace App\Command;

use App\Domain\Error\RedisConectionErrorException;

use App\Infraestructure\CacheDataRepositoryRedis;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StationEventListenerWorkerCommand extends Command
{
    protected static $defaultName = 'stationEventListenerWorker';
    private $io;
    private $cacheData;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        try {
            $this->cacheData = new CacheDataRepositoryRedis($_SERVER['HOST_WORKER_REDIS']);

           // shell_exec('nohup ' . "start php bin/console " . 'UpdateStationCache');
            $this->listenerEventForUpdateCache($this->cacheData);

        } catch (RedisConectionErrorException $e) {
            $this->io->success('Error Redis Conection ' . $e->getMessage() . ' ' . $e->getCode());
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

        $this->io->success(" [*] Waiting for Events called onUpdate. To exit Consumer press CTRL+C\n");

        $callback = static function ($msg) use ($cacheData) {

            $message = json_decode($msg->body, true);
            //var_dump($message);//uncoment for debug
            $cacheData->insert($message['uuidStation'], $message);
            echo 'Update cache for ' . $message['uuidStation'];
            if (isset($message['uuidUser']))
            {
                $cacheData->insert('findUserStations'.$message['uuidUser'], [''], 0);
                echo 'Update cache for findUserStations';
            }

        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

}
