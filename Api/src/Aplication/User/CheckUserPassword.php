<?php


namespace App\Aplication\User;


use App\Domain\Users\Error\UserErrorException;
use App\Domain\Users\User;

final class CheckUserPassword
{
    public function __construct(User $user, $password)
    {
        if(!password_verify($password,base64_decode($user->getPassword())))
        {
            throw new UserErrorException('Autentication denied',403);
        }
    }

}