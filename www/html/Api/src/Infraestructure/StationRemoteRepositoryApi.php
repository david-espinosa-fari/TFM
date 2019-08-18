<?php


namespace App\Infraestructure;


use App\Domain\Error\ApiConectionError;
use App\Domain\Error\RemoteStationsNotFound;
use App\Domain\Station;
use App\Domain\StationRemoteRepository;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class StationRemoteRepositoryApi implements StationRemoteRepository
{

    private $httpClient;
    private const ADDRESS_API = 'http://192.168.1.37:9000/';
   // private const ADDRESS_API = 'http://meteosalle.local/apiv1/stations/';


    public function __construct()
    {

        $this->httpClient = HttpClient::create(
            [
                'http_version' => '2.0',
                'headers' =>
                    [
                    'User-Agent'=>'MeteoSalleMiddel',
                    'Content-Type' => 'application/json'
                    ]
            ],10);

    }

    public function findAllStation():array
    {
        try {
            $response = $this->httpClient->request(
                'GET', self::ADDRESS_API .'stations/',
                [
                    'buffer' => true
                ]);
           // $response->getInfo('debug'); //uncoment for debug

            if (200 !== $response->getStatusCode()) {
                //$response->cancel();
            }
            $response = $response->getContent(false);

            return $this->convertApiResponseToDomain($response);

        } catch (TransportExceptionInterface $e) {
            throw new ApiConectionError('Transport Error '.$e->getMessage(),$e->getCode());
        } catch (ClientExceptionInterface $e) {
            throw new ApiConectionError('Client Error '.$e->getMessage(), $e->getCode());
        } catch (RedirectionExceptionInterface $e) {
            throw new ApiConectionError('Redirection Error '.$e->getMessage(), $e->getCode());
        } catch (ServerExceptionInterface $e) {
            throw new ApiConectionError('Server Error '.$e->getMessage(), $e->getCode());
        }
    }

    public function findPredictionsByLocationCode($locationCode):array
    {
        try {
            $response = $this->httpClient->request(
                'GET', self::ADDRESS_API .'prediction/'. $locationCode,
                [
                    'buffer' => true
                ]);
             //$response->getInfo('debug'); //uncoment for debug

            if (200 !== $response->getStatusCode()) {
                  //$response->cancel();
                throw new ApiConectionError('Error Status code '.$response->getStatusCode());
            }
            $response = $response->getContent(false);

            return $this->convertApiResponseToDomain($response);

        }  catch (TransportExceptionInterface $e) {
            throw new ApiConectionError('Transport Error '.$e->getMessage(),$e->getCode());
        } catch (ClientExceptionInterface $e) {
            throw new ApiConectionError('Client Error '.$e->getMessage(), $e->getCode());
        } catch (RedirectionExceptionInterface $e) {
            throw new ApiConectionError('Redirection Error '.$e->getMessage(), $e->getCode());
        } catch (ServerExceptionInterface $e) {
            throw new ApiConectionError('Server Error '.$e->getMessage(), $e->getCode());
        }

    }

    private function convertApiResponseToDomain(string $apiResponse):array
    {

        $apiResponse = json_decode($apiResponse,true);
        $stations = [];


        if (is_array($apiResponse))
        {

        $count = count($apiResponse);
        for ($i=0;$i<$count;$i++)
        {
          //  if (!empty($apiResponse[$i]) && !empty($apiResponse[$i]['stationId']))
            //{

                $uuidStation = utf8_encode($apiResponse[$i]['_id']);
                $uuidUser = utf8_encode($apiResponse[$i]['idApi']);
                $latitud = utf8_encode($apiResponse[$i]['latitude']);
                $longitud = utf8_encode($apiResponse[$i]['longitude']);
                $postalCode = null; //$apiResponse[$i]['postalCode'];
                $temp = utf8_encode($apiResponse[$i]['temperature']);
                $humidity = utf8_encode($apiResponse[$i]['humidity']);
                $presion = utf8_encode($apiResponse[$i]['pressure']);
                $location = utf8_encode($apiResponse[$i]['location']);
                $station = new Station
                (
                    $uuidStation,
                    $uuidUser,
                    $latitud,
                    $longitud,
                    $postalCode,
                    $temp,
                    $humidity,
                    $presion,
                    $location
                );
                //$station->setHistoric($this->findHistorycStation($response[$i]['uuidStation']));
                //$station->setPredictions($this->findPredictionsStation($response[$i]['postalCode']));

                $stations[] = $station;
         //   }

        }
       // var_dump($stations);
        return $stations;
        }

        throw new RemoteStationsNotFound('Stations not found ',500);

    }
}

