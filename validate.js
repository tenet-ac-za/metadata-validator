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

function validationResults(data)
{
	if (data['errors'] == null || data['success'] != false) {
		console.log("SUCCESS!");
		resetUI();
		$('#validator').addClass('valid');
		$('#validator #progress').progressbar('option', 'value', passes);
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
			$('#validator #results ul').append('<li>' +
				err['message'] +
				' [<a href="#" onclick="editor.gotoLine(' + err['line'] + ',' + err['column'] + ')">' +
				err['line'] + ', ' + err['column'] + '</a>]</li>');
		});
		$('#validator #results').removeClass('hidden');
	}
}

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

$(document).ready(function ()
{
	$('#validator input[type=button]').button();
	$('#validator label[for=mdfile]').button();
	$('#validator #progress').progressbar({ disabled: true, max: passes });

	editor = ace.edit("metadata");
    editor.setTheme("ace/theme/xcode");
    editor.getSession().setMode("ace/mode/xml");
	editor.on('paste', function() { resetUI(); });

	$('#validator #mdfile').change(function() {
		var file = this.files[0];
		if (file) {
			var reader = new FileReader();
			reader.readAsText(file);
			reader.onload = function(e) {
				editor.setValue(e.target.result);
				resetUI();
			};
		}
	});

	$('#validator #validate').click(function() {
		sendForValidation();
	});
});
