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
    
    public function put(string $path, array $data, ?string $token = null): array
    {
        return $this->request('PUT', $path, $data, $token);
    }

    public function delete(string $path, ?string $token = null): array
    {
        return $this->request('DELETE', $path, null, $token);
    }

    private function request(string $method, string $path, ?array $data, ?string $token): array
    {
        $ch = curl_init($this->baseUrl . $path);

        $headers = ['Content-Type: application/json; charset=utf-8', 'Accept: application/json; charset=utf-8'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_CONNECTTIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response   = curl_exec($ch);
        $curlError  = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError) {
            error_log("ApiClient cURL error [{$method} {$this->baseUrl}{$path}]: {$curlError}");
        }

        $decoded = json_decode(mb_convert_encoding($response ?: '', 'UTF-8', 'UTF-8'), true) ?? [];

        return [
            'status' => $statusCode,
            'data'   => $decoded,
            'ok'     => $statusCode >= 200 && $statusCode < 300,
        ];
    }
}
