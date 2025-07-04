<?php

/**
 * Functions for use in XSLT
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

if (file_exists(dirname(__DIR__) . '/local/config.inc.php')) {
    include_once(dirname(__DIR__) . '/local/config.inc.php');
}

class XsltFunc
{
    /**
     * Take a PEM representation of a certificate and return the x509 structure
     *
     * @param string $x509certdata
     * @return ?OpenSSLCertificate $x509cert
     */
    private static function pemToX509($x509certdata): ?OpenSSLCertificate
    {
        if (!function_exists('openssl_x509_read')) {
            error_log('pemToX509 needs OpenSSL functions');
            return null;
        }
        $pem = trim($x509certdata);
        if (!preg_match('/^-----BEGIN CERTIFICATE/', $pem)) {
            $pem = "-----BEGIN CERTIFICATE-----\n" . wordwrap($pem, 64, "\n", true) . "\n-----END CERTIFICATE-----\n";
        }
        $x509cert = @openssl_x509_read($pem);
        if ($x509cert === false) {
            return null;
        }
        return $x509cert;
    }

    /**
     * For use in XSLT, checks a cert is self-signed
     *
     * @param string $cert
     * @return bool
     */
    public static function checkCertSelfSigned($cert)
    {
        $x509data = @openssl_x509_parse(self::pemToX509($cert) ?? '');
        if (empty($x509data)) {
            return false;
        }
        if (
            !array_key_exists('subject', $x509data) ||
            !is_array($x509data['subject']) ||
            !array_key_exists('issuer', $x509data) ||
            !is_array($x509data['issuer'])
        ) {
            return false;
        }
        if (array_diff_assoc($x509data['subject'], $x509data['issuer'])) {
            return false;
        }
        return true;
    }

    /**
     * Check whether basicConstraints has CA:TRUE
     */
    public static function checkCertIsCA($cert)
    {
        $x509data = @openssl_x509_parse(self::pemToX509($cert) ?? '');
        if (empty($x509data)) {
            return false;
        }
        error_log(var_export($x509data['extensions'], true));
        if (
            !array_key_exists('extensions', $x509data) ||
            !array_key_exists('basicConstraints', $x509data['extensions'])
        ) {
            return false;
        }
        if (preg_match('/CA:TRUE/i', $x509data['extensions']['basicConstraints'])) {
            return true;
        }
        return false;
    }

    /**
     * Return the certificate issuer name
     *
     * @param string $cert
     * @return ?string $issuer
     */
    public static function getCertIssuer($cert)
    {
        $x509data = @openssl_x509_parse(self::pemToX509($cert) ?? '');
        if (empty($x509data)) {
            return null;
        }
        $issuer = array_key_exists('issuer', $x509data) && is_array($x509data['issuer'])
            ? (array_key_exists('CN', $x509data['issuer'])
                ? $x509data['issuer']['CN']
                : join('/', $x509data['issuer'])) : null;
        return $issuer;
    }

    /**
     * Return the certificate subject name
     *
     * @param string $cert
     * @return ?string $subject
     */
    public static function getCertSubject($cert)
    {
        $x509data = @openssl_x509_parse(self::pemToX509($cert) ?? '');
        if (empty($x509data)) {
            return null;
        }
        $subject = array_key_exists('subject', $x509data) && is_array($x509data['subject'])
            ? (array_key_exists('CN', $x509data['subject'])
                ? $x509data['subject']['CN']
                : join('/', $x509data['subject'])) : null;
        return $subject;
    }

    /**
     * Check the certificate expiry
     *
     * @param string $cert
     * @param string $fromto
     * @return bool
     */
    public static function checkCertValid($cert, $fromto = 'both')
    {
        $x509data = @openssl_x509_parse(self::pemToX509($cert) ?? '');
        if (empty($x509data)) {
            return false;
        }
        if ($fromto != 'to' && $x509data['validFrom_time_t'] >= time()) {
            return false;
        }
        if ($fromto != 'from' && $x509data['validTo_time_t'] < (time() + 366 * 86400)) {
            return false;
        }
        return true;
    }

    /**
     * Return the certificate dates
     *
     * @param string $cert
     * @param string $fromto
     * @param string $format per date()
     * @return ?string $date
     */
    public static function getCertDates($cert, $fromto = 'both', $format = 'Y-m-d')
    {
        $x509data = @openssl_x509_parse(self::pemToX509($cert) ?? '');
        if (empty($x509data)) {
            return null;
        }
        switch ($fromto) {
            case 'from':
                return date($format, $x509data['validFrom_time_t']);
            case 'to':
                return date($format, $x509data['validTo_time_t']);
            case 'both':
                return date($format, $x509data['validFrom_time_t'])
                    . ' - ' . date($format, $x509data['validTo_time_t']);
            default:
                return null;
        }
    }

    /**
     * Return the number of bits used in the certificate
     *
     * @param string $cert
     * @return ?integer $bits
     */
    public static function getCertBits($cert)
    {
        $x509key = @openssl_get_publickey(self::pemToX509($cert) ?? '');
        if (empty($x509key)) {
            return null;
        }
        $x509keydetails = @openssl_pkey_get_details($x509key);
        //error_log(var_export($x509keydetails, true));
        if ($x509keydetails['type'] == OPENSSL_KEYTYPE_EC) {
            return $x509keydetails['ec']['curve_name'];
        }
        if (!array_key_exists('bits', $x509keydetails)) {
            return null;
        }
        return (int) $x509keydetails['bits'];
    }

    /**
     * Check a URL
     *
     * This is a bit simplistic - it merely checks that the web server
     * can be reached and answers with something halfway valid. Will
     * accept any cert for https - use checkURLCert to verify it.
     *
     * @param string $url
     * @return bool
     */
    public static function checkURL($url)
    {
        if (!function_exists('curl_init')) {
            error_log('checkURL needs cURL functions');
            return false;
        }
        if (parse_url($url, PHP_URL_SCHEME) == 'data') {
            return true; // data: URLs are always valid
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Cache-Control: no-cache']);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        return curl_exec($curl) === false ? false : true;
    }

    /**
     * Check the certificate used by a remote web server
     *
     * This verifies the SSL cert against cURL's internal
     * list of certificate authorities
     *
     * @param string $url
     * @param bool $modern do modern browser compat checks
     * @return bool|string
     */
    public static function checkURLCert($url, $modern = true, $verbose = false)
    {
        if (!function_exists('curl_init')) {
            error_log('checkURLCert needs cURL functions');
            return false;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Cache-Control: no-cache']);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        if ($modern == true) {
            curl_setopt($curl, CURLOPT_CERTINFO, true);
            /* Try use a modernish version of TLS */
            if (defined('CURL_SSLVERSION_TLSv1_2')) {
                curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            }
            /* Chrome 54's cipher list - try eliminate older, insecure servers */
            curl_setopt(
                $curl,
                CURLOPT_SSL_CIPHER_LIST,
                implode(':', [
                    'ECDHE-ECDSA-AES128-GCM-SHA256', 'ECDHE-RSA-AES128-GCM-SHA256', 'ECDHE-ECDSA-AES256-GCM-SHA384',
                    'ECDHE-RSA-AES256-GCM-SHA384', 'ECDHE-ECDSA-CHACHA20-POLY1305-SHA256',
                    'ECDHE-RSA-CHACHA20-POLY1305-SHA256', 'ECDHE-ECDSA-AES128-SHA', 'ECDHE-RSA-AES128-SHA',
                    'ECDHE-ECDSA-AES256-SHA', 'ECDHE-RSA-AES256-SHA', 'RSA-AES128-GCM-SHA256', 'RSA-AES256-GCM-SHA384',
                    'RSA-AES128-SHA', 'RSA-AES256-SHA', 'RSA-3DES-EDE-SHA',
                ],),
            );
            /* OSCP stapling check - remote host must support it!
            if (defined('CURLOPT_SSL_VERIFYSTATUS'))
                curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, true);
            */
        }
        $curlresponse = curl_exec($curl) === false ? false : true;
        $curlerror = curl_error($curl);

        if ($curlresponse !== true && !$verbose) {
            error_log(sprintf(
                "checkURLCert(%s, %s) verifypeer returned %d (%s)",
                $url,
                $modern ? 'true' : 'false',
                curl_getinfo($curl, CURLINFO_SSL_VERIFYRESULT),
                $curlerror,
            ));
        } elseif ($modern == true) {
            /* check for SHA1 */
            $chain = curl_getinfo($curl, CURLINFO_CERTINFO);
            $root = array_pop($chain); /* except root cert */
            foreach ($chain as $cert) {
                if (preg_match('/(sha1|md5)/i', $cert['Signature Algorithm'])) {
                    error_log(sprintf(
                        "checkURLCert(%s, %s) signature check found %s",
                        $url,
                        $modern ? 'true' : 'false',
                        $cert['Signature Algorithm'],
                    ));
                    $curlerror = sprintf('Signature check found %s', $cert['Signature Algorithm']);
                    $curlresponse = false;
                }
            }
        }

        if ($verbose) {
            return $curlerror;
        } else {
            return $curlresponse;
        }
    }

    /**
     * Check that this is a valid EmailAddress
     *
     * The syntax and right-hand side are checked, although the RHS check is
     * very simple - any valid DNS RR will cause it to pass.
     *
     * @param string $email
     * @return bool
     */
    public static function checkEmailAddress($email)
    {
        $email = preg_replace('/^mailto:/', '', trim($email));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }
        $domain = preg_replace('/^[^@]+\@/', '', $email);
        if (
            !(checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA'))
        ) {
            return false;
        }
        return true;
    }

    /**
     * For use in XSLT, check the string is base64 encoded
     * @param string $data
     * @return bool
     */
    public static function checkBase64($data)
    {
        if (!function_exists('base64_decode')) {
            error_log('checkBase64 needs URL functions');
            return true;
        }
        if (@base64_decode(preg_replace('/\s+/', '', $data), true) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check that the string is blank (i.e. empty or contains only whitespace)
     * @param string $data
     * @return bool
     */
    public static function checkStringIsBlank($data)
    {
        if (trim($data) == '') {
            return true;
        } else {
            return false;
        }
    }
}
