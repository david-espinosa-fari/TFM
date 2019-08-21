<?php


namespace App\Domain\Events;


interface MeteosalleEvents
{
    public function __toString():string;

    public function getDataAsJson():string;
}