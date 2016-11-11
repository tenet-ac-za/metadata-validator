<?php
require_once ('PHPUnit/Autoload.php');
require_once (dirname(__DIR__) . '/local/xsltfunc.inc.php');

class xsltfuncTest extends PHPUnit_Framework_TestCase
{
    protected $selfsignedcert;
    protected $notselfsignedcert;

    public function __construct()
    {
        $this->selfsigned = file_get_contents(__DIR__ . '/selfsigned.pem');
        $this->casigned = file_get_contents(__DIR__ . '/casigned.pem');
        $this->expired = file_get_contents(__DIR__ . '/expired.pem');
    }

    public function testCheckCertSelfSigned()
    {
        $this->assertTrue(xsltfunc::checkCertSelfSigned($this->selfsigned));
        $this->assertFalse(xsltfunc::checkCertSelfSigned($this->casigned));
    }

    public function testCheckCertValid()
    {
        $this->assertTrue(xsltfunc::checkCertValid($this->selfsigned));
        $this->assertFalse(xsltfunc::checkCertValid($this->expired));
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
        $this->assertFalse(xsltfunc::checkURLCert('https://www.pcwebshop.co.uk/'));
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
}
