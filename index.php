<?php

/**
 * metadata-validator
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

include_once(__DIR__ . '/ui/header.inc.php');
?>
<h1>SAML Metadata Validator</h1>
<p>This validator applys very similar rules to SAFIRE's
<a href="https://phph.<?php echo constant('DOMAIN') ?>">metadata aggregator</a>. It
is intended to allow people to check their own metadata before submitting
it for inclusion in the federation registry. To test your metadata,
copy-and-paste it into the box below (or upload your metadata file).
Then click the [Validate!] button to begin.</p>

<div id="validator"></div>

<?php include_once(__DIR__ . '/ui/footer.inc.php'); ?>
