<?php
/**
 * Template header for metadata-validator
 *
 * SAFIRE uses SimpleSAMLphp, and so we need to skin this to look somewhat
 * like our version of SimpleSAMLphp. As such, parts of this template are
 * loosely derived from {@link http://simplesamlphp.org/ SimpleSAMLphp} 
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, SAFIRE - South African Identity Federation
 * @license https://github.com/safire-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */
if (preg_match('/\.local$/', $_SERVER['SERVER_NAME'])) {
    $domain = 'safire.local';
} else {
    $domain = 'safire.ac.za';
}$domain = 'safire.local';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<meta name="robots" content="index, nofollow">
	<link rel="license" content="https://opensource.org/licenses/MIT">
	
	<!-- these are SAFIRE-specific -->
	<title>validator.<?php echo $domain ?></title>
	<link rel="stylesheet" type="text/css" href="https://discovery.<?php echo $domain ?>/module.php/safire/resources/default.css">
	<link rel="icon" type="image/icon" href="https://discovery.<?php echo $domain ?>/module.php/safire/resources/favicon.ico">
	<link rel="icon" type="image/png" sizes="16x16" href="https://discovery.<?php echo $domain ?>/module.php/safire/resources/favicon-16x16.png">
	<link rel="icon" type="image/png" sizes="32x32" href="https://discovery.<?php echo $domain ?>/module.php/safire/resources/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="72x72" href="https://discovery.<?php echo $domain ?>/module.php/safire/resources/favicon-72x72.png">
	<script type="text/javascript" src="https://discovery.<?php echo $domain ?>/resources/script.js"></script>
	<meta name="theme-color" content="#5da9dd">
	
	<!-- these are the bits you need to keep in a new skin -->
	<link rel="stylesheet" type="text/css" href="ui/jquery-ui.css">
  	<link rel="stylesheet" type="text/css" href="ui/validator.css">
	<script src="//code.jquery.com/jquery-3.1.1.min.js"
            integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
	<script src="//code.jquery.com/jquery-migrate-3.0.0.js"></script>
	<script src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"
            integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.2.5/ace.js"></script>
	<script src="validate.js"></script>
</head>
<body>

<div id="wrap">

	<div id="header">
        <a title="South African Identity Federation" href="https://safire.ac.za/"><img src="https://discovery.<?php echo $domain ?>/module.php/safire/resources/SAFIRE_P_White_SimpleSAML.svg" alt="SAFIRE" style="float: right; display: inline; height: 72px;"></a>
		<h1><a style="text-decoration: none; color: white" href="/">validator.<?php echo $domain ?></a></h1>
	</div>
	<div id="languagebar">English</div>	<div id="content">
	