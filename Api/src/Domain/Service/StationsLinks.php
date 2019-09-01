<?php


namespace App\Domain\Service;


final class StationsLinks
{
    private $urlStations;
    /**
     * @var string
     */
    private $urlUser;
    /**
     * @var string
     */
    private $urlPredictions;

    public function __construct()
    {
        $this->urlStations = $_SERVER['PROTOCOL'].$_SERVER['HTTP_HOST'].'/apiv1/stations/';
        $this->urlUser = $_SERVER['PROTOCOL'].$_SERVER['HTTP_HOST'].'/apiv1/user/';
        $this->urlPredictions = $_SERVER['PROTOCOL'].$_SERVER['HTTP_HOST'].'/apiv1/predictions/';
    }

    public function getLinksForGET($uuidStation):array
    {
        $link[] = $this->deleteStations($uuidStation);
        $link[] = $this->putStations($uuidStation);
        $link[] = $this->postStationsHistory($uuidStation);
        $link[] = $this->getPredictionsPostalCode();
        $link[] = $this->getUser();

        return $link;
    }
    public function getLinksForPOST($uuidStation):array
    {
        $link[] = $this->deleteStations($uuidStation);
        $link[] = $this->putStations($uuidStation);
        $link[] = $this->getStations($uuidStation);
        $link[] = $this->postStationsHistory($uuidStation);
        $link[] = $this->getUser();

        return $link;
    }
    public  function getLinksForPUT($uuidStation):array
    {
        $link[] = $this->deleteStations($uuidStation);
        $link[] = $this->getStations($uuidStation);
        $link[] = $this->postStationsHistory($uuidStation);

        return $link;
    }
    public function getLinksForDELETE($uuidStation):array
    {
        $link[] = $this->getAllStations();
        $link[] = $this->getStations($uuidStation);
        $link[] = $this->getUser();

        return $link;
    }
    public function getLinksForAll():array
    {
        $link[] = $this->getAllStations();
        $link[] = $this->deleteStations();
        $link[] = $this->putStations();
        $link[] = $this->getStations();
        $link[] = $this->postStations();
        $link[] = $this->postStationsHistory();
        $link[] = $this->getStationsPostalCode();
        $link[] = $this->getPredictionsPostalCode();
        $link[] = $this->getUser();

        return $link;
    }

    public function getLinksForPostalCode($postalCode): array
    {
        $link[] = $this->getAllStations();
        $link[] = $this->postStationsHistory();
        $link[] = $this->getStationsPostalCode($postalCode);
        $link[] = $this->getPredictionsPostalCode($postalCode);
        $link[] = $this->getUser();

        return $link;
    }
    private function getAllStations(): array
    {
        return
            [
                'href'=> $this->urlStations,
                'action'=>'GET ',
                'types'=>[]
            ];
    }
    private function deleteStations($uuidStation = '{uuidStation}'): array
    {
        return
            [
                'href'=> $this->urlStations.$uuidStation,
                'action'=>'DELETE ',
                'types'=>[]
            ];
    }
    private function putStations($uuidStation = '{uuidStation}'): array
    {
        return
            [
                'href'=> $this->urlStations.$uuidStation,
                'action'=>'PUT ',
                'types'=>['multipart/form-data', 'application/x-www-form-urlencoded']
            ];
    }
    private function getStations($uuidStation = '{uuidStation}'): array
    {
        return
            [
                'href'=> $this->urlStations.$uuidStation,
                'action'=>'GET ',
                'types'=>[]
            ];
    }
    private function postStations($uuidStation = '{uuidStation}'): array
    {
        return
            [
                'href'=> $this->urlStations.$uuidStation,
                'action'=>'POST ',
                'types'=>['multipart/form-data', 'application/x-www-form-urlencoded']
            ];
    }
    private function postStationsHistory($uuidStation = '{uuidStation}'): array
    {
        return
            [
                'href'=> $this->urlStations.$uuidStation.'/history',
                'action'=>'POST ',
                'types'=>['multipart/form-data', 'application/x-www-form-urlencoded']
            ];
    }
    private function getStationsPostalCode($postalCode = '{postalCode}'): array
    {
        return
            [
                'href'=> $this->urlStations.'postalcode/'.$postalCode,
                'action'=>'GET ',
                'types'=>[]
            ];
    }
    private function getPredictionsPostalCode($postalCode = '{postalCode}'): array
    {
        return
            [
                'href'=> $this->urlPredictions.$postalCode,
                'action'=>'GET ',
                'types'=>[]
            ];
    }
    private function getUser($uuidUser = '{uuidUser}'): array
    {
        return
            [
                'href'=> $this->urlUser.$uuidUser,
                'action'=>'GET ',
                'types'=>[]
            ];
    }
}