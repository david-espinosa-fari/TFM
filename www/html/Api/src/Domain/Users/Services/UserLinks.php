<?php

namespace App\Domain\Users\Services;


final class UserLinks
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
        $this->urlUser = $_SERVER['PROTOCOL'].$_SERVER['HTTP_HOST'].'/apiv1/user/';
        $this->urlStations = $_SERVER['PROTOCOL'].$_SERVER['HTTP_HOST'].'/apiv1/stations/';
    }

    public function getLinksForPut($uuidUser):array
    {
        $link[] = $this->deleteUser($uuidUser);
        $link[] = $this->getUser($uuidUser);
        $link[] = $this->getUserStations($uuidUser);

        return $link;
    }
    public function getLinksForDelete($uuidUser):array
    {
        $link[] = $this->postUser();
        $link[] = $this->getUser($uuidUser);
        $link[] = $this->getUserStations($uuidUser);

        return $link;
    }
    public function getLinksForPost($uuidUser):array
    {
        $link[] = $this->deleteUser($uuidUser);
        $link[] = $this->putUser($uuidUser);
        $link[] = $this->getUser($uuidUser);
        $link[] = $this->getUserStations($uuidUser);

        return $link;
    }
    public function getLinksForGet($uuidUser):array
    {
        $link[] = $this->deleteUser($uuidUser);
        $link[] = $this->putUser($uuidUser);
        $link[] = $this->postUser();
        $link[] = $this->getUserStations($uuidUser);

        return $link;
    }

    public function getLinksForAll():array
    {
        $link[] = $this->deleteUser();
        $link[] = $this->putUser();
        $link[] = $this->getUser();
        $link[] = $this->postUser();
        $link[] = $this->getUserStations();

        return $link;
    }

    private function postUser($uuidUser = '{uuidUser}'): array
    {
        return
            [
                'href'=> $this->urlUser.$uuidUser,
                'action'=>'POST ',
                'types'=>['multipart/form-data', 'application/x-www-form-urlencoded']
            ];
    }
    private function putUser($uuidUser = '{uuidUser}'): array
    {
        return
            [
                'href'=> $this->urlUser.$uuidUser,
                'action'=>'PUT ',
                'types'=>['multipart/form-data', 'application/x-www-form-urlencoded']
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
    private function deleteUser($uuidUser = '{uuidUser}'): array
    {
        return
            [
                'href'=> $this->urlUser.$uuidUser,
                'action'=>'DELETE ',
                'types'=>[]
            ];
    }
    private function getUserStations($uuidUser = '{uuidUser}'): array
    {
        return
            [
                'href'=> $this->urlUser.$uuidUser.'/stations',
                'action'=>'GET ',
                'types'=>[]
            ];
    }

}