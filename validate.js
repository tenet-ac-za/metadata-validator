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
var passes = 6; /* must match validate.php */

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
 * callback to handle the AJAX response from validation
 */
function validationResults(data)
{
	if (data['errors'] == null || data['success'] != false) {
		console.log("SUCCESS!");
		resetUI();
		$('#validator').addClass('valid');
		$('#validator #progress').progressbar('option', 'value', passes);
		editor.gotoLine(1, 0);
	} else {
		console.log(data);
		var firstError = true;
		$('#validator #progress').progressbar('option', 'value', data['pass']);
		$('#validator').addClass('invalid');
		$('#validator #results').html('<h3>Results from validation pass ' + data['pass'] + '/' + passes + ':</h3><ul></ul>');
		$.each(data['errors'], function (k, err) {
			if (firstError == true && err['line'] > 0) {
				editor.gotoLine(err['line'], err['column'], true);
				firstError = false;
			}
			var msg = err['message'];
			if (msg.match(/\[ERROR\]/)) {
				msg = '<span class="mesg-error">' + msg + '</span>';
			}
			if (msg.match(/\[WARN\]/)) {
				msg = '<span class="mesg-warn">' + msg + '</span>';
			}
			if (msg.match(/\[INFO\]/)) {
				msg = '<span class="mesg-info">' + msg + '</span>';
			}
			if (err['line'] > 0) {
				msg = msg + ' [<a href="#" onclick="editor.gotoLine(' + err['line'] + ',' + err['column'] + ')">' + err['line'] + ', ' + err['column'] + '</a>]';
			}
			$('#validator #results ul').append('<li>' + msg + '</li>');
		});
		$('#validator #results').removeClass('hidden');
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
		'<div id="validator-dialog-form" title="Enter address of metadata server"><form>' +
		'<input type="url" name="mdaddress" id="mdaddress" placeholder="https://..." class="text ui-widget-content ui-corner-all" size="40">' +
		/* Allow form submission with keyboard without duplicating the dialog button */
		'<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">' +
		'</form></div>'
	);
	$('#validator-dialog-form').dialog({
	  width: 400,
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
	$('#validator #progress').progressbar({ disabled: true, max: passes });

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
