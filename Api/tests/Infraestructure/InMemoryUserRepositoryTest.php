<?php

namespace tests\Infraestructure;


use App\Domain\Users\Error\UserErrorException;
use App\Domain\Users\User;
use App\Domain\Users\UserRepository;

final class InMemoryUserRepositoryTest implements UserRepository
{
    private $conect;

    /**
     * StationRepository constructor.
     * @throws UserErrorException
     */

    public function __construct()
    {

    }

    public function createUser(User $user): void
    {
        try {
            $this->findUser((string)$user);
            throw new Exception('User already exists try update, use method PUT instead', 400);
        } catch (UserErrorException $exception) {

            $uuidUser = (string)$user;
            $name = $user->getName();
            $lastname = $user->getLastname();
            $password = $user->getPassword();
            $userName = $user->getUserName();
            $age = $user->getAge();
            $gender = $user->getGender();

            $fields = 'INSERT INTO `user`(uuidUser,name,lastname,password,userName,age,gender)';
            $values = ' VALUES (?,?,?,?,?,?,?)';
            $query = $fields . $values;

            $statment = $this->conect->prepare($query);

            $statment->bindParam(1, $uuidUser);
            $statment->bindParam(2, $name);
            $statment->bindParam(3, $lastname);
            $statment->bindParam(4, $password);
            $statment->bindParam(5, $userName);
            $statment->bindParam(6, $age);
            $statment->bindParam(7, $gender);
            if (!$statment->execute()) {
                throw new UserErrorException('Could not insert value, CHANGE your uuidUser', 400);
            }

        }

    }

    public function findUser(string $uuidUser): User
    {
        return new User
            (
                'uuidUser',
                'name',
                'lastname',
                'password',
                'userName',
                'age',
                'gender'
            );
    }

    public function updateUser(User $user):void
    {
        if ((string)$user==='a')
        {
            return;
        }
        throw new UserErrorException('User could not being updated', 400);
    }

    public function deleteUser($uuidUser): void
    {
        $update = "UPDATE `user` SET ";
        $values = "deletedUser = '1'";
        $where = " WHERE uuidUser = :uuidUser";

        $query = $update . $values . $where;
        $statment = $this->conect->prepare($query);

        $statment->bindValue(':uuidUser', $uuidUser);
        if (!$statment->execute()) {
            throw new UserErrorException('Could not delete user, check your request ', 400);
        }
    }
}