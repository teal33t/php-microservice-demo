<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AuthService
{
    private $client;
    private $userServiceUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->userServiceUrl = getenv('USER_SERVICE_URL') ?: 'http://user-service';
    }

    public function isAdmin(): bool
    {
        $token = $this->getBearerToken();
        if (!$token) {
            return false;
        }

        try {
            $response = $this->client->get($this->userServiceUrl . '/profile', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return isset($data['role']) && $data['role'] === 'admin';
        } catch (GuzzleException $e) {
            return false;
        }
    }

    private function getBearerToken(): ?string
    {
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }

        return null;
    }
}