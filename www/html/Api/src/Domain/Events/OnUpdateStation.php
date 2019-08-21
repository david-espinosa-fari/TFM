<?php


namespace App\Domain\Events;


use App\Domain\Station;

class OnUpdateStation implements MeteosalleEvents
{
    private const EVENT_NAME = 'onUpdate';
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
        return self::EVENT_NAME;
    }

    public function getDataAsJson():string
    {
        return json_encode($this->payload);
    }
}