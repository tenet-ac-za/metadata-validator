<?php

use PHPUnit\Framework\TestCase;

/* It seems the OpenSSL functions don't do timezones properly, so the results here vary depending on system timezone */
date_default_timezone_set('UTC');

class XsltFuncTest extends TestCase
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
        $this->assertTrue(XsltFunc::checkCertSelfSigned($this->selfsigned));
        $this->assertFalse(XsltFunc::checkCertSelfSigned($this->casigned));
        $this->assertFalse(XsltFunc::checkCertSelfSigned(''));
    }

    public function testCheckCertIsCA()
    {
        $this->assertTrue(XsltFunc::checkCertSelfSigned($this->selfsigned));
        $this->assertFalse(XsltFunc::checkCertSelfSigned($this->casigned));
    }

    public function testGetCertIssuer()
    {
        $this->assertStringContainsString('SWITCHaai', XsltFunc::getCertIssuer($this->casigned));
        $this->assertFalse(XsltFunc::getCertIssuer(''));
    }

    public function testCheckCertValid()
    {
        $this->assertTrue(XsltFunc::checkCertValid($this->selfsigned));
        $this->assertFalse(XsltFunc::checkCertValid($this->expired));
        $this->assertFalse(XsltFunc::checkCertValid(''));
    }

    public function testGetCertDates()
    {
        $this->assertEquals('2016-08-26', XsltFunc::getCertDates($this->selfsigned, 'from'));
        $this->assertEquals('2026-08-29', XsltFunc::getCertDates($this->selfsigned, 'to'));
        $this->assertEquals('1472202070 - 1787994070', XsltFunc::getCertDates($this->selfsigned, 'both', '%s'));
        $this->assertFalse(XsltFunc::getCertDates(''));
    }

    public function testGetCertBits()
    {
        $this->assertEquals(2048, XsltFunc::getCertBits($this->selfsigned));
        $this->assertEquals(2048, XsltFunc::getCertBits($this->casigned));
        $this->assertFalse(XsltFunc::getCertBits(''));
    }

    public function testcheckURL()
    {
        $this->assertTrue(XsltFunc::checkURL('https://safire.ac.za/'));
        $this->assertFalse(XsltFunc::checkURL('https://invalid-phpunit.safire.ac.za/'));
        $this->assertFalse(XsltFunc::checkURL('htt://safire.ac.za/'));
        $this->assertFalse(XsltFunc::checkURL('safire.ac.za/'));
    }

    public function testCheckURLCert()
    {
        $this->assertTrue(XsltFunc::checkURLCert('https://safire.ac.za/'));
        $this->assertFalse(XsltFunc::checkURLCert('https://expired.badssl.com/'));
        $this->assertFalse(XsltFunc::checkURLCert('https://wrong.host.badssl.com/'));
        $this->assertFalse(XsltFunc::checkURLCert('https://sha1-2017.badssl.com/', true));
        $this->assertFalse(XsltFunc::checkURLCert('https://untrusted-root.badssl.com/'));
        $this->assertMatchesRegularExpression('/(server certificate verification failed|unable to get local issuer certificate|self[- ]signed certificate in certificate chain)/', XsltFunc::checkURLCert('https://untrusted-root.badssl.com/', false, true));
        $this->assertFalse(XsltFunc::checkURLCert('https://rc4-md5.badssl.com/'));
    }

    public function testCheckEmailAddress()
    {
        $this->assertTrue(XsltFunc::checkEmailAddress('mailto:testCheckEmailAddress@safire.ac.za'));
        $this->assertTrue(XsltFunc::checkEmailAddress('testCheckEmailAddress@safire.ac.za'));
        $this->assertFalse(XsltFunc::checkEmailAddress('testCheckEmailAddress@@safire.ac.za'));
        $this->assertFalse(XsltFunc::checkEmailAddress('testCheckEmailAddress@safire.local'));
    }

    public function testCheckBase64()
    {
        $this->assertTrue(XsltFunc::checkBase64(base64_encode('this string is valid')));
        $this->assertFalse(XsltFunc::checkBase64('#'));
    }

    public function testCheckStringIsBlank()
    {
        $this->assertTrue(XsltFunc::checkStringIsBlank(''));
        $this->assertTrue(XsltFunc::checkStringIsBlank(' '));
        $this->assertFalse(XsltFunc::checkStringIsBlank(' a '));
    }
}
