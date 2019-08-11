<?php

namespace App\Aplication\Station;

use App\Domain\Station;
use App\Domain\StationRepository;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class CreateStation
{

	/**
	 * @var StationRepository
	 */
	private $repository;

	public function __construct(StationRepository $repository)
	{
		$this->repository = $repository;
	}

	public function __invoke(Station $station)
	{
		$this->repository->createStation($station);
	}
}