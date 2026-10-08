<?php
require_once __DIR__ . '/../config/fonnte.php';

/**
 * Mengirim pesan WhatsApp melalui Fonnte.
 * Return:
 * [
 *   'success' => bool,
 *   'response' => array|string,
 *   'error' => string|null
 * ]
 */
function kirimWhatsApp($target, $message) {
    $target = trim($target);

    if ($target === '' || trim(FONNTE_TOKEN) === '' || FONNTE_TOKEN === 'ISI_TOKEN_FONNTE_DI_SINI') {
        return [
            'success' => false,
            'response' => null,
            'error' => 'Nomor target atau token Fonnte belum dikonfigurasi.'
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'response' => null,
            'error' => 'Ekstensi PHP cURL belum aktif.'
        ];
    }

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => FONNTE_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => [
            'target' => $target,
            'message' => $message,
            'countryCode' => FONNTE_DEFAULT_COUNTRY_CODE
        ],
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . FONNTE_TOKEN
        ],
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($error) {
        return ['success' => false, 'response' => null, 'error' => $error];
    }

    $decoded = json_decode($response, true);
    $success = $httpCode >= 200 && $httpCode < 300 && is_array($decoded) && (($decoded['status'] ?? false) === true);

    return [
        'success' => $success,
        'response' => $decoded ?? $response,
        'error' => $success ? null : 'Fonnte mengembalikan response yang tidak berhasil.'
    ];
}
