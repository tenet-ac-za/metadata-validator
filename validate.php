<?php

/**
 * metadata-validator AJAX POST handler
 *
 * This backend takes an AJAX POST (using jQuery or similar) of type text/xml,
 * assumes it is SAML metadata, and tries to validate it using rules that are
 * somewhat similar (and derived from) WAYF's {@link https://github.com/wayf-dk/phph PHPH}
 * metadata aggregator.
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */

if (file_exists(__DIR__ . '/local/config.inc.php')) {
    include_once(__DIR__ . '/local/config.inc.php');
}

/** @var array $namespaces SAML namespaces lookup table */
$namespaces = array(
    'urn:oasis:names:tc:SAML:2.0:protocol' => 'samlp',
    'urn:oasis:names:tc:SAML:2.0:assertion' => 'saml',
    'urn:mace:shibboleth:metadata:1.0' => 'shibmd',
    'urn:oasis:names:tc:SAML:2.0:metadata' => 'md',
    'urn:oasis:names:tc:SAML:metadata:rpi' => 'mdrpi',
    'urn:oasis:names:tc:SAML:metadata:ui' => 'mdui',
    'urn:oasis:names:tc:SAML:metadata:attribute' => 'mdattr',
    'urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol' => 'idpdisc',
    'urn:oasis:names:tc:SAML:profiles:SSO:request-init' => 'init',
    'http://www.w3.org/2001/XMLSchema-instance' => 'xsi',
    'http://www.w3.org/2001/XMLSchema' => 'xs',
    'http://www.w3.org/1999/XSL/Transform' => 'xsl',
    'http://www.w3.org/XML/1998/namespace' => 'xml',
    'http://schemas.xmlsoap.org/soap/envelope/' => 'SOAP-ENV',
    'http://www.w3.org/2000/09/xmldsig#' => 'ds',
    'http://www.w3.org/2001/04/xmlenc#' => 'xenc',
    'urn:oasis:names:tc:SAML:metadata:algsupport' => 'algsupport',
    'http://refeds.org/metadata' => 'remd',
);
$secapseman = array_flip($namespaces);

/** @var array $passes Descriptive names for the various passes */
$GLOBALS['passes'] = array(
    'pre-flight checks',
    'valid XML (parser)',
    'valid namespaces (parser)',
    'verify SAML metadata schema',
    'verify local metadata schemas',
    'metadata testing rules',
    'local metadata testing rules',
);

/**
 * array_filter function to exclude certain libXMLError errors
 *
 * @return array
 */
function filter_libxml_errors()
{
    $errors = libxml_get_errors();
    $filteredErrors = array();
    foreach ($errors as $error) {
        if ($error->code == 1209) {
            continue;
        }
        if ($error->code == 1 and preg_match('/xmlXPathCompOpEval: function .+ not found/', $error->message)) {
            continue;
        }
        if ($error->code == 1 and preg_match('/entity does not have an mdrpi:RegistrationInfo element/', $error->message)) {
            continue;
        }
        if ($error->code == 1 and preg_match('/entity has legacy KeyName element/', $error->message)) {
            continue;
        }
        if ($error->code == 1 and preg_match('/\[ERROR\] regular expression in scope/', $error->message)) {
            continue;
        }
        $filteredErrors[] = $error;
    }
    return $filteredErrors;
}

/**
 * send a JSON encoded version of the libXMLError
 *
 * @param array $response
 */
function sendResponse($response, $pass = 0)
{
    header('Content-Type: application/json');
    if (is_string($response)) {
        // emulate libXMLError
        $err = new libXMLError();
        $err->level = LIBXML_ERR_FATAL;
        $err->code = 4;
        $err->column = 0;
        $err->message = '[ERROR] ' . $response;
        $err->file = '';
        $err->line = 0;
        $response = array ($err);
    }
    /* if this is only info messages, return success */
    $success = true;
    foreach ($response as $err) {
        if ($err->code == 1 and preg_match('/\[INFO\]/', $err->message)) {
            continue;
        }
        $success = false;
    }
    // error_log(var_export($response, true));
    print json_encode(array(
        'pass' => $pass,
        'passdescr' => $GLOBALS['passes'][$pass],
        'passes' => count($GLOBALS['passes']) - 1,
        'success' => $success,
        'errors' => $response
    ));
    if (! defined('PHPUNIT_COMPOSER_INSTALL') && ! defined('__PHPUNIT_PHAR__')) {
        exit; /* can't unit test this */
    } else {
        throw new RuntimeException("exit()");
    }
}

/* 0 - preflight: did we get the right content type */
if ($_SERVER["CONTENT_TYPE"] !== 'text/xml') {
    sendResponse('Incorrect content-type: header');
}

/* 0 - preflight: could we read it */
$xml = file_get_contents('php://input');
if (empty($xml)) {
    sendResponse('No input supplied');
}

/* 1 - valid XML: parse the XML into DOM */
libxml_use_internal_errors(true);
libxml_clear_errors();
$doc = new DOMDocument();
$doc->preserveWhiteSpace = true;
if ($doc->loadXML($xml) !== true) {
    $errors = filter_libxml_errors();
    sendResponse($errors, 1);
}

/* 2 - valid namespaces: turn it into an XPath */
libxml_clear_errors();
$xp = new DomXPath($doc);
foreach ($namespaces as $full => $prefix) {
    $xp->registerNamespace($prefix, $full);
}
$errors = filter_libxml_errors();
if ($errors) {
    sendResponse($errors, 2);
}

/* 3 - verify SAML schema */
libxml_clear_errors();
$xp->document->schemaValidate('./schemas/ws-federation.xsd');
$errors = filter_libxml_errors();
if ($errors) {
    sendResponse($errors, 3);
}

/* 4 - verify local schemas */
libxml_clear_errors();
$localschemas = glob('./local/*.xsd');
foreach ($localschemas as $schema) {
    $xp->document->schemaValidate($schema);
}
$errors = filter_libxml_errors();
if ($errors) {
    sendResponse($errors, 4);
}

/* 5 - use Ian Young's SAML metadata testing rules */
libxml_clear_errors();
$xslt = new XSLTProcessor();
$rules = glob('./rules/*.xsl');
foreach ($rules as $rule) {
    if (preg_match('/check_framework\.xsl$/', $rule)) {
        continue;
    }
    $xslt->setParameter('', 'expectedAuthority', constant('REGISTRATION_AUTHORITY'));
    $xslt->importStylesheet(new SimpleXMLElement($rule, 0, true));
    $xslt->transformToDoc($xp->document);
}
$errors = filter_libxml_errors();
if ($errors) {
    sendResponse($errors, 5);
}

/* 6 - use local SAML metadata testing rules */
if (file_exists(__DIR__ . '/local/xsltfunc.inc.php')) {
    include_once(__DIR__ . '/local/xsltfunc.inc.php');
    $xslt->registerPHPFunctions(
        array_map(function ($n) {
            return 'XsltFunc::' . $n;
        }, get_class_methods('XsltFunc'))
    );
}
libxml_clear_errors();
$localrules = glob('./local/*.xsl');
foreach ($localrules as $rule) {
    if (preg_match('/check_framework\.xsl$/', $rule) or preg_match('/ns_norm\.xsl$/', $rule)) {
        continue;
    }
    $xslt->importStylesheet(new SimpleXMLElement($rule, 0, true));
    $xslt->transformToDoc($xp->document);
}
$errors = filter_libxml_errors();
if ($errors) {
    sendResponse($errors, 6);
}

/* we got this far, so everything is okay! */
print json_encode(array(
    'pass' => count($GLOBALS['passes']),
    'success' => true,
    'errors' => null
));
