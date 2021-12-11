<?php
/**
 * metadata-validator DCV generator
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2021, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */

if (file_exists(__DIR__ . '/local/config.inc.php')) {
    include_once(__DIR__ . '/local/config.inc.php');
}
include_once(__DIR__ . '/vendor/autoload.php');

use Pdp\Domain;
use Pdp\Rules;

function sendResponse($response, $status = 200) {
    if (array_key_exists('callback', $_REQUEST)) {
        header('content-type: text/javascript; charset=utf-8');
        echo addslashes($_REQUEST['callback']) . '(';
    } else {
        header('content-type: application/json; charset=utf-8');
    }
    http_response_code($status);
    header("access-control-allow-origin: *");

    if ($status != 200) {
        $response = [ 'error' => $response, 'code' => $status, ];
    }
    print json_encode(array_merge(
        [
            'entityID' => $_REQUEST['entityID'] ?: '[UNKNOWN]',
            'ref' => $_REQUEST['ref'] ?: null,
        ],
        $response
    ));

    if (array_key_exists('callback', $_REQUEST)) {
        print ');';
    }
    exit;
}

function getPublicSuffix ($domain) {
    global $publicSuffixList;
    if (!isset($publicSuffixList)) {
        if (!file_exists(constant('PUBLICSUFFIXLIST'))) {
            trigger_error('Could not open Public Suffix List');
            $publicSuffixList = Rules::fromPath(__DIR__ . '/vendor/jeremykendall/php-domain-parser/test_data/public_suffix_list.dat');
        } else {
            $publicSuffixList = Rules::fromPath(constant('PUBLICSUFFIXLIST'));
        }
    }
    $lookup = $publicSuffixList->resolve($domain);
    if ($lookup) {
        if ($lookup->registrableDomain()->toString()) {
            return $lookup->registrableDomain()->toString();
        } else {
            sendResponse('Cannot validate a public suffix of "' . strtoupper($domain) . '"', 403);
        }
    } else {
        return $domain;
    }
}

/**
 * Quick and dirty check that the result is what we expect...
 * NB! DNS caching is a problem...
 */
function checkDCVResult (&$dcv_result) {
    foreach ($dcv_result['rrset'] as $rrtype => $rdata) {
        $valid = true;
        foreach ($dcv_result['domains'] as $domain) {
            $dns = dns_get_record($dcv_result['label'] . '.' . $domain, constant('DNS_'.$rrtype));
            if ($dns === false) {
                $valid = false;
            } elseif (@$dns[0]['type'] != $rrtype) {
                $valid = false;
            } else {
                switch ($rrtype) {
                    case 'TXT':
                        if ($dns[0]['txt'] != $rdata) {
                            $valid = false;
                        }
                        break;
                    case 'CNAME':
                        if (rtrim($dns[0]['target'], '.') != rtrim($rdata, '.')) {
                            $valid = false;
                        }
                        break;
                    default:
                        $valid = false;
                }
            }
        }
        if ($valid) {
            $dcv_result['valid'][] = $rrtype;
        }
    }
}

if (empty($_REQUEST['entityID'])) {
    sendResponse('Invalid or nonexistent entityID', 400);
}

/**
 * @var Candidate domains for DCV
 */
$domains = [];
$warnings = [];

/**
 * See if we need to validate the domain from the entityID
 */
$url = parse_url($_REQUEST['entityID']);
if ($url['scheme'] == 'http' or $url['scheme'] == 'https') {
    $domains[] = getPublicSuffix($url['host']);
}

/**
 * Domains froms scopes
 */
if (!empty($_REQUEST['scopes'])) {
    foreach ($_REQUEST['scopes'] as $scope) {
        if (preg_match('/true/i', $scope['regexp'])) {
            $warnings[] = 'Cannot domain validate scopes with regular expressions: "' . $scope['scope'] . '"';
        } else {
            $domains[] = getPublicSuffix($scope['scope']);
        }
    }
}

/**
 * Reference handling
 */
if (empty($_REQUEST['ref'])) {
    sendResponse('Invalid or nonexistent DCV reference', 400);
} elseif (preg_match('/test/i', $_REQUEST['ref'])) {
    $_REQUEST['ref'] = 'TEST';
    $warnings[] = 'These are test values; expect different values when doing this in production';
}

/**
 * Assemble a DCV result
 */
$dcv_result = [
    /*The label must be "random", unique to this entity, and valid for no more than 30 days */
    'label' => '_' . substr(
        sha1(
            ceil((date('z') + 1) / 30) . ':' // month-ish number
            . strtolower(trim($_REQUEST['entityID'])) . ':'
            . constant('DCV_SECRET') . ':'
            . strtolower(trim($_REQUEST['ref']))
        ),
        0, 10
    ),
    /* Possible DNS responses, all pointing at the entityID in MDQ-ish form */
    'rrset' => [
        'TXT' => constant('DCV_TXT_PREFIX') . '{sha1}' . sha1($_REQUEST['entityID']),
        'CNAME' => '_' . sha1($_REQUEST['entityID']) . '.' . constant('DCV_CNAME_SUFFIX') . '.',
    ],
    /* a set of domains that must be validated */
    'domains' => array_values(array_unique($domains)),
    'warnings' => array_values(array_unique($warnings)),
];

if (array_key_exists('check', $_REQUEST) and $_REQUEST['check']) {
    checkDCVResult($dcv_result);
}

sendResponse($dcv_result);
