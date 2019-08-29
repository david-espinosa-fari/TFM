<?php


namespace App\Infraestructure;


use App\Domain\Error\ApiConectionError;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\Service\FindPostalCodeByLocation;
use App\Domain\Station;
use App\Domain\StationPrediction;
use App\Domain\StationRemoteRepository;
use ErrorException;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class StationRemoteRepositoryApi implements StationRemoteRepository
{
    private const ADDRESS_API = 'http://34.66.90.82:9000/v1/';
    private $httpClient;

    public function __construct()
    {

        $this->httpClient = HttpClient::create(
            [
                'http_version' => '2.0',
                'headers' =>
                    [
                        'User-Agent' => 'MeteoSalleMiddel',
                        'Content-Type' => 'application/json'
                    ]
            ], 10);

    }

    public function findAllStation(): array
    {
        try {
            $response = $this->httpClient->request(
                'GET', self::ADDRESS_API . 'stations/',
                [
                    'buffer' => true
                ]);

            if (200 !== $response->getStatusCode()) {
            }
            $response = $response->getContent(true);
            return $this->convertApiResponseToDomain($response);

        } catch (TransportExceptionInterface $e) {
            throw new ApiConectionError('Transport Error ' . $e->getMessage(), $e->getCode());
        } catch (ClientExceptionInterface $e) {
            throw new ApiConectionError('Client Error ' . $e->getMessage(), $e->getCode());
        } catch (RedirectionExceptionInterface $e) {
            throw new ApiConectionError('Redirection Error ' . $e->getMessage(), $e->getCode());
        } catch (ServerExceptionInterface $e) {
            throw new ApiConectionError('Server Error ' . $e->getMessage(), $e->getCode());
        } catch (ErrorException $e) {
            throw new ApiConectionError('Server Error ' . $e->getMessage(), $e->getCode());
        }
    }

    private function convertApiResponseToDomain(string $apiRes): array
    {

        $apiResponse = json_decode($apiRes, true);

        $stations = [];

        if (isset($apiResponse)) {
            $count = count($apiResponse);
            for ($i = 0; $i < $count; $i++) {
                $uuidStation = $apiResponse[$i]['stationId'];//
                $location = $apiResponse[$i]['location'];//
                $state = $apiResponse[$i]['state'];
                $latitud = $apiResponse[$i]['latitude'];
                $longitud = $apiResponse[$i]['longitude'];
                $uuidUser = $apiResponse[$i]['idApi'];//
                $temp = $apiResponse[$i]['temperature'];
                $humidity = $apiResponse[$i]['humidity'];
                $presion = $apiResponse[$i]['pressure'];
                $localRepo = new FindPostalCodeByLocation(new StationRepositoryMysql($_SERVER['HOST_MYSQL']));
                $postalCode = $localRepo($uuidStation);//$apiResponse[$i]['postalCode'];
                $timeStamp = $apiResponse[$i]['timeStamp'] ?? time();


                $station = new Station($uuidStation, $uuidUser, $latitud, $longitud, $temp, $humidity, $presion, $location, $state, $postalCode);
                $station->setTimestamp($timeStamp);

                $stations[] = $station;

            }
            return $stations;
        }

        throw new RemoteStationsNotFound('Remote stations not found ', 500);

    }

    public function findPredictionsByLocationCode($locationCode): ?array
    {
        try {
            $response = $this->httpClient->request(
                'GET', self::ADDRESS_API . 'prediction/' . $locationCode,
                [
                    'buffer' => true
                ]);
            if (200 !== $response->getStatusCode()) {
                throw new ApiConectionError('Error Status code ' . $response->getStatusCode());
            }
            $response = $response->getContent(false);

            if (isset($response)) {
                return $this->convertPredictionsResponseToDomain($response);
            }
            throw new ApiConectionError('No predictions for this location', 404);


        } catch (TransportExceptionInterface $e) {
            throw new ApiConectionError('Transport Error ' . $e->getMessage(), $e->getCode());
        } catch (ClientExceptionInterface $e) {
            throw new ApiConectionError('Client Error ' . $e->getMessage(), $e->getCode());
        } catch (RedirectionExceptionInterface $e) {
            throw new ApiConectionError('Redirection Error ' . $e->getMessage(), $e->getCode());
        } catch (ServerExceptionInterface $e) {
            throw new ApiConectionError('Remote server Error ' . $e->getMessage(), $e->getCode());
        } catch (RemoteStationsNotFound $e) {
            throw new ApiConectionError('Remote server Error ' . $e->getMessage(), $e->getCode());
        }

    }

    public function convertPredictionsResponseToDomain($apiRes): array
    {
        $apiResponse = json_decode($apiRes, true);
        $predictions = [];

        if (isset($apiResponse)) {
            $count = count($apiResponse);
            for ($i = 0; $i < $count; $i++) {
                $timeStamp = $apiResponse[$i]['timeStamp'];
                $location = $apiResponse[$i]['location'];//
                $state = $apiResponse[$i]['state'];
                $latitud = $apiResponse[$i]['latitude'];
                $longitud = $apiResponse[$i]['longitude'];
                $temp = $apiResponse[$i]['temperature'];
                $humidity = $apiResponse[$i]['humidity'];
                $presion = $apiResponse[$i]['pressure'];
                $prediction = new StationPrediction(
                    $timeStamp,
                    $location,
                    $state,
                    $latitud,
                    $longitud,
                    $temp,
                    $humidity,
                    $presion

                );

                $predictions[] = $prediction;

            }
            return $predictions;
        }
    }

    public function findStationsByLocationCode($locationCode): array
    {

        try {
            $response = $this->httpClient->request(
                'GET', self::ADDRESS_API . 'station/' . $locationCode,
                [
                    'buffer' => true
                ]);

            if (200 !== $response->getStatusCode()) {

                throw new ApiConectionError('Error Status code ' . $response->getStatusCode());
            }
            $response = $response->getContent(false);

            return $this->convertApiResponseToDomain($response);

        } catch (TransportExceptionInterface $e) {
            throw new ApiConectionError('Transport Error ' . $e->getMessage(), $e->getCode());
        } catch (ClientExceptionInterface $e) {
            throw new ApiConectionError('Client Error ' . $e->getMessage(), $e->getCode());
        } catch (RedirectionExceptionInterface $e) {
            throw new ApiConectionError('Redirection Error ' . $e->getMessage(), $e->getCode());
        } catch (ServerExceptionInterface $e) {
            throw new ApiConectionError('Server Error ' . $e->getMessage(), $e->getCode());
        } catch (RemoteStationsNotFound $e) {
            throw new ApiConectionError('Server Error ' . $e->getMessage(), $e->getCode());
        }

    }
}

