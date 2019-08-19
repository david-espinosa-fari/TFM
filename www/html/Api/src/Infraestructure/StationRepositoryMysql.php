<?php

namespace App\Infraestructure;

use App\Aplication\Station\FindRemotePredictionStations;
use App\Aplication\UpdateCache;
use App\Domain\Error\ApiConectionError;
use App\Domain\Error\LocationCodeError;
use App\Domain\Error\RedisConectionErrorException;
use App\Domain\Station;
use App\Domain\StationErrorException;
use App\Domain\StationHistory;
use App\Domain\StationRepository;
use Exception;
use PDO;
use PDOException;

final class StationRepositoryMysql implements StationRepository
{
	private $conect;

    /**
     * StationRepository constructor.
     * @param $host
     */

	public function __construct(string $host)
	{

		try
		{
			$this->conect = new PDO(
				//"mysql:host=localhost;dbname={$_SERVER['DB_MYSQL']}",
				"mysql:host=$host;dbname={$_SERVER['DB_MYSQL']}",
				$_SERVER['USER_MYSQL'],
				$_SERVER['PASS_MYSQL']
			);

		}
		catch (PDOException $exception)
		{
			throw new StationErrorException('Internal Server error', 500);
			//$stationErrorResponse->setMoreInfo($exception);
			//throw $stationErrorResponse;
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
				throw new StationErrorException('Could not insert value, check your request; user most exist and CHANGE your uuidStation', 400);
			}
		}
	}

	public function findStation(string $uuidStation): Station
	{
		$select = 'select uuidStation, uuidUser, latitud, longitud, postalCode,  temp, humidity, presion, location';
		$from = ' from `station` where `station`.`uuidStation` = ? and deletedStation = '.'0'.'';
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
			$station->setHistoric($this->findHistorycStation($uuidStation));
			try{

                $station->setPredictions(
                    $this->findPredictionsStation($response['postalCode']
                    )
                );
            }catch (LocationCodeError $exception){
            }catch(ApiConectionError $exception){
            }

			return $station;
		}
		throw new StationErrorException('Station ' . $uuidStation . ' not Found', 404);

	}

	private function findHistorycStation(string $uuidStation):array
	{
		$select = 'select temp, humidity, presion, timestamp';
		$from = ' from `StationHistory` where uuidStation = ?';
		$query = $select.$from;

		$stmt = $this->conect->prepare($query);
		$stmt->bindParam(1, $uuidStation);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}

    public function findLocationCode($postalCode):string
    {
        $select = 'select locationCode';
        $from = ' from `citiesZip` where `postalCode` = ? ';
        $query = $select.$from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $postalCode);

        if ($stmt->execute())
        {
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($response))
            {
                return $response['locationCode'];
            }
        }
        throw new LocationCodeError();
	}

	public function findPredictionsStation($postalCode): array
    {
        try {
            $apiRepository = new StationRemoteRepositoryApi();
            $findPredictions = new FindRemotePredictionStations($apiRepository,$this);

            return $findPredictions->findPredictionsByPostalCode($postalCode);

        } catch (LocationCodeError $e) {
        }




		/*esto iria a la api de marc a buscar las predicciones para
		un codigo postal para una estacion


		$json = json_encode([
			[
            "zipCode"=>$postalCode,
			"temp"=> 33,
			"humidity"=> 90,
			"presion"=>14.7,
			"timestamp"=> "2019 - 08 - 11 11:56:55"
			],
			[

				"temp"=> 10,
				"humidity"=> 30,
				"presion"=>14.6,
				"timestamp"=> "2019 - 08 - 12 12:56:55"
			],
			[

				"temp"=> 24,
				"humidity"=> 90,
				"presion"=>14.7,
				"timestamp"=> "2019 - 08 - 13 13:56:55"
			]
			]);
		return json_decode($json,true);*/
	}

	public function findAllStation():array
	{
		$stations=[];
		$select = 'select uuidStation,uuidUser,latitud,longitud,postalCode,temp,humidity,presion,location';
		$from = ' from station where deletedStation = '.'0'.'';
		$query = $select.$from;

		$stmt = $this->conect->prepare($query);
		$stmt->bindParam(1, $uuidStation);
		$stmt->execute();
		$response = $stmt->fetchAll(PDO::FETCH_ASSOC);// lo tomo-all para que sea mas rapida la consulta

		$count = count($response);
		for ($i=0;$i<$count;$i++)
		{
			if (!empty($response[$i]) && !empty($response[$i]['uuidStation']))
			{
				$station = new Station
				(
					$response[$i]['uuidStation'],
					$response[$i]['uuidUser'],
					$response[$i]['latitud'],
					$response[$i]['longitud'],
					$response[$i]['postalCode'],
					$response[$i]['temp'],
					$response[$i]['humidity'],
					$response[$i]['presion'],
					$response[$i]['location']
				);
				$station->setHistoric($this->findHistorycStation($response[$i]['uuidStation']));
				//$station->setPredictions($this->findPredictionsStation($response[$i]['postalCode']));

				$stations[] = $station;
			}

		}
        if (empty($stations))
        {
            throw new StationErrorException('Stations not Found ',404);
        }

		return $stations;
	}

	public function updateStation(Station $station):void
	{
		$uuidStation = (string)$station;
		$uuidUser = $station->getUuidUser();
		$latitud = $station->getLatitud();
		$longitud = $station->getLongitud();
		$postalCode = $station->getPostalCode();
		$temp = $station->getTemp();
		$humidity = $station->getHumidity();

		$update = "UPDATE `station` SET ";
		$values = "uuidUser = :uuidUser, latitud =:latitud, longitud=:longitud, postalCode=:postalCode, temp=:temp, humidity=:humidity";
		$where = " WHERE uuidStation = :uuidStation";

		$query = $update.$values.$where;
		$statment = $this->conect->prepare($query);

		$statment->bindValue(':uuidStation', $uuidStation);
		$statment->bindValue(':uuidUser', $uuidUser);
		$statment->bindValue(':latitud', $latitud);
		$statment->bindValue(':longitud', $longitud);
		$statment->bindValue(':postalCode', $postalCode);
		$statment->bindValue(':temp', $temp);
		$statment->bindValue(':humidity', $humidity);
		$statment->execute();
	}

	public function deleteStation($uuidStation):void
	{
		$update = "UPDATE `station` SET ";
		$values = "deletedStation = '1'";
		$where = " WHERE uuidStation = :uuidStation";

		$query = $update.$values.$where;
		$statment = $this->conect->prepare($query);

		$statment->bindValue(':uuidStation', $uuidStation);
		$statment->execute();
	}

	public function addStationHistory(StationHistory $stationHistory):void
	{
		$uuidStation = (string)$stationHistory;
		$temp = $stationHistory->getTemp();
		$humidity = $stationHistory->getHumidity();
		$presion = $stationHistory->getPresion();
		$timestamp = $stationHistory->getTimestamp();

		$fields = 'INSERT INTO `StationHistory`(uuidStation,temp,humidity,presion,timestamp)';
		$values = ' VALUES (?,?,?,?,?)';
		$query = $fields.$values;

		$statment = $this->conect->prepare($query);

		$statment->bindParam(1, $uuidStation);
		$statment->bindParam(2, $temp);
		$statment->bindParam(3, $humidity);
		$statment->bindParam(4, $presion);
		$statment->bindParam(5, $timestamp);
		if (!$statment->execute())
		{
			throw new StationErrorException('Could not insert value, check your request; station most exist', 400);
		}
	}
}