<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 01/04/2019
 * Time: 12:59
 */

namespace Test\Aplication\User;


use App\Aplication\User\UpdateUser;
use App\Domain\Users\User;
use App\Infraestructure\CacheDataRepositoryRedis;
use Exception;
use PHPUnit\Framework\TestCase;
use tests\Infraestructure\CacheDataInMemoryRepository;
use tests\Infraestructure\InMemoryUserRepositoryTest;

class UpdateUserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnExceptionOnUpdate() //UserErrorException('User could not being updated', 400)
    : void
    {
        $this->expectException(Exception::class);

        $userRepo = new InMemoryUserRepositoryTest();
        $message = new CacheDataRepositoryRedis('1');

        $updateUser = new UpdateUser($userRepo,$message);
        $updateUser(new User('a','a','a','a','a','a','a'));
    }

}