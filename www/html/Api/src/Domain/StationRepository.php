<?php

namespace App\Domain;

interface StationRepository
{
	public function createStation(Station $station):void;

	public function findStation(string $uuidStation): Station;

	public function findAllStation():array;
}