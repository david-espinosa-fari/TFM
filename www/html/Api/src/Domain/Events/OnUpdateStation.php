<?php


namespace App\Domain\Events;


use App\Domain\Station;

class OnUpdateStation implements MeteosalleEvents
{
    private const RABBIT_TAIL = 'onUpdate';
    /**
     * @var string
     */
    private $payload;

    public function __construct(Station $payload)
    {
        $this->payload = $payload->getStationLikeArray();

    }

    public function __toString():string
    {
        return self::RABBIT_TAIL;
    }

    public function getDataAsJson():string
    {
        return json_encode($this->payload);
    }
}