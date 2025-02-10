<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthService
{
    private $key;
    private $algorithm = 'HS256';

    public function __construct()
    {
        $this->key = getenv('JWT_SECRET_KEY') ?: 'your-secret-key-change-this-in-production';
    }

    public function generateToken(array $user): string
    {
        $payload = [
            'iss' => 'user-service',
            'iat' => time(),
            'exp' => time() + (60 * 60), // Token valid for 1 hour
            'userId' => $user['id'],
            'username' => $user['username']
        ];

        return JWT::encode($payload, $this->key, $this->algorithm);
    }

    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->key, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAuthenticatedUser(array $headers): ?array
    {
        $token = $this->extractTokenFromHeaders($headers);
        if (!$token) {
            return null;
        }

        return $this->verifyToken($token);
    }

    private function extractTokenFromHeaders(array $headers): ?string
    {
        $authHeader = $headers['Authorization'] ?? null;
        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}