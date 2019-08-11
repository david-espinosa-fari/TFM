<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 18/03/2019
 * Time: 21:13
 */

namespace App\Infraestructure;

use App\Domain\Model\StationDto;
use App\Domain\Services\ParametersInput;
use \PDO;
use \PDOException;
use App\Domain\Model\StationRepository;
//use App\Domain\Model\Station;
use App\Domain\Error\JsonErrorResponse;

final class ImplementStationRepositoryMysql implements StationRepository
{

	private $conect;

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
			$jsonErrorResponse = new JsonErrorResponse('Internal Server error', 500);
			$jsonErrorResponse->setMoreInfo($exception);
			throw $jsonErrorResponse;
		}
	}

	/*public function findAll(): array
	{
		$parameters = [];
		$parametersInput = new ParametersInput($parameters);

		return $this->findBy($parametersInput);
	}*/

	public function searchOne(string $stationId): StationDto
	{

		$query = "select * from `station` where `station`.`uuid_station` = ?";

		$stmt = $this->conect->prepare($query);
		$stmt->bindParam(1, $stationId);
		$stmt->execute();

		$response = $stmt->fetchObject(StationDto::class);

		if (!is_object($response))
		{
			throw new JsonErrorResponse('Station ' . $stationId . ' not Found', 404);
		}

		return $response;
	}

	public function findBy(ParametersInput $parametersInput): array
	{
		$arrayOfStations = [];

		$query = $this->buildQueryForSelectAtMysql($parametersInput);

		$stmt = $this->conect->prepare("$query");
		$stmt->execute();

		while ($response = $stmt->fetchObject(StationDto::class))
		{
			if (!is_object($response))
			{
				break;
			}

			$arrayOfStations[] = $response;
		}
		if (empty($arrayOfStations))
		{
			throw new JsonErrorResponse('Element not found', 404);
		}

		return $arrayOfStations;
	}

	public function buildQueryForSelectAtMysql(ParametersInput $parametersInput): string
	{
		$filters = '*';
		$conditions = '';
		$queryLatLong = '';
		$queryParam = '';

		if (!$parametersInput->isEmptyFilters())
		{
			$filters = implode(', ', $parametersInput->getFilters());
		}
		if (!$parametersInput->isEmptyParameters())
		{
			foreach ($parametersInput->getParameters() as $parameter => $value)
			{
				if (!($value === '') || !($value === null))
				{
					$tempArray[] = "$parameter = \"$value\"";
					$queryParam = implode(' and ', $tempArray);
				}
			}
		}
		if (!$parametersInput->isEmptyLatLong())
		{
			if (count($parametersInput->getLatitud()) === 2
				&& count($parametersInput->getLongitud()) === 2)//si el arreglo de latitud y longitud tienen dos elementos cada uno
			{
				$tempArray1[] = ' latitud >= "' . $parametersInput->getLatitud()[0] . '"';
				$tempArray2[] = ' latitud <= "' . $parametersInput->getLatitud()[1] . '"';
				$tempArray3[] = ' longitud >= "' . $parametersInput->getLongitud()[0] . '"';
				$tempArray4[] = ' longitud <= "' . $parametersInput->getLongitud()[1] . '"';

				$tempArray = array_merge($tempArray1, $tempArray2, $tempArray3, $tempArray4);
				$queryLatLong = implode(' and ', $tempArray);
			}
			elseif (count($parametersInput->getLatitud()) === 1
				&& count($parametersInput->getLongitud()) === 1)
			{
				$tempArray1[] = ' latitud = "' . $parametersInput->getLatitud()[0] . '"';
				$tempArray2[] = ' longitud = "' . $parametersInput->getLongitud()[0] . '"';
				$tempArray = array_merge($tempArray1, $tempArray2);
				$queryLatLong = implode(' and ', $tempArray);
			}

		}
		if (!$parametersInput->isEmptyLatLong() && !$parametersInput->isEmptyParameters())
		{
			$conditions = " where " . $queryParam . " and " . $queryLatLong;
			//dump("conditions and LarLong o Parameter ".$conditions);
		}
		elseif (!$parametersInput->isEmptyLatLong() || !$parametersInput->isEmptyParameters())
		{
			$conditions = " where " . $queryParam . $queryLatLong;
			//dump("conditions or LarLong o Parameter ".$conditions);
		}

		/** @noinspection OneTimeUseVariablesInspection */
		$query = "select " . $filters . " from `station` " . $conditions;

		//var_dump($query); //uncomment for debug
		return $query;

	}

	public function insertStation(ParametersInput $parametersInput): void
	{
		try
		{
			$this->searchOne($parametersInput->getStationId());//Lanza una excepcion si no encuentra el elemento

			throw new JsonErrorResponse('Station ' . $parametersInput->getStationId() . ' already exist Conflict', 409);

		}
		catch (JsonErrorResponse $jsonErrorResponse)
		{
			//var_dump($jsonErrorResponse->getErrorCode());//uncoment for debug
			if ($jsonErrorResponse->getErrorCode() === 404)
			{
				$this->insert($parametersInput);
			}
			else
			{
				throw $jsonErrorResponse;
			}
		}
	}

	public function update(ParametersInput $parametersInput, string $stationId): void
	{
		if (!$parametersInput->isEmptyParameters())
		{
			$arrayValues = $parametersInput->getParameters();
		}
		if (!$parametersInput->isEmptyLatLong())
		{
			$arrayValues['latitud'] = $parametersInput->getLatitud()[0];
			$arrayValues['longitud'] = $parametersInput->getLongitud()[0];
		}
		if (!empty($arrayValues))
		{
			foreach ($arrayValues as $param => $value)
			{
				$tempArray[] = "$param = :$param";
			}
			$setParam = implode(',', $tempArray);

			$query = "UPDATE `station` SET " . $setParam . " WHERE uuid_station = " . ":uuid_station";
			$statment = $this->conect->prepare($query);

			foreach ($arrayValues as $param => $value)
			{
				$statment->bindValue(':' . $param, $value);
			}
			$statment->bindValue(':uuid_station', $stationId);
			$statment->execute();
		}
		else
		{
			throw new JsonErrorResponse('No value to update Bad request', 400);
		}
//		echo "function update \n";
//		var_dump($this->findBy(new ParametersInput(['uuid_station' => $stationId])));

	}

	public function delete(string $stationId): void
	{
		try
		{
			$this->searchOne($stationId);

			$query = "DELETE FROM station WHERE uuid_station = " . ":uuid_station";
			$statment = $this->conect->prepare($query);

			$statment->bindValue(':uuid_station', $stationId);
			$statment->execute();
			//var_dump($statment->execute());
			echo "function deleted \n";
		}
		catch (JsonErrorResponse $e)
		{
			throw $e;
		}
	}

	private function insert(ParametersInput $parametersInput): void
	{
		$userId = $parametersInput->getUserId();
		$stationId = $parametersInput->getStationId();
		$temp = $parametersInput->getTemp();
		$lat = $parametersInput->getLatitud()[0];
		$lon = $parametersInput->getLongitud()[0];
		$population = $parametersInput->getPopulation();

		$query = 'INSERT INTO `station`(uuid_user,uuid_station,temp,latitud,longitud,population) VALUES (?, ?, ?, ?, ?, ?)';
		$statment = $this->conect->prepare($query);

		$statment->bindParam(1, $userId);
		$statment->bindParam(2, $stationId);
		$statment->bindParam(3, $temp, PDO::PARAM_INT);
		$statment->bindParam(4, $lat);
		$statment->bindParam(5, $lon);
		$statment->bindParam(6, $population);
		$statment->execute();
	}
}