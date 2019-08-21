<?php


namespace App\Domain;

use App\Domain\Events\MeteosalleEvents;

interface TailMessageRepository
{
    public function publishEvent(MeteosalleEvents $event): void;
}