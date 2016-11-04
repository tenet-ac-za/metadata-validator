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
	 * For use in XSLT, checks a cert is self-signed
	 *
	 * @param string $cert
	 * @return bool
	 */
	static public function checkCertSelfSigned($cert)
	{
		if (!function_exists('openssl_x509_read')) {
			error_log('xslCheckCertSelfSigned needs OpenSSL functions');
			return true;
		}
		$pem = "-----BEGIN CERTIFICATE-----\n" . wordwrap(trim($cert), 64, "\n", true) . "\n-----END CERTIFICATE-----\n";
		$x509 = @openssl_x509_read($pem);
		if ($x509 === false)
			return false;
		$x509data = @openssl_x509_parse($x509);
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
