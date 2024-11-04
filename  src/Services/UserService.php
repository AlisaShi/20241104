<?php
namespace App\Services;

use App\Models\User;

class UserService {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function createUser($data) {
        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            throw new \Exception('Email already exists');
        }

        // Create user
        return $this->userModel->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user'
        ]);
    }

    public function attemptLogin($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return false;
        }

        if (!$this->userModel->verifyPassword($password, $user->password)) {
            return false;
        }

        unset($user->password);
        return $user;
    }
}