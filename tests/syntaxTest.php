<?php
use \PHPUnit\Framework\TestCase;

/** @runTestsInSeparateProcesses */
class syntaxTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = 'validator.safire.ac.za';
    }

    public function testIndex()
    {
        ob_start();
        include_once(dirname(__DIR__) . '/index.php');
        $output = ob_get_clean();
        $this->assertStringContainsString('!DOCTYPE html', $output);
    }

    public function testFetchmetadata()
    {
        $_REQUEST['url'] = 'https://metadata.safire.ac.za/safire-hub-metadata.xml';
        ob_start();
        include_once(dirname(__DIR__) . '/fetchmetadata.php');
        $output = ob_get_clean();
        $this->assertStringContainsString('<?xml', $output);
    }

    /* needs work! */
    /** @preserveGlobalState disabled */
    public function testValidate()
    {
        $_SERVER['CONTENT_TYPE'] = 'text/xml';
        try {
            ob_start();
            include_once(dirname(__DIR__) . '/validate.php');
        } catch (RuntimeException $e) {
            $this->assertEquals("exit()", $e->getMessage());
        }
        $output = ob_get_clean();
        $this->assertStringContainsString('[ERROR] No input supplied', $output);
        $this->assertNotNull(json_decode($output));
    }
}
