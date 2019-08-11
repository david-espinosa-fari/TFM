<?php

namespace App\Infraestructure;

use App\Domain\Station;
use App\Domain\StationErrorException;
use App\Domain\StationRepository;
use Exception;
use PDO;
use PDOException;

class StationRepositoryMysql implements StationRepository
{
	private $conect;

	/**
	 * StationRepository constructor.
	 * @throws StationErrorException
	 */

	public function __construct()
	{
		try
		{
			$this->conect = new PDO(
				"mysql:host={$_SERVER['HOST_MYSQL']};dbname={$_SERVER['DB_MYSQL']}",
				$_SERVER['USER_MYSQL'],
				$_SERVER['PASS_MYSQL']
			);

		}
		catch (PDOException $exception)
		{
			$stationErrorResponse = new StationErrorException('Internal Server error', 500);
			$stationErrorResponse->setMoreInfo($exception);
			throw $stationErrorResponse;
		}
	}

	public function createStation(Station $station):void
	{
		try{

			$this->findStation((string)$station);
			throw new Exception('Station already exists try tu update, use method PUT instead', 400);

		}catch (StationErrorException $e)
		{
			$uuidStation = (string)$station;
			$uuidUser = $station->getUuidUser();
			$latitud = $station->getLatitud();
			$longitud = $station->getLongitud();
			$postalCode = $station->getPostalCode();
			$temp = $station->getTemp();
			$humidity = $station->getHumidity();
			$presion = $station->getPresion();
			$location = $station->getLocation();

			$fields = 'INSERT INTO `station`(uuidStation,uuidUser,latitud,longitud,postalCode,temp,humidity,presion,location)';
			$values = ' VALUES (?,?,?,?,?,?,?,?,?)';
			$query = $fields.$values;

			$statment = $this->conect->prepare($query);

			$statment->bindParam(1, $uuidStation);
			$statment->bindParam(2, $uuidUser);
			$statment->bindParam(3, $latitud);
			$statment->bindParam(4, $longitud);
			$statment->bindParam(5, $postalCode);
			$statment->bindParam(6, $temp);
			$statment->bindParam(7, $humidity);
			$statment->bindParam(8, $presion);
			$statment->bindParam(9, $location);
			if (!$statment->execute())
			{
				throw new StationErrorException('Could not insert value, check your request; user most exist', 400);
			}
		}
	}

	public function findStation(string $uuidStation): Station
	{
		$select = 'select uuidStation, uuidUser, latitud, longitud, postalCode,  temp, humidity, presion, location';
		$from = ' from `station` where `station`.`uuidStation` = ?';
		$query = $select.$from;

		$stmt = $this->conect->prepare($query);
		$stmt->bindParam(1, $uuidStation);
		$stmt->execute();

		$response = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!empty($response))
		{
		$station = new Station
		(
			$response['uuidStation'],
			$response['uuidUser'],
			$response['latitud'],
			$response['longitud'],
			$response['postalCode'],
			$response['temp'],
			$response['humidity'],
			$response['presion'],
			$response['location']
		);

			return $station;
		}
		throw new StationErrorException('Station ' . $uuidStation . ' not Found', 404);

	}


}