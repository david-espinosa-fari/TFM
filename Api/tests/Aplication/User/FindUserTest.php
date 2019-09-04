<?php


namespace Test\Aplication\User;


use App\Infraestructure\Users\UserRepositoryMysql;
use Exception;
use PHPUnit\Framework\TestCase;

class FindUserTest  extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowExceptionFindUser() //UserErrorException('User could not being updated', 400)
    {
        $this->expectException(Exception::class);
        $userRepository = new UserRepositoryMysql();

        $findUser = $userRepository->findUser('u22');

    }
}