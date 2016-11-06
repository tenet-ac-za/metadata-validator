/**
 * Javascript support functions for metadata-validator
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, SAFIRE - South African Identity Federation
 * @license https://opensource.org/licenses/MIT MIT License
 * @requires jQuery
 * @requires jquery-ui.js
 * @requires ace.js
 */
var editor;

/**
 * reset the results pane
 */
function resetUI()
{
    /* reset the UI */
    $('#validator').removeClass('invalid');
    $('#validator').removeClass('valid');
    $('#validator #results').html('<ul></ul>');
    $('#validator #results').addClass('hidden');
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
        '<span class="passdescr"> (' + data['passdescr'] + ')</span>' +
        ':</h3><ul></ul>'
    );
    $.each(data['errors'], function (k, err) {
        if (firstError == true && err['line'] > 0) {
            editor.gotoLine(err['line'], err['column'], true);
            firstError = false;
        }
        var msg = err['message'];
        if (msg.match(/\[ERROR\]/) || (err['code'] != 1 && err['level'] > 1)) {
            msg = '<span class="mesg-error">' + msg + '</span>';
        }
        if (msg.match(/\[WARN\]/) || (err['code'] != 1 && err['level'] == 1)) {
            msg = '<span class="mesg-warn">' + msg + '</span>';
        }
        if (msg.match(/\[INFO\]/)) {
            msg = '<span class="mesg-info">' + msg + '</span>';
        }
        if (err['line'] > 0) {
            msg = msg + ' <span class="linenum">[<a href="#" onclick="editor.gotoLine(' + err['line'] + ',' + err['column'] + ')">' + err['line'] + ', ' + err['column'] + '</a>]</span>';
        }
        $('#validator #results ul').append('<li>' + msg + '</li>');
    });
    $('#validator #results').removeClass('hidden');
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
        $('#validator').addClass('valid');
        $('#validator #progress').progressbar('option', 'value', 100);
        editor.gotoLine(1, 0);
    } else {
        populateResultsPane(data);
        $('#validator').addClass('invalid');
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

/*
 * Fetch metata from a URL (via a proxy)
 */
function fetchFromURL(url) {
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
function createFetchURLDialog() {
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

$(document).ready(function ()
{
    $('#validator input[type=button]').button();
    $('#validator #validate').focus();
    $('#validator label[for=mdfile]').button();
    $('#validator #progress').progressbar({ disabled: true });

    editor = ace.edit("metadata");
    editor.setTheme("ace/theme/xcode");
    editor.getSession().setMode("ace/mode/xml");
    editor.$blockScrolling = Infinity;
    editor.on('paste', function() { resetUI(); });

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
});
