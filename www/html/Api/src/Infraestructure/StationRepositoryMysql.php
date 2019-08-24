<?php

namespace App\Infraestructure;

use App\Aplication\Station\FindRemotePredictionStations;
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

        try {
            $this->conect = new PDO(
            //"mysql:host=localhost;dbname={$_SERVER['DB_MYSQL']}",
                "mysql:host=$host;dbname={$_SERVER['DB_MYSQL']}",
                $_SERVER['USER_MYSQL'],
                $_SERVER['PASS_MYSQL']
            );

        } catch (PDOException $exception) {
            throw new StationErrorException('Internal Server error', 500);
            //$stationErrorResponse->setMoreInfo($exception);
            //throw $stationErrorResponse;
        }
    }

    public function createStation(Station $station): void
    {
        try {

            $this->findStation((string)$station);
            throw new Exception('Station already exists try tu update, use method PUT instead', 400);

        } catch (StationErrorException $e) {
            $uuidStation = (string)$station;
            $uuidUser = $station->getUuidUser();
            $latitud = $station->getLatitud();
            $longitud = $station->getLongitud();
            $postalCode = $station->getPostalCode();
            $temp = $station->getTemp();
            $humidity = $station->getHumidity();
            $presion = $station->getPresion();
            $location = $station->getLocation();
            $state = $station->getState();

            $fields = 'INSERT INTO `station`(uuidStation,uuidUser,latitud,longitud,postalCode,temp,humidity,presion,location,state)';
            $values = ' VALUES (?,?,?,?,?,?,?,?,?,?)';
            $query = $fields . $values;

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
            $statment->bindParam(10, $state);
            if (!$statment->execute()) {
                throw new StationErrorException('Could not insert value, check your request; user most exist and CHANGE your uuidStation', 400);
            }
        }
    }

    public function findStation(string $uuidStation): Station
    {
        $select = 'select uuidStation, uuidUser, latitud, longitud, postalCode,  temp, humidity, presion, location, state, timestamp';
        $from = ' from `station` where `station`.`uuidStation` = ? and deletedStation = ' . '0' . '';
        $query = $select . $from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $uuidStation);
        $stmt->execute();

        $response = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($response)) {
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
                $response['location'],
                $response['state']//esto es para el nuevo campo abria que agregarlo en las conversiones de redis
            );
            $station->setTimestamp($response['timestamp']);
            $station->setHistoric($this->findHistorycStation($uuidStation));
            try {

                $station->setPredictions(
                    $this->findPredictionsStation($response['postalCode']
                    )
                );
            } catch (LocationCodeError $exception) {
            } catch (ApiConectionError $exception) {
            }

            return $station;
        }
        throw new StationErrorException('Station ' . $uuidStation . ' not Found', 404);

    }

    private function findHistorycStation(string $uuidStation): array
    {
        $select = 'select temp, humidity, presion, UNIX_TIMESTAMP(timestamp) as timestamp';
        $from = ' from `StationHistory` where uuidStation = ?';
        $query = $select . $from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $uuidStation);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function findPredictionsStation($postalCode)
    {
        try {
            $apiRepository = new StationRemoteRepositoryApi();
            $findPredictions = new FindRemotePredictionStations($apiRepository, $this);

            return $findPredictions->findPredictionsByPostalCode($postalCode);

        } catch (LocationCodeError $e) {
        }
    }

    public function findLocationCode($postalCode): string
    {
        $select = 'select locationCode';
        $from = ' from `citiesZip` where `postalCode` = ? ';
        $query = $select . $from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $postalCode);

        if ($stmt->execute()) {
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($response)) {
                return $response['locationCode'];
            }
        }
        throw new LocationCodeError();
    }

    public function findAllStation(): array
    {
        $stations = [];
        $select = 'select uuidStation,uuidUser,latitud,longitud,postalCode,temp,humidity,presion,location,state,timestamp';
        $from = ' from station where deletedStation = ' . '0' . '';
        $query = $select . $from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $uuidStation);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);// lo tomo-all para que sea mas rapida la consulta

        $count = count($response);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($response[$i]) && !empty($response[$i]['uuidStation'])) {
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
                    $response[$i]['location'],
                    $response[$i]['state']
                );
                $station->setTimestamp($response[$i]['timestamp']);
                $station->setHistoric($this->findHistorycStation($response[$i]['uuidStation']));
                //$station->setPredictions($this->findPredictionsStation($response[$i]['postalCode']));

                $stations[] = $station;
            }

        }
        if (empty($stations)) {
            throw new StationErrorException('Stations not Found ', 404);
        }

        return $stations;
    }

    public function updateStation(Station $station): void
    {
        $uuidStation = (string)$station;
        $uuidUser = $station->getUuidUser();
        $latitud = $station->getLatitud();
        $longitud = $station->getLongitud();
        $postalCode = $station->getPostalCode();
        $temp = $station->getTemp();
        $humidity = $station->getHumidity();
        $state = $station->getState();

        $update = "UPDATE `station` SET ";
        $values = "uuidUser = :uuidUser, latitud =:latitud, longitud=:longitud, postalCode=:postalCode, temp=:temp, humidity=:humidity, state=:state";
        $where = " WHERE uuidStation = :uuidStation";

        $query = $update . $values . $where;
        $statment = $this->conect->prepare($query);

        $statment->bindValue(':uuidStation', $uuidStation);
        $statment->bindValue(':uuidUser', $uuidUser);
        $statment->bindValue(':latitud', $latitud);
        $statment->bindValue(':longitud', $longitud);
        $statment->bindValue(':postalCode', $postalCode);
        $statment->bindValue(':temp', $temp);
        $statment->bindValue(':humidity', $humidity);
        $statment->bindValue(':state', $state);
        $statment->execute();
    }

    public function deleteStation($uuidStation): void
    {
        $update = "UPDATE `station` SET ";
        $values = "deletedStation = '1'";
        $where = " WHERE uuidStation = :uuidStation";

        $query = $update . $values . $where;
        $statment = $this->conect->prepare($query);

        $statment->bindValue(':uuidStation', $uuidStation);
        $statment->execute();
    }

    public function addStationHistory(StationHistory $stationHistory): void
    {
        $uuidStation = (string)$stationHistory;
        $temp = $stationHistory->getTemp();
        $humidity = $stationHistory->getHumidity();
        $presion = $stationHistory->getPresion();

        $fields = 'INSERT INTO `StationHistory`(uuidStation,temp,humidity,presion)';
        $values = ' VALUES (?,?,?,?)';
        $query = $fields . $values;

        $statment = $this->conect->prepare($query);

        $statment->bindParam(1, $uuidStation);
        $statment->bindParam(2, $temp);
        $statment->bindParam(3, $humidity);
        $statment->bindParam(4, $presion);

        if (!$statment->execute()) {
            throw new StationErrorException('Could not insert value, check your request; station most exist examples values temp=28.99, humidity=89.99, presion=1214.79', 400);
        }
    }

    public function findUserStations($uuidUser): array
    {
        $stations = [];
        $select = 'select uuidStation,uuidUser,latitud,longitud,postalCode,temp,humidity,presion,location,state,timestamp';
        $from = ' from station where deletedStation = ' . '0' . ' and `station`.`uuidUser` = ?';
        $query = $select . $from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $uuidUser);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = count($response);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($response[$i]) && !empty($response[$i]['uuidStation'])) {
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
                    $response[$i]['location'],
                    $response[$i]['state']
                );
                $station->setTimestamp($response[$i]['timestamp']);
                $station->setHistoric($this->findHistorycStation($response[$i]['uuidStation']));
                //$station->setPredictions($this->findPredictionsStation($response[$i]['postalCode']));

                $stations[] = $station;
            }

        }
        if (empty($stations)) {
            throw new StationErrorException('Stations not Found ', 404);
        }

        return $stations;
    }

    public function findStationsByPostalCode($postalCode): array
    {
        $select = 'select uuidStation, uuidUser, latitud, longitud, postalCode,  temp, humidity, presion, location, state,timestamp';
        $from = ' from `station` where `station`.`postalCode` = ? and deletedStation = ' . '0' . '';
        $query = $select . $from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $postalCode);
        $stmt->execute();

        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $count = count($response);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($response[$i]) && !empty($response[$i]['uuidStation'])) {
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
                    $response[$i]['location'],
                    $response[$i]['state']
                );
                $station->setTimestamp($response[$i]['timestamp']);
                //$station->setHistoric($this->findHistorycStation($response[$i]['uuidStation']));
                //$station->setPredictions($this->findPredictionsStation($response[$i]['postalCode']));

                $stations[] = $station;
            }

        }
        if (empty($stations)) {
            throw new StationErrorException('Stations not Found ', 404);
        }

        return $stations;
    }
}