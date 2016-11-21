<?php
/**
 * Functions for use in XSLT
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, SAFIRE - South African Identity Federation
 * @license https://github.com/safire-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */
class xsltfunc {
    /**
     * Take a PEM representation of a certificate and return the x509 structure
     *
     * @param string $x509certdata
     * @return x509cert|false $x509cert
     */
    static private function _pemToX509($x509certdata)
    {
        if (!function_exists('openssl_x509_read')) {
            error_log('_pemToX509 needs OpenSSL functions');
            return false;
        }
        $pem = trim($x509certdata);
        if (!preg_match('/^-----BEGIN CERTIFICATE/',$pem)) {
            $pem = "-----BEGIN CERTIFICATE-----\n" . wordwrap($pem, 64, "\n", true) . "\n-----END CERTIFICATE-----\n";
        }
        $x509cert = @openssl_x509_read($pem);
        if ($x509cert === false)
            return false;
        return $x509cert;
    }

    /**
     * For use in XSLT, checks a cert is self-signed
     *
     * @param string $cert
     * @return bool
     */
    static public function checkCertSelfSigned($cert)
    {
        $x509data = @openssl_x509_parse(self::_pemToX509($cert));
        if (empty($x509data))
            return false;
        if (!array_key_exists('subject', $x509data) or
            !array_key_exists('issuer', $x509data))
            return false;
        if (array_diff_assoc($x509data['subject'], $x509data['issuer']))
            return false;
        return true;
    }

    /**
     * Return the certificate issuer name
     *
     * @param string $cert
     * @return string $issuer
     */
    static public function getCertIssuer($cert)
    {
        $x509data = @openssl_x509_parse(self::_pemToX509($cert));
        if (empty($x509data))
            return false;
        $issuer = array_key_exists('issuer', $x509data)
            ? (array_key_exists('CN', $x509data['issuer'])
                ? $x509data['issuer']['CN']
                : join('/', $x509data['issuer'])
            ) : false;
        return $issuer;
    }

    /**
     * Check the certificate expiry
     *
     * @param string $cert
     * @param string $fromto
     * @return bool
     */
    static public function checkCertValid($cert, $fromto = 'both')
    {
        $x509data = @openssl_x509_parse(self::_pemToX509($cert));
        if (empty($x509data))
            return false;
        if ($fromto != 'to' and $x509data['validFrom_time_t'] >= time())
            return false;
        if ($fromto != 'from' and $x509data['validTo_time_t'] < (time() + 30 * 86400))
            return false;
        return true;
    }

    /**
     * Return the certificate dates
     *
     * @param string $cert
     * @param string $fromto
     * @param string $format per strftime
     * @return string $date
     */
    static public function getCertDates($cert, $fromto = 'both', $format = '%F')
    {
        $x509data = @openssl_x509_parse(self::_pemToX509($cert));
        if (empty($x509data))
            return false;
        switch ($fromto) {
            case 'from':
                return strftime($format, $x509data['validFrom_time_t']);
            case 'to':
                return strftime($format, $x509data['validTo_time_t']);
            case 'both':
                return strftime($format, $x509data['validFrom_time_t']) . ' - ' . strftime($format, $x509data['validTo_time_t']);
            default:
                return false;
        }
    }

    /**
     * Check a URL
     *
     * This is a bit simplistic - it merely checks that the web server
     * can be reached and answers with something halfway valid. Will
     * accept any cert for https - use checkURLCert to verify it.
     *
     * @param string $url
     * @param bool $checkstatus
     * @param string $type
     * @return bool
     */
    static public function checkURL($url)
    {
        if (!function_exists('curl_init')) {
            error_log('checkURL needs cURL functions');
            return false;
        }
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED) === false)
            return false;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache'));
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        return curl_exec($curl);
    }

    /**
     * Check the certificate used by a remote web server
     *
     * This verifies the SSL cert against cURL's internal
     * list of certificate authorities
     *
     * @param string $url
     * @return bool
     */
    static public function checkURLCert($url)
    {
        if (!function_exists('curl_init')) {
            error_log('checkURLCert needs cURL functions');
            return false;
        }
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED) === false)
            return false;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache'));
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CERTINFO, true);
        if (defined('CURLOPT_SSL_VERIFYSTATUS'))
            curl_setopt($curl, CURLOPT_SSL_VERIFYSTATUS, true);
        return curl_exec($curl);
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
    static public function checkEmailAddress($email)
    {
        if (!function_exists('checkdnsrr')) {
            error_log('checkEmailAddress needs Network functions');
            return false;
        }
        $email = preg_replace('/^mailto:/', '', trim($email));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            return false;
        $domain = preg_replace('/^[^@]+\@/', '', $email);
        if (!checkdnsrr($domain, 'ANY'))
            return false;
        return true;
    }

    /**
     * For use in XSLT, check the string is base64 encoded
     * @param string $data
     * @return bool
     */
    static public function checkBase64($data)
    {
        if (!function_exists('base64_decode')) {
            error_log('checkBase64 needs URL functions');
            return true;
        }
        if (@base64_decode(preg_replace('/\s+/', '', $data), true) === false)
            return false;
        else
            return true;
    }

    /**
     * Check that the string is blank (i.e. empty or contains only whitespace)
     * @param string $data
     * @return bool
     */
    static public function checkStringIsBlank($data)
    {
        if (trim($data) == '')
            return true;
        else
            return false;
    }

    /**
     * Check that the string is valid JSON
     * @param string $data
     * @param string $type type to check against gettype()
     * @return bool
     */
    static public function checkJSON($data, $type = null)
    {
        if (!function_exists('json_decode')) {
            error_log('checkJSON needs JSON functions');
            return true;
        }
        $json = @json_decode(trim($data));
        if ($json === null)
            return false;
        elseif (is_string($type) and gettype($json) != $type)
            return false;
        else
            return true;
    }

}
