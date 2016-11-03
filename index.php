<?php
/**
 * metadata-validator
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, SAFIRE - South African Identity Federation
 * @license https://github.com/safire-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */
include_once('ui/header.inc');
?>
<h2>SAML Metadata Validator</h2>
<p>This validator applys very similar rules to SAFIRE's
<a href="https://phph.<?php echo $domain ?>">metadata aggregator</a>. It
is intended to allow people to check their own metadata before submitting
it for inclusion in the federation registry. To test your metadata,
copy-and-paste it into the box below (or upload your metadata file).
Then click the [Validate!] button to begin.</p>

<div id="validator">
    <div id="metadata"></div>
    <div id="progress"></div>
    <div id="buttons">
        <input id="validate" type="button" value="Validate!" class="button">
        <div class="right">
            <input id="mdurl" type="button" value="Fetch URL..." class="button">
            <label for="mdfile">Upload file...</label>
            <input id="mdfile" type="file" multiple="">
        </div>
    </div>
    <div id="results" class="hidden"></div>
</div>

<?php include_once('ui/footer.inc'); ?>