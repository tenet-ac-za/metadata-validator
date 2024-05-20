<?php

use PHPUnit\Framework\TestCase;

/* It seems the OpenSSL functions don't do timezones properly, so the results here vary depending on system timezone */
date_default_timezone_set('UTC');

class xsltfuncTest extends TestCase
{
    protected $selfsigned;
    protected $casigned;
    protected $expired;

    protected function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = 'validator.safire.ac.za';
        require_once(dirname(__DIR__) . '/local/xsltfunc.inc.php');
        $this->selfsigned = file_get_contents(__DIR__ . '/selfsigned.pem');
        $this->casigned = file_get_contents(__DIR__ . '/casigned.pem');
        $this->expired = file_get_contents(__DIR__ . '/expired.pem');
    }

    public function testCheckCertSelfSigned()
    {
        $this->assertTrue(xsltfunc::checkCertSelfSigned($this->selfsigned));
        $this->assertFalse(xsltfunc::checkCertSelfSigned($this->casigned));
        $this->assertFalse(xsltfunc::checkCertSelfSigned(''));
    }

    public function testCheckCertIsCA()
    {
        $this->assertTrue(xsltfunc::checkCertSelfSigned($this->selfsigned));
        $this->assertFalse(xsltfunc::checkCertSelfSigned($this->casigned));
    }

    public function testGetCertIssuer()
    {
        $this->assertStringContainsString('SWITCHaai', xsltfunc::getCertIssuer($this->casigned));
        $this->assertFalse(xsltfunc::getCertIssuer(''));
    }

    public function testCheckCertValid()
    {
        $this->assertTrue(xsltfunc::checkCertValid($this->selfsigned));
        $this->assertFalse(xsltfunc::checkCertValid($this->expired));
        $this->assertFalse(xsltfunc::checkCertValid(''));
    }

    public function testGetCertDates()
    {
        $this->assertEquals('2016-08-26', xsltfunc::getCertDates($this->selfsigned, 'from'));
        $this->assertEquals('2026-08-29', xsltfunc::getCertDates($this->selfsigned, 'to'));
        $this->assertEquals('1472202070 - 1787994070', xsltfunc::getCertDates($this->selfsigned, 'both', '%s'));
        $this->assertFalse(xsltfunc::getCertDates(''));
    }

    public function testGetCertBits()
    {
        $this->assertEquals(2048, xsltfunc::getCertBits($this->selfsigned));
        $this->assertEquals(2048, xsltfunc::getCertBits($this->casigned));
        $this->assertFalse(xsltfunc::getCertBits(''));
    }

    public function testcheckURL()
    {
        $this->assertTrue(xsltfunc::checkURL('https://safire.ac.za/'));
        $this->assertFalse(xsltfunc::checkURL('https://invalid-phpunit.safire.ac.za/'));
        $this->assertFalse(xsltfunc::checkURL('htt://safire.ac.za/'));
        $this->assertFalse(xsltfunc::checkURL('safire.ac.za/'));
    }

    public function testCheckURLCert()
    {
        $this->assertTrue(xsltfunc::checkURLCert('https://safire.ac.za/'));
        $this->assertFalse(xsltfunc::checkURLCert('https://expired.badssl.com/'));
        $this->assertFalse(xsltfunc::checkURLCert('https://wrong.host.badssl.com/'));
        $this->assertFalse(xsltfunc::checkURLCert('https://sha1-2017.badssl.com/', true));
        $this->assertFalse(xsltfunc::checkURLCert('https://untrusted-root.badssl.com/'));
        $this->assertMatchesRegularExpression('/(server certificate verification failed|unable to get local issuer certificate|self[- ]signed certificate in certificate chain)/', xsltfunc::checkURLCert('https://untrusted-root.badssl.com/', false, true));
        $this->assertFalse(xsltfunc::checkURLCert('https://rc4-md5.badssl.com/'));
    }

    public function testCheckEmailAddress()
    {
        $this->assertTrue(xsltfunc::checkEmailAddress('mailto:testCheckEmailAddress@safire.ac.za'));
        $this->assertTrue(xsltfunc::checkEmailAddress('testCheckEmailAddress@safire.ac.za'));
        $this->assertFalse(xsltfunc::checkEmailAddress('testCheckEmailAddress@@safire.ac.za'));
        $this->assertFalse(xsltfunc::checkEmailAddress('testCheckEmailAddress@safire.local'));
    }

    public function testCheckBase64()
    {
        $this->assertTrue(xsltfunc::checkBase64(base64_encode('this string is valid')));
        $this->assertFalse(xsltfunc::checkBase64('#'));
    }

    public function testCheckStringIsBlank()
    {
        $this->assertTrue(xsltfunc::checkStringIsBlank(''));
        $this->assertTrue(xsltfunc::checkStringIsBlank(' '));
        $this->assertFalse(xsltfunc::checkStringIsBlank(' a '));
    }
}
