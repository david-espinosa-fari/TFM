<?php


namespace App\Domain;


interface StationRemoteRepository
{
    public function findPredictionsByLocationCode($locationCode);

    public function findAllStation():array;
}