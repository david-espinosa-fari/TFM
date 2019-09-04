<?php

namespace App\Domain\Users;


use App\Domain\Users\Error\UserErrorException;
use Symfony\Component\HttpFoundation\Request;

final class User
{

    private $uuidUser;
    private $name;
    private $lastname;
    private $password;
    private $userName;
    private $age;
    private $gender;

    public function __construct(
        $uuidUser, $name, $lastname, $password, $userName, $age, $gender
    )
    {
        $this->uuidUser = $uuidUser;
        $this->name = $name;
        $this->lastname = $lastname;
        $this->password=$password;
        $this->userName = $userName;
        $this->age = $age;
        $this->gender = $gender;
    }

    public static function buildUser(Request $request): User
    {
        $data = [];
        $data['uuidUser'] = $request->get('uuidUser');
        $data['name'] = $request->get('name');
        $data['lastname'] = $request->get('lastname');
        $data['password'] = self::criptPassword($request->get('password'));
        $data['userName'] = $request->get('userName');
        $data['age'] = $request->get('age');
        $data['gender'] = $request->get('gender');

        foreach ($data as $datafield => $value) {
            if (empty($value)) {
                throw new UserErrorException('Missing argument ' . $datafield . ' in request ', 400);
            }
        }

        return new self(
            $data['uuidUser'],
            $data['name'],
            $data['lastname'],
            $data['password'],
            $data['userName'],
            $data['age'],
            $data['gender']
        );
    }

    /**
     * @param mixed $password
     * @return string
     */
    public static function criptPassword($password): string
    {
        return base64_encode(password_hash($password, PASSWORD_BCRYPT, array('cost'=>12)));
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
        $this->password = self::criptPassword($password);
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