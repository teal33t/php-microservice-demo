<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\AuthService;

class UserController
{
    private $userRepository;
    private $authService;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->authService = new AuthService();
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['username']) || !isset($data['password'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Username and password are required']);
            return;
        }

        $user = $this->userRepository->verifyCredentials($data['username'], $data['password']);
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        $token = $this->authService->generateToken($user);
        echo json_encode(['token' => $token]);
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['username']) || !isset($data['password']) || !isset($data['email'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Username, password and email are required']);
            return;
        }

        try {
            $userId = $this->userRepository->create($data);
            $user = $this->userRepository->findById($userId);
            echo json_encode(['user' => $user]);
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to create user']);
        }
    }

    public function getProfile()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userDetails = $this->userRepository->findById($user['userId']);
        if (!$userDetails) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'User not found']);
            return;
        }

        echo json_encode(['user' => $userDetails]);
    }

    public function updateProfile()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'No data provided']);
            return;
        }

        try {
            $success = $this->userRepository->update($user['userId'], $data);
            if ($success) {
                $updatedUser = $this->userRepository->findById($user['userId']);
                echo json_encode(['user' => $updatedUser]);
            } else {
                header('HTTP/1.0 500 Internal Server Error');
                echo json_encode(['error' => 'Failed to update user']);
            }
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to update user']);
        }
    }
}