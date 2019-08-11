<?php

namespace App\Domain;

use Symfony\Component\HttpFoundation\Request;

class Station
{

	private $uuidStation;
	private $uuidUser;
	private $latitud;
	private $longitud;
	private $postalCode;
	private $temp;
	private $humidity;
	private $presion;
	private $location;

	public function __construct(
			$uuidStation,
			$uuidUser,
			$latitud,
			$longitud,
			$postalCode,
			$temp,
			$humidity,
			$presion,
			$location
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
	}

	public function __toString()
	{
		return $this->uuidStation;
	}
	public static function buildStation(Request $request):Station
	{
		$data = [];
		$data['uuidStation']=$request->get('uuidStation');
		$data['uuidUser']=$request->get('uuidUser');
		$data['latitud']=$request->get('latitud');
		$data['longitud']=$request->get('longitud');
		$data['postalCode']=$request->get('postalCode');
		$data['temp']=$request->get('temp');
		$data['humidity']=$request->get('humidity');
		$data['presion']=$request->get('presion');
		$data['location']=$request->get('location');

		foreach ($data as $datafield=>$value)
		{
			if (empty($value))
			{
				throw new StationErrorException('Missing argument '.$datafield.' in request ', 400);
			}
		}

		return new self(
			$data['uuidStation'],
			$data['uuidUser'],
			$data['latitud'],
			$data['longitud'],
			$data['postalCode'],
			$data['temp'],
			$data['humidity'],
			$data['presion'],
			$data['location']
		);
	}


	public function getStationLikeArray():array
	{
		return get_object_vars($this);
	}

	/**
	 * @return mixed
	 */
	public function getUuidUser():string
	{
		return (string)$this->uuidUser;
	}

	/**
	 * @return mixed
	 */
	public function getLatitud():float
	{
		return (float)$this->latitud;
	}

	/**
	 * @return mixed
	 */
	public function getLongitud():float
	{
		return (float)$this->longitud;
	}

	/**
	 * @return mixed
	 */
	public function getPostalCode():string
	{
		return (string)$this->postalCode;
	}

	/**
	 * @return mixed
	 */
	public function getTemp():int
	{
		return (int)$this->temp;
	}

	/**
	 * @return mixed
	 */
	public function getHumidity():int
	{
		return (int)$this->humidity;
	}

	/**
	 * @return mixed
	 */
	public function getPresion():float
	{
		return (float)$this->presion;
	}

	/**
	 * @return mixed
	 */
	public function getLocation()
	{
		return $this->location;
	}

}