<?php

namespace App\Infraestructure\Users;

use App\Domain\Users\Error\UserErrorException;
use App\Domain\Users\User;
use App\Domain\Users\UserRepository;
use Exception;
use PDO;
use PDOException;

final class UserRepositoryMysql implements UserRepository
{
    private $conect;

    /**
     * StationRepository constructor.
     * @throws UserErrorException
     */

    public function __construct()
    {
        try {
            $this->conect = new PDO(
                "mysql:host={$_SERVER['HOST_MYSQL']};dbname={$_SERVER['DB_MYSQL']}",
                $_SERVER['USER_MYSQL'],
                $_SERVER['PASS_MYSQL']
            );

        } catch (PDOException $exception) {
            $stationErrorResponse = new UserErrorException('Internal Server error', 500);
            $stationErrorResponse->setMoreInfo($exception);
            throw $stationErrorResponse;
        }
    }

    public function createUser(User $user): void
    {
        try {
            $this->findUser((string)$user);
            throw new Exception('User already exists try tu update, use method PUT instead', 400);
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
        $select = 'select uuidUser,name,lastname,password,userName,age,gender';
        $from = ' from `user` where `user`.`uuidUser` = ? and deletedUser= ' . '0' . '';
        $query = $select . $from;

        $stmt = $this->conect->prepare($query);
        $stmt->bindParam(1, $uuidUser);
        $stmt->execute();

        $response = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($response)) {
            $user = new User
            (
                $response['uuidUser'],
                $response['name'],
                $response['lastname'],
                $response['password'],
                $response['userName'],
                $response['age'],
                $response['gender']
            );

            return $user;
        }
        throw new UserErrorException('User ' . $uuidUser . ' not Found', 404);
    }

    public function updateUser(User $user): void
    {
        $uuidUser = (string)$user;
        $name = $user->getName();
        $lastname = $user->getLastname();
        $password = $user->getPassword();
        $userName = $user->getUserName();
        $age = $user->getAge();
        $gender = $user->getGender();

        $update = "UPDATE `user` SET";
        $values = " name = :name, lastname = :lastname, password = :password, userName = :userName, age = :age, gender = :gender";
        $where = " WHERE uuidUser = :uuidUser";

        $query = $update . $values . $where;
        $statment = $this->conect->prepare($query);

        $statment->bindValue(':name', $name);
        $statment->bindValue(':lastname', $lastname);
        $statment->bindValue(':password', $password);
        $statment->bindValue(':userName', $userName);
        $statment->bindValue(':age', $age);
        $statment->bindValue('gender', $gender);
        $statment->bindValue(':uuidUser', $uuidUser);
        if (!$statment->execute()) {
            throw new UserErrorException('User could not being updated', 400);
        }
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