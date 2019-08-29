<?php


namespace App\Domain;


class StationPrediction
{

    private $latitud;
    private $longitud;
    private $temp;
    private $humidity;
    private $state;

    private $presion;
    private $location;
    private $timeStamp;

    public function __construct(
        $timeStamp,
        $location,
        $state,
        $latitud,
        $longitud,
        $temp,
        $humidity,
        $presion
    )
    {
        $this->timeStamp = $timeStamp;
        $this->location = $location;
        $this->state = $state;
        $this->latitud = $latitud;
        $this->longitud = $longitud;
        $this->temp = $temp;
        $this->humidity = $humidity;
        $this->presion = $presion;
    }

    public function getStationLikeArray(): array
    {
        return get_object_vars($this);
    }
}