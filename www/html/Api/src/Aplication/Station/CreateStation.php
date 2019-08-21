<?php

namespace App\Aplication\Station;

use App\Domain\Events\OnUpdateStation;
use App\Domain\Station;
use App\Domain\StationRepository;
use App\Domain\TailMessageRepository;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class CreateStation
{

	/**
	 * @var StationRepository
	 */
	private $repository;
    /**
     * @var TailMessageRepository
     */
    private $tailMessageRepository;

    public function __construct(StationRepository $repository, TailMessageRepository $tailMessageRepository)
	{
		$this->repository = $repository;
        $this->tailMessageRepository = $tailMessageRepository;
    }

	public function __invoke(Station $station):void
	{
		$this->repository->createStation($station);

        $event = new OnUpdateStation($station);
        $this->tailMessageRepository->publishEvent($event);
	}
}