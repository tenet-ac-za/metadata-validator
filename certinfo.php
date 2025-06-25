<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/local/xsltfunc.inc.php')) {
    include_once(__DIR__ . '/local/xsltfunc.inc.php');
}

function sendResponse($response, $status = 200)
{
    if (array_key_exists('callback', $_REQUEST)) {
        header('content-type: text/javascript; charset=utf-8');
        echo addslashes($_REQUEST['callback']) . '(';
    } else {
        header('content-type: application/json; charset=utf-8');
    }
    http_response_code($status);
    header("access-control-allow-origin: *");

    if ($status != 200) {
        $response = [ 'error' => $response, 'code' => $status, ];
    }
    print json_encode($response);

    if (array_key_exists('callback', $_REQUEST)) {
        print ');';
    }
    if (! defined('PHPUNIT_COMPOSER_INSTALL') && ! defined('__PHPUNIT_PHAR__')) {
        exit; /* can't unit test this */
    } else {
        throw new RuntimeException("exit()");
    }
}

function getOpenSSLInfo($pem, $arg = '-text')
{
    /* get OpenSSL response */
    $openssl = proc_open('openssl x509 ' . escapeshellarg($arg), [ 0 => ['pipe', 'r'], 1 => ['pipe', 'w'] ], $pipes);
    if (is_resource($openssl)) {
        fwrite($pipes[0], $pem);
        fclose($pipes[0]);
        $openssl_out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($openssl);
    }
    if (isset($openssl_out)) {
        return $openssl_out;
    } else {
        return false;
    }
}

if (substr(PHP_SAPI, 0, 3) === 'cli') {
    if ($argc > 1) {
        $cert = file_get_contents($argv[1]);
    } else {
        $cert = file_get_contents('php://stdin');
    }
} else {
    $cert = file_get_contents('php://input');
}

if (empty($cert)) {
    sendResponse('Invalid or nonexistent certificate', 400);
}

/* normalise the certificate */
$pem = trim($cert);
if (!preg_match('/^-----BEGIN CERTIFICATE/', $pem)) {
    $pem = "-----BEGIN CERTIFICATE-----\n" .
        wordwrap(preg_replace('/\s+/', '', $pem), 64, "\n", true) .
        "\n-----END CERTIFICATE-----\n";
}

$response = [
    'pem' => $pem,
    'openssl' => getOpenSSLInfo($pem),
    'validity' => [
        'from' => XsltFunc::getCertDates($cert, 'from'),
        'to' => XsltFunc::getCertDates($cert, 'from'),
        'range' => XsltFunc::getCertDates($cert, 'both'),
        'valid' => XsltFunc::checkCertValid($cert),
    ],
    'bits' => XsltFunc::getCertBits($cert),
    'issuer' => XsltFunc::getCertIssuer($cert),
    'subject' => XsltFunc::getCertSubject($cert),
    'selfsigned' => XsltFunc::checkCertSelfSigned($cert),
    'ca' => XsltFunc::checkCertIsCA($cert),
    'fingerprint' => preg_replace('/^.*Fingerprint=([0-9A-F:]+).*$/si', '$1', getOpenSSLInfo($pem, '-fingerprint')),
];

sendResponse($response);
