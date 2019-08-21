<?php

namespace App\Domain;

interface CacheDataRepository
{

	public function find($hashMd5FromQuery);

	public function insert(string $valueToInsert, array $dataToCache, $timeExpire): void;
}