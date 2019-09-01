<?php

namespace App\Domain;

use Symfony\Component\HttpFoundation\Request;

final class Station
{

    private $uuidStation;
    private $uuidUser;
    private $latitud;
    private $longitud;
    private $postalCode;
    private $temp;
    private $humidity;
    private $state;

    private $presion;
    private $location;
    private $historic;
    private $predictions;
    private $timestamp;

    public function __construct(
        $uuidStation,
        $uuidUser,
        $latitud,
        $longitud,
        $temp,
        $humidity,
        $presion,
        $location,
        $state,
        $postalCode
    )
    {
        $this->uuidStation = $uuidStation;
        $this->uuidUser = $uuidUser;
        $this->latitud = $latitud;
        $this->longitud = $longitud;
        $this->postalCode = $postalCode;
        $this->temp = $temp;
        $this->humidity = $humidity;
        $this->presion = $presion;
        $this->location = $location;
        $this->state = $state;
        $this->timestamp = time();

    }

    public static function buildStation(Request $request): Station
    {
        $data = [];
        $data['uuidStation'] = $request->get('uuidStation');
        $data['uuidUser'] = $request->get('uuidUser');
        $data['latitud'] = $request->get('latitud');
        $data['longitud'] = $request->get('longitud');
        $data['postalCode'] = $request->get('postalCode');
        $data['temp'] = $request->get('temp');
        $data['humidity'] = $request->get('humidity');
        $data['presion'] = $request->get('presion');
        $data['location'] = $request->get('location');
        $data['state'] = $request->get('state');

        foreach ($data as $datafield => $value) {
            if (empty($value)) {
                throw new StationErrorException('Missing argument ' . $datafield . ' in request ', 400);
            }
        }

        return new self(
            $data['uuidStation'],
            $data['uuidUser'],
            $data['latitud'],
            $data['longitud'],
            $data['temp'],
            $data['humidity'],
            $data['presion'],
            $data['location'],
            $data['state'],
            $data['postalCode']
        );
    }

    public function __toString()
    {
        return $this->uuidStation;
    }

    public function getStationLikeArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return mixed
     */
    public function getUuidUser(): string
    {
        return (string)$this->uuidUser;
    }

    /**
     * @param mixed $uuidUser
     */
    public function setUuidUser($uuidUser): void
    {
        $this->uuidUser = $uuidUser;
    }

    /**
     * @return mixed
     */
    public function getLatitud(): float
    {
        return (float)$this->latitud;
    }

    /**
     * @param mixed $latitud
     */
    public function setLatitud($latitud): void
    {
        $this->latitud = $latitud;
    }

    /**
     * @return mixed
     */
    public function getLongitud(): float
    {
        return (float)$this->longitud;
    }

    /**
     * @param mixed $longitud
     */
    public function setLongitud($longitud): void
    {
        $this->longitud = $longitud;
    }

    /**
     * @return mixed
     */
    public function getPostalCode(): string
    {
        return (string)$this->postalCode;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return mixed
     */
    public function getTemp(): int
    {
        return (int)$this->temp;
    }

    /**
     * @param mixed $temp
     */
    public function setTemp($temp): void
    {
        $this->temp = $temp;
    }

    /**
     * @return mixed
     */
    public function getHumidity(): int
    {
        return (int)$this->humidity;
    }

    /**
     * @param mixed $humidity
     */
    public function setHumidity($humidity): void
    {
        $this->humidity = $humidity;
    }

    /**
     * @return mixed
     */
    public function getPresion(): float
    {
        return (float)$this->presion;
    }

    /**
     * @param mixed $presion
     */
    public function setPresion($presion): void
    {
        $this->presion = $presion;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location): void
    {
        $this->location = $location;
    }

    /**
     * @param mixed $historic
     */
    public function setHistoric($historic): void
    {
        $this->historic = $historic;
    }

    /**
     * @param mixed $prediccions
     */
    public function setPredictions($prediccions): void
    {
        $this->predictions = $prediccions;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp): void
    {
        if (strtotime($timestamp) === false) {
            $this->timestamp = $timestamp;
        } else {

            $this->timestamp = strtotime($timestamp);
        }
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

}