/* global phpbb */

(function($) {  // Avoid conflicts with other libraries

'use strict';

/**
 * The following callbacks are for reording items. row_down
 * is triggered when an item is moved down, and row_up is triggered when
 * an item is moved up. It moves the row up or down, and deactivates /
 * activates any up / down icons that require it (the ones at the top or bottom).
 */
phpbb.addAjaxCallback('row_down', function(res) {
	if (typeof res.success === 'undefined' || !res.success) {
		return;
	}

	var $firstTr = $(this).parents('tr'),
		$secondTr = $firstTr.next();

	$firstTr.insertAfter($secondTr);
});

phpbb.addAjaxCallback('row_up', function(res) {
	if (typeof res.success === 'undefined' || !res.success) {
		return;
	}

	var $secondTr = $(this).parents('tr'),
		$firstTr = $secondTr.prev();

	$secondTr.insertBefore($firstTr);
});

/**
 * This callback replaces activate links with deactivate links and vice versa.
 * It does this by replacing the text, and replacing all instances of "activate"
 * in the href with "deactivate", and vice versa.
 */
phpbb.addAjaxCallback('activate_deactivate', function(res) {
	var $this = $(this),
		newHref = $this.attr('href');

	$this.text(res.text);

	if (newHref.indexOf('deactivate') !== -1) {
		newHref = newHref.replace('deactivate', 'activate');
	} else {
		newHref = newHref.replace('activate', 'deactivate');
	}

	$this.attr('href', newHref);
});

/**
 * The removes the parent row of the link or form that triggered the callback,
 * and is good for stuff like the removal of forums.
 */
phpbb.addAjaxCallback('row_delete', function(res) {
	if (res.SUCCESS !== false) {
		$(this).parents('tr').remove();
	}
});

/**
 * Handler for submitting permissions form in chunks
 * This call will submit permissions forms in chunks of 5 fieldsets.
 */
function submitPermissions() {
	var $form = $('form#set-permissions'),
		fieldsetList = $form.find('fieldset[id^=perm]'),
		formDataSets = [],
		$submitAllButton = $form.find('input[type=submit][name^=action]')[0];

	$.each(fieldsetList, function (key, value) {
		if (key % 5 === 0) {
			formDataSets[Math.floor(key / 5)] = $form.find('fieldset#' + value.id).serialize();
		} else {
			formDataSets[Math.floor(key / 5)] += '&' + $form.find('fieldset#' + value.id).serialize();
		}
	});

	// Set proper start values for handling refresh of page
	var permissionSubmitSize = formDataSets.length,
		permissionRequestCount = 0,
		forumIds = [],
		permissionSubmitFailed = false;

	/**
	 * Handler for submitted permissions form chunk
	 *
	 * @param {object} res Object returned by AJAX call
	 */
	function handlePermissionReturn(res) {
		permissionRequestCount++;
		var $dark = $('#darkenwrapper');

		if (res.S_USER_WARNING) {
			phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
			permissionSubmitFailed = true;
		} else if (!permissionSubmitFailed && res.S_USER_NOTICE) {
			// Fill list of selected forums
			if (typeof res.REFRESH_DATA !== 'undefined') {
				$.each(res.REFRESH_DATA.url.split('&'), function (key, value){
					var forumIdMatch = value.match(/^forum_id\[\]=([0-9]+)$/);
					if (forumIdMatch !== null) {
						forumIds.push(forumIdMatch[1]);
						// Remove added forum IDs from refresh URL
						res.REFRESH_DATA.url = res.REFRESH_DATA.url.replace('&' + forumIdMatch[0], '');
					}
				});
			}

			// Display success message at the end of submitting the form
			if (permissionRequestCount >= permissionSubmitSize) {
				var $alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
				// Do not allow closing alert
				$dark.off('click');
				$alert.find('.alert_close').hide();

				if (typeof res.REFRESH_DATA !== 'undefined') {
					setTimeout(function () {
						// Create forum to submit using POST. This will prevent
						// exceeding the maximum length of URLs
						var form = '<form action="' + res.REFRESH_DATA.url + '" method="post">';
						$.each(forumIds, function (key, value) {
							form += '<input type="text" name="forum_id[]" value="' + value + '" />';
						});
						form += '</form>';
						$form = $(form);
						$('body').append($form);

						// Hide the alert even if we refresh the page, in case the user
						// presses the back button.
						$dark.fadeOut(phpbb.alertTime, function () {
							if (typeof $alert !== 'undefined') {
								$alert.hide();
							}
						});

						// Submit form
						$form.submit();
					}, res.REFRESH_DATA.time * 1000); // Server specifies time in seconds
				}
			}
		}
	}

	// Create AJAX request for each form data set
	$.each(formDataSets, function (key, formData) {
		$.ajax({
			url: $form.action,
			type: 'POST',
			data: formData + '&' + $submitAllButton.name + '=' + encodeURIComponent($submitAllButton.value) +
				'&creation_time=' + $form.find('input[type=hidden][name=creation_time]')[0].value +
				'&form_token=' + $form.find('input[type=hidden][name=form_token]')[0].value,
			success: handlePermissionReturn,
			error: handlePermissionReturn
		});
	});
}

$('[data-ajax]').each(function() {
	var $this = $(this),
		ajax = $this.attr('data-ajax');

	if (ajax !== 'false') {
		var fn = (ajax !== 'true') ? ajax : null;
		phpbb.ajaxify({
			selector: this,
			refresh: $this.attr('data-refresh') !== undefined,
			callback: fn
		});
	}
});

/**
* Automatically resize textarea
*/
$(function() {
	phpbb.resizeTextArea($('textarea:not(.no-auto-resize)'), {minHeight: 75});

	var $setPermissionsForm = $('form#set-permissions');
	if ($setPermissionsForm.length) {
		$setPermissionsForm.on('submit', function (e) {
			submitPermissions();
			e.preventDefault();
		});
	}
});


})(jQuery); // Avoid conflicts with other libraries
