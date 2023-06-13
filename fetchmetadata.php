<?php
/**
 * metadata-validator fetchurl helper
 *
 * This acts as a simple proxy to get around the CORS restrictions in browsers.
 * It tries to validate the request as much as possible, to limit the
 * possibility of abuse.
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */

/** @var array $SUPPORTED_SCHEMES Array of URI schemes to allow */
$SUPPORTED_SCHEMES = array('http', 'https');

/** @var array $SUPPORTED_CONTENT_TYPES Array of content types to allow */
$SUPPORTED_CONTENT_TYPES = array ('application/samlmetadata+xml', 'application/xml', 'text/xml', 'text/plain');

/** @var int FETCH_MAX_SIZE Maximum size of a download in bytes */
define('FETCH_MAX_SIZE', 1024 * 1024);

/**
 * Generate a 502 Bad Gateway error and exit
 *
 * @param string $message The message to return
 */
function badGateway($message)
{
    header('HTTP/1.0 502 Bad Gateway');
    header('Status: 502 Bad Gateway');
    print trim($message) . "\n";
    if (! defined('PHPUNIT_COMPOSER_INSTALL') && ! defined('__PHPUNIT_PHAR__')) {
        exit(1); /* can't unit test this */
    } else {
        throw new RuntimeException("exit()");
    }
}

if (empty($_REQUEST['url']))
    badGateway('A URL must be given');

$url = parse_url($_REQUEST['url']);

if (!in_array(strtolower($url['scheme']), $SUPPORTED_SCHEMES))
    badGateway('The following schemes are supported: ' . implode(', ', $SUPPORTED_SCHEMES));

if (array_key_exists('user', $url) or array_key_exists('pass', $url))
    badGateway('Cannot fetch a password protected resource');

if (strtolower($url['host']) == 'localhost' or in_array('127.0.0.1', gethostbynamel($url['host'])))
    badGateway('Please don\'t fetch local resources :-(');

/* set up a cURL object to try fetch this for us */

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $_REQUEST['url']);
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Via: ' . $_SERVER['SERVER_NAME'],
    'Accept: ' . implode(', ', $SUPPORTED_CONTENT_TYPES),
    'Cache-Control: no-cache',
));
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
/* break the connection if FETCH_MAX_SIZE reached */
curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
curl_setopt($curl, CURLOPT_NOPROGRESS, false);
curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, function($ds, $d, $us, $u){
    return ($d > FETCH_MAX_SIZE) ? 1 : 0;
});

$data = curl_exec($curl);
if ($data === false)
    badGateway(curl_error($curl));

if (!in_array(curl_getinfo($curl, CURLINFO_CONTENT_TYPE), $SUPPORTED_CONTENT_TYPES))
    badGateway('Got unsupported content type. Only accept: ' . implode(', ', $SUPPORTED_CONTENT_TYPES));

http_response_code(curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
header('Status: ' . curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
header('Content-Type: text/plain'); /* edit wants plain text, not DOM */
header('Content-Length: ' . curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD));
header('X-Location: ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
print substr($data, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
curl_close($curl);
