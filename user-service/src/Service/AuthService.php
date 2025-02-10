<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Repository\UserRepository;
use Exception;

class AuthService
{
    private $key;
    private $userRepository;

    public function __construct()
    {
        $this->key = getenv('JWT_SECRET', 'your-secret-key');
        $this->userRepository = new UserRepository();
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        $token = $this->generateToken($user['id']);
        
        return [
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ],
            'token' => $token
        ];
    }

    public function generateToken(int $userId): string
    {
        $payload = [
            'iss' => 'user-service',
            'iat' => time(),
            'exp' => time() + (60 * 60), // Token valid for 1 hour
            'userId' => $userId
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function verifyToken(string $token): ?int
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            return $decoded->userId;
        } catch (Exception $e) {
            return null;
        }
    }

    public function middleware(): ?int
    {
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $jwt = str_replace('Bearer ', '', $headers['Authorization']);
        return $this->verifyToken($jwt);
    }
}