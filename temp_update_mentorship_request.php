<?php

function request(string $url, array $data, array $headers = []): string
{
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => json_encode($data),
            'ignore_errors' => true,
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        throw new RuntimeException("HTTP request failed to $url");
    }

    return $response;
}

try {
    $loginUrl = 'http://127.0.0.1:8000/api/v1/auth/login';
    $loginData = ['email' => 'mentor2@example.com', 'password' => 'password123'];
    $loginResponse = request($loginUrl, $loginData, ['Content-Type: application/json']);
    echo "LOGIN RESPONSE:\n" . $loginResponse . "\n\n";

    $loginJson = json_decode($loginResponse, true);
    if (!isset($loginJson['token'])) {
        throw new RuntimeException('Login did not return a token.');
    }

    $token = $loginJson['token'];
    $updateUrl = 'http://127.0.0.1:8000/api/v1/mentorship-requests/3';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ];

    $options = [
        'http' => [
            'method' => 'PATCH',
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => json_encode(new stdClass()),
            'ignore_errors' => true,
        ],
    ];
    $context = stream_context_create($options);
    $updateResponse = file_get_contents($updateUrl, false, $context);
    if ($updateResponse === false) {
        throw new RuntimeException("HTTP request failed to $updateUrl");
    }
    echo "UPDATE RESPONSE:\n" . $updateResponse . "\n\n";

    $showUrl = 'http://127.0.0.1:8000/api/v1/mentorship-requests/3';
    $options['http']['method'] = 'GET';
    $options['http']['content'] = null;
    $context = stream_context_create($options);
    $showResponse = file_get_contents($showUrl, false, $context);
    if ($showResponse === false) {
        throw new RuntimeException("HTTP request failed to $showUrl");
    }
    echo "SHOW RESPONSE:\n" . $showResponse . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
