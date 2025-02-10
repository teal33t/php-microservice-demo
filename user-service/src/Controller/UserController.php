<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Repository\UserRepository;

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
        
        if (!isset($data['email']) || !isset($data['password'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $result = $this->authService->login($data['email'], $data['password']);
        
        if (!$result) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        echo json_encode($result);
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Email, password and name are required']);
            return;
        }

        if ($this->userRepository->findByEmail($data['email'])) {
            header('HTTP/1.0 409 Conflict');
            echo json_encode(['error' => 'Email already exists']);
            return;
        }

        $userId = $this->userRepository->create($data);
        $token = $this->authService->generateToken($userId);

        echo json_encode([
            'user' => [
                'id' => $userId,
                'email' => $data['email'],
                'name' => $data['name']
            ],
            'token' => $token
        ]);
    }

    public function getProfile()
    {
        $userId = $this->authService->middleware();
        
        if (!$userId) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'User not found']);
            return;
        }

        unset($user['password']);
        echo json_encode($user);
    }

    public function updateProfile()
    {
        $userId = $this->authService->middleware();
        
        if (!$userId) {
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

        if (!$this->userRepository->update($userId, $data)) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to update profile']);
            return;
        }

        $user = $this->userRepository->findById($userId);
        unset($user['password']);
        echo json_encode($user);
    }
}