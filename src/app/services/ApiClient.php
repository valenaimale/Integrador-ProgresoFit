<?php

namespace PAW\app\services;

class ApiClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = getenv('API_BASE_URL') ?: 'http://localhost:3000';
    }

    public function get(string $path, ?string $token = null): array
    {
        return $this->request('GET', $path, null, $token);
    }

    public function post(string $path, array $data, ?string $token = null): array
    {
        return $this->request('POST', $path, $data, $token);
    }

    private function request(string $method, string $path, ?array $data, ?string $token): array
    {
        $ch = curl_init($this->baseUrl . $path);

        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 5,
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $decoded = json_decode($response, true) ?? [];

        return [
            'status' => $statusCode,
            'data'   => $decoded,
            'ok'     => $statusCode >= 200 && $statusCode < 300,
        ];
    }
}
