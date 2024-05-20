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
if (!function_exists('apache_setenv') or !apache_setenv('CSP_NONCE', $nonce)) {
    $nonce = array_key_exists('CSP_NONCE', $_SERVER) ? $_SERVER['CSP_NONCE'] : getenv('CSP_NONCE');
}
$nonce = sprintf(' nonce="%s"', $nonce);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="description" content="A SAML2 metadata validator that checks for both syntactic/schema correctness as well as applying site-specific local checks">
    <meta name="robots" content="noindex, nofollow">
    <link rel="license" href="https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE">
    <title><?= $_SERVER['SERVER_NAME'] ?></title>

    <!-- these are SAFIRE-specific -->
    <link rel="preconnect" href="https://static.<?= constant('DOMAIN') ?>">
    <link rel="stylesheet" href="https://static.<?= constant('DOMAIN') ?>/css/ssp-stylesheet-test.css">
    <link rel="icon" type="image/icon" href="https://static.<?= constant('DOMAIN') ?>/favicons/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="https://static.<?= constant('DOMAIN') ?>/favicons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://static.<?= constant('DOMAIN') ?>/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="72x72" href="https://static.<?= constant('DOMAIN') ?>/favicons/favicon-72x72.png">
    <link rel="icon" type="image/png" sizes="150x150" href="https://static.<?= constant('DOMAIN') ?>/favicons/favicon-150x150.png">
    <meta name="theme-color" content="#5da9dd">

    <!-- these are the bits you need to keep in a new skin -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" type="text/css" href="ui/jquery-ui.css">
    <!-- cdnjs preferred, but doesn't have style sheets for ace editor :-(. These are unnecessary if you disable useStrictCSP in footer.inc.php -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ace-builds@1.33.2/css/ace.css" integrity="sha256-attAqBHW7Lrtbe8maDpZhm2GoONy1kaP6RFBAYp3bGI=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ace-builds@1.33.2/css/theme/xcode.css" integrity="sha256-j5T9X1QnSjk9DPjHuebrv01S8/x1VqCWGAhq4NYPdh8=" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="ui/validator.css">

    <!-- this is for the github-corners - https://github.com/tholman/github-corners -->
    <style<?php echo $nonce; ?>>
        .github-corner:hover .octo-arm{animation:octocat-wave 560ms ease-in-out}
        @keyframes octocat-wave{0%,100%{transform:rotate(0)}20%,60%{transform:rotate(-25deg)}40%,80%{transform:rotate(10deg)}}
        @media (max-width:500px){
            .github-corner:hover .octo-arm{animation:none}
            .github-corner .octo-arm{animation:octocat-wave 560ms ease-in-out}
        }
        .github-corner svg{fill:#eee; color:var(--safire-purple); position: absolute; top: 0; border: 0; right: 0;}
        .github-corner .octo-arm{transform-origin: 130px 106px;}
    </style>
</head>
<body>
  <div id="layout">
    <header id="header"<?= strpos($_SERVER['SERVER_NAME'], '.local') === false ? '' : '  class="preproduction"' ?>>
      <div class="wrap">
        <div class="logospace">
          <div class="v-center logo-header">
            <div id="logo">
              <a href="https://<?= constant('DOMAIN') ?>/">
                <img id="safire-logo" class="pure-img" src="https://static.<?= constant('DOMAIN') ?>/logos/SAFIRE_P_White_SimpleSAML.svg" width="258" height="72" alt="SAFIRE">
              </a>
            </div>
          </div>
        </div>
      </div>
      <!-- this is for the github-corners - https://github.com/tholman/github-corners -->
      <a href="https://github.com/tenet-ac-za/metadata-validator" target="_blank" class="github-corner" aria-label="View source on Github">
        <svg width="80" height="80" viewBox="0 0 250 250" aria-hidden="true">
            <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path>
            <path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" class="octo-arm"></path>
            <path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body"></path>
        </svg>
      </a>
      <!-- end of github-corners -->
    </header>
    <main>
      <div id="content">
        <div class="wrap">
