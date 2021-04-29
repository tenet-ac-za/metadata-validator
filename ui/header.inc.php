<?php
/**
 * Template header for metadata-validator
 *
 * SAFIRE uses SimpleSAMLphp, and so we need to skin this to look somewhat
 * like our version of SimpleSAMLphp. As such, parts of this template are
 * loosely derived from {@link http://simplesamlphp.org/ SimpleSAMLphp}
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */
if (file_exists(dirname(__DIR__) . '/local/config.inc.php')) {
    include_once(dirname(__DIR__) . '/local/config.inc.php');
}

$nonce = base64_encode(random_bytes(32));
if (function_exists('apache_setenv')) {
    apache_setenv('CSP_NONCE', $nonce);
}
$nonce = sprintf(' nonce="%s"', $nonce);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0">
    <meta name="robots" content="index, nofollow">
    <meta name="creator" content="South African Identity Federation">
    <meta name="description" content="A SAML2 metadata validator that checks for both syntactic/schema correctness as well as applying site-specific local checks">
    <link rel="license" href="https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE">

    <!-- these are SAFIRE-specific -->
    <title>validator.<?php echo constant('DOMAIN') ?></title>
    <link rel="stylesheet" type="text/css" href="//static.<?php echo constant('DOMAIN') ?>/css/ssp-default.css">
    <link rel="icon" type="image/icon" href="//static.<?php echo constant('DOMAIN') ?>/favicons/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="//static.<?php echo constant('DOMAIN') ?>/favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="//static.<?php echo constant('DOMAIN') ?>/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="72x72" href="//static.<?php echo constant('DOMAIN') ?>/favicons/favicon-72x72.png">
    <link rel="icon" type="image/png" sizes="150x150" href="//static.<?php echo constant('DOMAIN') ?>/favicons/favicon-150x150.png">
    <meta name="theme-color" content="#5da9dd">

    <!-- these are the bits you need to keep in a new skin -->
    <link rel="stylesheet" type="text/css" href="ui/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="ui/validator.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
            integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"
            integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"
            integrity="sha512-GZ1RIgZaSc8rnco/8CXfRdCpDxRCphenIiZ2ztLy3XQfCbQUSCuk8IudvNHxkRA3oUg6q0qejgN/qqyG1duv5Q==" crossorigin="anonymous"></script>
    <script src="validate.js"></script>

    <!-- this is for the github-corners - https://github.com/tholman/github-corners -->
    <style<?php echo $nonce; ?>>
        .github-corner:hover .octo-arm{animation:octocat-wave 560ms ease-in-out}
        @keyframes octocat-wave{0%,100%{transform:rotate(0)}20%,60%{transform:rotate(-25deg)}40%,80%{transform:rotate(10deg)}}
        @media (max-width:500px){
            .github-corner:hover .octo-arm{animation:none}
            .github-corner .octo-arm{animation:octocat-wave 560ms ease-in-out}
        }
        .github-corner svg{fill:#eee; color:#5da9dd; position: absolute; top: 0; border: 0; right: 0;}
        .github-corner .octo-arm{transform-origin: 130px 106px;}
    </style>
</head>
<body>

<!-- this is for the github-corners - https://github.com/tholman/github-corners -->
<a href="https://github.com/tenet-ac-za/metadata-validator" target="_blank" class="github-corner" aria-label="View source on Github">
    <svg width="80" height="80" viewBox="0 0 250 250" aria-hidden="true">
        <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path>
        <path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" class="octo-arm"></path>
        <path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body"></path>
    </svg>
</a>
<!-- end of github-corners -->

<div id="wrap">

    <div id="header" class="safire-header">
        <a title="South African Identity Federation" href="https://safire.ac.za/"><img src="//static.<?php echo constant('DOMAIN') ?>/logos/SAFIRE_P_White_SimpleSAML.svg" alt="SAFIRE" width="258" height="72" alt="[SAFIRE]"></a>
        <h1><a href="/">validator.<?php echo constant('DOMAIN') ?></a></h1>
    </div>
    <div id="languagebar">&nbsp;</div>  <div id="content">
