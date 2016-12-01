<?php
/** @runTestsInSeparateProcesses */
class syntaxTest extends \PHPUnit_Framework_TestCase
{
    public function testIndex()
    {
        $_SERVER['SERVER_NAME'] = 'validator.safire.ac.za';
        ob_start();
        include_once(dirname(__DIR__) . '/index.php');
        $output = ob_get_clean();
        $this->assertContains('!DOCTYPE html', $output);
    }

    public function testFetchmetadata()
    {
        $_SERVER['SERVER_NAME'] = 'validator.safire.ac.za';
        $_REQUEST['url'] = 'https://metadata.safire.ac.za/safire-hub-metadata.xml';
        ob_start();
        include_once(dirname(__DIR__) . '/fetchmetadata.php');
        $output = ob_get_clean();
        $this->assertContains('<?xml', $output);
    }

    /* needs work! */
    /** @preserveGlobalState disabled */
    public function testValidate()
    {
        $_SERVER['SERVER_NAME'] = 'validator.safire.ac.za';
        $_SERVER['CONTENT_TYPE'] = 'text/xml';
        ob_start();
        // include_once(dirname(__DIR__) . '/validate.php');
        $output = ob_get_clean();
        // $this->assertNotNull(json_decode($output));
    }
}
