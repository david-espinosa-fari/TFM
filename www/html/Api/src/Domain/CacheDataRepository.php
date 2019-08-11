<?php

namespace App\Domain;

interface CacheDataRepository
{

	public function find($hashMd5FromQuery);

	public function insert($keyHashMd5, $dataToCache, $timeExpire): void;
}