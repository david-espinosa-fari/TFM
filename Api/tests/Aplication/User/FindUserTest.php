<?php


namespace Test\Aplication\User;


use App\Aplication\User\FindUser;
use App\Domain\Users\User;
use PHPUnit\Framework\TestCase;
use tests\Infraestructure\CacheDataInMemoryRepository;
use tests\Infraestructure\InMemoryUserRepositoryTest;

class FindUserTest  extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnAUserObject():User //UserErrorException('User could not being updated', 400)
    {

        $userRepository = new InMemoryUserRepositoryTest();
        $cacheData = new CacheDataInMemoryRepository($_SERVER['HOST_REDIS']);

        $findUser = new FindUser($userRepository, $cacheData);
        $user = $findUser('deleted');
        $this->assertInstanceOf(User::class);
    }
}