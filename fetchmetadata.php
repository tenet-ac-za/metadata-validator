<?php
/**
 * metadata-validator fetchurl helper
 *
 * This acts as a simple proxy to get around the CORS restrictions in browsers.
 * It tries to validate the request as much as possible, to limit the
 * possibility of abuse.
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, SAFIRE - South African Identity Federation
 * @license https://github.com/safire-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */
 
/** @var array $SUPPORTED_SCHEMES Array of URI schemes to allow */
$SUPPORTED_SCHEMES = array('http', 'https');
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
    exit(1);    
}
 
if (empty($_REQUEST['url']))
    badGateway('A URL must be given');

$url = parse_url($_REQUEST['url']);

if (!in_array(strtolower($url['scheme']), $SUPPORTED_SCHEMES))
    badGateway('The following schemes are supported: ' . implode(', ', $SUPPORTED_SCHEMES));

if ($url['user'] or $url['pass'])
    badGateway('Cannot fetch a password protected resource');

if (strtolower($url['hostname']) == 'localhost' or in_array('127.0.0.1', gethostbynamel($url['hostname'])))
    badGateway('Please don\'t fetch local resources :-(');

/* set up a cURL object to try fetch this for us */

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $_REQUEST['url']);
curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Via: ' . $_SERVER['SERVER_NAME'],
    'Accept: text/xml',
    'Cache-Control: no-cache',
));
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
/* break the connection if FETCH_MAX_SIZE reached */
curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
curl_setopt($curl, CURLOPT_NOPROGRESS, false);
curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, function($ds, $d, $us, $u){
    return ($d > FETCH_MAX_SIZE) ? 1 : 0;
});

$data = curl_exec($curl);
if ($data === false)
    badGateway(curl_error($curl));

header('Status: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE));
header('Content-Type: text/plain');
header('Content-Length: ' . strlen($data));
print $data;
