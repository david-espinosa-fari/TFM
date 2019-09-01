<?php

namespace test\Domain\Users;


use App\Domain\Users\Error\UserErrorException;
use Symfony\Component\HttpFoundation\Request;

final class UserTest
{

    private $uuidUser;
    private $name;
    private $lastname;
    private $password;
    private $userName;
    private $age;
    private $gender;

    public function __construct(
        $uuidUser='nombreFacke',
        $name='nombreFacke',
        $lastname='nombreFacke',
        $password='nombreFacke',
        $userName='nombreFacke',
        $age='nombreFacke',
        $gender='nombreFacke'
    )
    {
        $this->uuidUser = $uuidUser;
        $this->name = $name;
        $this->lastname = $lastname;
        $this->setPassword($password);
        $this->userName = $userName;
        $this->age = $age;
        $this->gender = $gender;
    }

    public function __toString(): string
    {
        return $this->uuidUser;
    }

    public function getUserLikeArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param mixed $userName
     */
    public function setUserName($userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return mixed
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     */
    public function setAge($age): void
    {
        $this->age = $age;
    }

    /**
     * @return mixed
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender): void
    {
        $this->gender = $gender;
    }


}