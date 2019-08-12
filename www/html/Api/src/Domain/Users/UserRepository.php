<?php

namespace App\Domain\Users;


interface UserRepository
{
	public function createUser(User $User):void;

	public function findUser(string $uuidUser): User;

	public function updateUser(User $uuidUser):void;

	public function deleteUser($uuidUser):void;
}