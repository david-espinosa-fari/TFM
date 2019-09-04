<?php


namespace App\Tests\Aplication\User;


use App\Infraestructure\Users\UserRepositoryMysql;
use Exception;
use PHPUnit\Framework\TestCase;

class CreateUserTest extends TestCase
{
    /**
     * @test
     */
    public function createUserTest()
    {

        $this->expectException(Exception::class);
        $userRepository = new UserRepositoryMysql();

        $findUser = $userRepository->findUser('u22');
    }


}

