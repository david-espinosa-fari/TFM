<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StartWorkersCommand extends Command
{
    protected static $defaultName = 'startWorkers';

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $arrayOfConsumers = ['UpdateStationCache','stationEventListenerWorker' ];

        $count = count($arrayOfConsumers);

        for ($i = 0; $i < $count; $i++)
        {
           echo shell_exec('nohup ' . "start php bin/console " . $arrayOfConsumers[$i]);
        }

    }
}
