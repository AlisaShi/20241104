// src/Controllers/AuthController.php
<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Utils\Response;
use App\Utils\Validator;
use App\Utils\Session;

class AuthController {
    private $userService;

    public function __construct() {
        $this->userService = new UserService();
    }

    public function register() {
        $validator = new Validator($_POST);
        $validator
            ->required('name')
            ->required('email')->email('email')
            ->required('password')->min('password', 6)
            ->required('password_confirmation');

        if ($validator->fails()) {
            return Response::error($validator->getErrors(), 422);
        }

        if ($_POST['password'] !== $_POST['password_confirmation']) {
            return Response::error(['password' => 'Passwords do not match'], 422);
        }

        try {
            $user = $this->userService->createUser($_POST);
            Session::set('user', $user);
            return Response::success($user, 'Registration successful');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function login() {
        $validator = new Validator($_POST);
        $validator
            ->required('email')->email('email')
            ->required('password');

        if ($validator->fails()) {
            return Response::error($validator->getErrors(), 422);
        }

        try {
            $user = $this->userService->attemptLogin($_POST['email'], $_POST['password']);
            if (!$user) {
                return Response::error('Invalid credentials', 401);
            }
            Session::set('user', $user);
            return Response::success($user, 'Login successful');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    public function logout() {
        Session::destroy();
        return Response::success(null, 'Logged out successfully');
    }
}
