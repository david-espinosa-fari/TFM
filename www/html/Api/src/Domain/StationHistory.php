<?php

namespace App\Domain;

use Symfony\Component\HttpFoundation\Request;

final class StationHistory
{

	private $uuidStation;
	private $temp;
	private $humidity;
	private $presion;
	private $timestamp;

	private function __construct($uuidStation, $temp, $humidity, $presion, $timestamp)
	{

		$this->uuidStation = $uuidStation;
		$this->temp = $temp;
		$this->humidity = $humidity;
		$this->presion = $presion;
		$this->timestamp = $timestamp;
	}
	public function getStationHostoryLikeArray():array
	{
		return get_object_vars($this);
	}
	public function __toString():string
	{
		return $this->uuidStation;
	}

	public static function buildStationHistory($uuidStation, Request $request):StationHistory
	{
		$data['uuidStation'] = $uuidStation;
		$data['temp'] = $request->get('temp');
		$data['humidity'] = $request->get('humidity');
		$data['presion'] = $request->get('presion');
		$data['timestamp'] = $request->get('timestamp');

		foreach ($data as $datafield => $value)
		{
			if (empty($value))
			{
				throw new StationErrorException('Missing argument ' . $datafield . ' in request ', 400);
			}
		}

		return new self(
			$data['uuidStation'],
			$data['temp'],
			$data['humidity'],
			$data['presion'],
			$data['timestamp']
		);
	}
	/**
	 * @return mixed
	 */
	public function getTemp()
	{
		return $this->temp;
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
	public function getHumidity()
	{
		return $this->humidity;
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
	public function getPresion()
	{
		return $this->presion;
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
	public function getTimestamp()
	{
		return date("Y-m-d H:i:s", strtotime($this->timestamp));
	}

	/**
	 * @param mixed $timestamp
	 */
	public function setTimestamp($timestamp): void
	{
		$this->timestamp = $timestamp;
	}



}