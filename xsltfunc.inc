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
		if (array_diff_assoc($x509data['subject'], $x509data['issuer']))
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
			error_log('xslCheckBase64 needs URL functions');
			return true;
		}
		if (@base64_decode(preg_replace('/\s+/', '', $data), true) === false)
			return false;
		else
			return true;
	}

}
