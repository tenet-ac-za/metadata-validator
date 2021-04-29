/**
 * Javascript support functions for metadata-validator
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 * @requires jQuery
 * @requires jquery-ui.js
 * @requires ace.js
 */
var editor;
var spinner;
var content;

/**
 * reset the results pane
 */
function resetUI()
{
    /* reset the UI */
    $('#validator').removeClass('validator-invalid');
    $('#validator').removeClass('validator-valid');
    $('#validator #results').html('<ul></ul>');
    $('#validator #results').addClass('validator-hidden');
    $('#validator #progress').progressbar('option', 'disabled', 'true');
    $('#validator #progress').progressbar('option', 'value', 0);
}

/**
 * construct the results pane from json
 */
function populateResultsPane(data)
{
    console.log(data);
    var firstError = true;
    $('#validator #results').html(
        '<h3>Results from validation pass ' + data['pass'] + '/' + data['passes'] +
        '<span class="validator-passdescr"> (' + data['passdescr'] + ')</span>' +
        ':</h3><ul></ul>'
    );
    $.each(data['errors'], function (k, err) {
        if (firstError == true && err['line'] > 0) {
            editor.gotoLine(err['line'], err['column'], true);
            firstError = false;
        }
        var msg = err['message'];
        if (msg.match(/\[ERROR\]/) || (err['code'] != 1 && err['level'] > 1)) {
            msg = '<span class="validator-mesg-error">' + msg + '</span>';
        }
        if (msg.match(/\[WARN\]/) || (err['code'] != 1 && err['level'] == 1)) {
            msg = '<span class="validator-mesg-warn">' + msg + '</span>';
        }
        if (msg.match(/\[INFO\]/)) {
            msg = '<span class="validator-mesg-info">' + msg + '</span>';
        }
        if (err['line'] > 0) {
            msg = msg + ' <span class="validator-linenum">[<a href="#" onclick="editor.gotoLine(' + err['line'] + ',' + err['column'] + ')">' + err['line'] + ', ' + err['column'] + '</a>]</span>';
        }
        $('#validator #results ul').append('<li>' + msg + '</li>');
    });
    $('#validator #results').removeClass('validator-hidden');
}

/**
 * callback to handle the AJAX response from validation
 */
function validationResults(data)
{
    if (data['errors'] == null || data['success'] != false) {
        console.log("SUCCESS!");
        if (data['errors'] && data['errors'].length) {
            populateResultsPane(data);
        } else {
            resetUI();
        }
        $('#validator').addClass('validator-valid');
        $('#validator #progress').progressbar('option', 'value', 100);
        editor.gotoLine(1, 0);
    } else {
        populateResultsPane(data);
        $('#validator').addClass('validator-invalid');
        $('#validator #progress').progressbar('option', 'value', (data['pass'] / data['passes']) * 100);
    }
}

/**
 * Send the XML to the server for validation
 */
function sendForValidation()
{
    resetUI();
    $('#validator #progress').progressbar('option', 'disabled', 'false');
    var editorData = editor.getValue();
    $.ajax({
        type: 'POST',
        url: "validate.php",
        data: editorData,
        contentType: 'text/xml',
        processData: false,
        cache: false,
        success: validationResults
    });
}

/**
 * Send the XML to the server for normalisation
 */
function sendForNormalisation()
{
    resetUI();
    var editorData = editor.getValue();
    $.ajax({
        type: 'POST',
        url: "normalise.php",
        data: editorData,
        contentType: 'text/xml',
        processData: false,
        cache: false,
        success: function(data) {
            editor.setValue(data);
            resetUI();
            editor.gotoLine(1, 0);
        }
    });
}

/**
 * Render DCV RR
 */
function renderDCVDNS(data, rrtype = 'TXT')
{
    var dns = '';
    if (data['warnings'] && data['warnings'].length) {
        $.each(data['warnings'], function (i, v) {
            dns = dns + '# WARNING: ' + v + "\n";
        });
    }
    for (var i = 0; i < data['domains'].length; i++) {
        dns = dns + data['label'] + '.' + data['domains'][i] + ". IN ";
        dns = dns + rrtype + ' ';
        if (rrtype == 'TXT') { dns = dns + '"'; }
        dns = dns + data['rrset'][rrtype];
        if (rrtype == 'TXT') { dns = dns + '"'; }
        dns = dns + "\n";
    }
    console.log(dns);
    return dns;
}

/**
 * Send the XML to the server for DCV
 */
function sendForDCV()
{
    resetUI();
    var editorData = editor.getValue();
    var editorXML = $.parseXML(editorData);
    var editorJSON = { 'entityID' : null, 'scopes' : [] };
    var entityDescriptor = editorXML.getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'EntityDescriptor');
    if (entityDescriptor) {
        editorJSON.entityID = entityDescriptor[0].getAttribute('entityID');
    }
    var scopesXML = editorXML.getElementsByTagNameNS('urn:mace:shibboleth:metadata:1.0', 'Scope');
    for (var i = 0; i < scopesXML.length; i++) {
        editorJSON.scopes.push({ 'scope' : scopesXML.item(i).textContent, 'regexp' : scopesXML.item(i).getAttribute('regexp') });
    }
    $.ajax({
        url: "dcv.php",
        data: editorJSON,
        dataType: 'json', /* note CSP for jsonp */
        cache: false,
        success: function(data, textStatus, jqxhr) {
            var msg = '<div id="validator-dialog-dcv" title="Domain Control Validation for &quot;' + data['entityID'] + '&quot;">' +
                '<p>In order to validate your ownership of this entity, you need to create one of the following sets of DNS records:</p>' +
                '<div id="validator-dialog-dcv-tabs"><ul>';
            $.each(data['rrset'], function (i, v) {
                msg = msg + '<li><a href="#validator-dialog-dcv-rr-' + i + '">' + i + '</a></li>';
            });
            msg = msg + '</ul>';
            $.each(data['rrset'], function (i, v) {
                msg = msg + '<div id="validator-dialog-dcv-rr-' + i + '"><pre style="text-align: left">' + renderDCVDNS(data, i) + '</pre></div>';
            });
            msg = msg + '</div></div>';
            $('#validator').append(msg);
            $('#validator-dialog-dcv-tabs').tabs();
        },
        error: function(jqxhr, textStatus) {
            var data = jqxhr.responseJSON;
            var msg = '<div id="validator-dialog-dcv" title="Domain Control Validation Error">' +
                '<p>Failed to determine DCV requirements for &quot;' + data['entityID'] + '&quot;:</p>' +
                '<div id="validator-dcv-error" class="validator-mesg-error">' + data['error'] + '</div></div>';
            $('#validator').append(msg);
        },
        complete: function(jqxhr, textStatus) {
            $('#validator-dialog-dcv').dialog({
                modal: true,
                width: 'auto',
                buttons: {
                    Ok: function() {
                        $( this ).dialog('close');
                        $( this ).remove();
                    }
                }
            });
        }
    });
}

/*
 * Fetch metata from a URL (via a proxy)
 */
function fetchFromURL(url)
{
    console.log('Fetching metadata from ' + url);
    $.ajax({
        method: 'GET',
        url: 'fetchmetadata.php',
        data: { url: url },
        success: function(data) {
            editor.setValue(data);
            resetUI();
            editor.gotoLine(1, 0);
        },
        error: function(jqxhr) {
            console.log(jqxhr);
            if (jqxhr.status == 502) {
                error = jqxhr.responseText;
            } else {
                error = jqxhr.statusText;
            }
            $('#validator').append(
                '<div id="validator-dialog-error" title="An error has occurred">' +
                '<p>An error occurred fetching data from &quot;' + url + '&quot;</p>' +
                '<p>' + error + '</p>' +
                '</div>'
            );
            $('#validator-dialog-error').dialog({
                modal: true,
                buttons: {
                    Ok: function() {
                        $( this ).dialog('close');
                        $( this ).remove();
                    }
                }
            });
        },
        dataType: 'text'
    });
}

/**
 * Create a dialog for to get the metadata URL
 */
function createFetchURLDialog()
{
    $('#validator').append(
        '<div id="validator-dialog-form" title="Enter address of metadata server:" style="overflow:hidden;text-align:center;"><form>' +
        '<input type="url" name="mdaddress" id="mdaddress" placeholder="https://..." class="ui-corner-all" size="40" style="margin:0 auto;padding:0;width: 98%">' +
        /* Allow form submission with keyboard without duplicating the dialog button */
        '<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">' +
        '</form></div>'
    );
    $('#validator-dialog-form').dialog({
      width: 400,
      height: 'auto',
      modal: true,
      buttons: {
        Fetch: function() {
            var url = $( this ).find('input#mdaddress').val();
            $( this ).dialog('close');
            fetchFromURL(url);
            $( this ).remove();
        },
        Cancel: function() {
          $( this ).dialog('close');
          $( this ).remove();
        }
      },
      close: function() {
        $( this ).find('form')[0].reset();
      }
    });
}

/**
 * Create the basic DOM for the application, so users can just use a <div>
 */
function createValidatorDOM()
{
    return '<div id="metadata"></div>' +
        '<div id="progress"></div>' +
        '<div id="buttons">' +
            '<input id="validate" type="button" value="Validate!" class="validator-button"> ' +
            '<input id="normalise" type="button" value="Normalise" class="validator-button"> ' +
            '<input id="dcv" type="button" value="DCV" class="validator-button">' +
            '<div class="validator-right">' +
                '<input id="mdurl" type="button" value="Fetch URL..." class="validator-button"> ' +
                '<label for="mdfile">Upload file...</label>' +
                '<input id="mdfile" type="file" multiple=""> ' +
            '</div>' +
        '</div>' +
        '<div id="results" class="validator-hidden"></div>' +
        '<div id="spinner">' +
            '<div class="validator-bounce1"></div>' +
            '<div class="validator-bounce2"></div>' +
            '<div class="validator-bounce3"></div>' +
        '</div>';
}

$(document).ready(function ()
{
    $('#validator').html(createValidatorDOM());
    $('#validator input[type=button]').button();
    $('#validator #validate').focus();
    $('#validator label[for=mdfile]').button();
    $('#validator #progress').progressbar({ disabled: true });
    $('#validator #validate').attr("disabled", true);
    $('#validator #normalise').hide();
    $('#validator #dcv').hide();

    content = false;

    editor = ace.edit("metadata");
    editor.setTheme("ace/theme/xcode");
    editor.getSession().setMode("ace/mode/xml");
    editor.$blockScrolling = Infinity;
    editor.on('paste', function() { resetUI(); });
    editor.on('change', function() {
        if (editor.getValue().length) {
            if (! content) {
                $('#validator #validate').removeAttr("disabled");
                $('#validator #normalise').show();
                $('#validator #dcv').show();
                content = true;
            }
        } else {
            content = false;
            $('#validator #validate').attr("disabled", true);
            $('#validator #normalise').hide();
            $('#validator #dcv').hide();
        }
    });

    $('#validator #mdfile').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.readAsText(file);
            reader.onload = function(e) {
                editor.setValue(e.target.result);
                resetUI();
                editor.gotoLine(1, 0);
            };
        }
    });

    $('#validator #mdurl').click(function() {
        createFetchURLDialog();
    });

    $('#validator #validate').click(function() {
        sendForValidation();
    });

    $('#validator #normalise').click(function() {
        sendForNormalisation();
    });

    $('#validator #dcv').click(function() {
        sendForDCV();
    });

    /* Ajax global event handlers to display comfort throbber/spinner */
    $(document).ajaxStart(function() {
        /* delay starting the spinner, so we don't show it for quick functions */
        spinner = setTimeout(function() { $('#validator #spinner').show(); }, 350);
    });
    $(document).ajaxStop(function() {
        clearTimeout(spinner);
        $('#validator #spinner').hide();
    });
});
