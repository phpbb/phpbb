/* global phpbb */

/* eslint-disable camelcase, no-unused-vars */

(function ($) {  // Avoid conflicts with other libraries
	'use strict';

	phpbb.prepareSendStats = function () {
		const $form = $('#acp_help_phpbb');
		const $dark = $('#darkenwrapper');
		let $loadingIndicator;

		$form.on('submit', event => {
			const $this = $(this);
			const currentTime = Math.floor(new Date().getTime() / 1000);
			const statsTime = parseInt($this.find('input[name=help_send_statistics_time]').val(), 10);

			event.preventDefault();
			$this.unbind('submit');

			// Skip ajax request if form is submitted too early or send stats
			// checkbox is not checked
			if (!$this.find('input[name=help_send_statistics]').is(':checked') ||
			statsTime > currentTime) {
				$form.find('input[type=submit]').click();
				setTimeout(() => {
					$form.find('input[type=submit]').click();
				}, 300);
				return;
			}

			/**
			 * Handler for AJAX errors
			 */
			function errorHandler(jqXHR, textStatus, errorThrown) {
				if (typeof console !== 'undefined' && console.log) {
					console.log('AJAX error. status: ' + textStatus + ', message: ' + errorThrown);
				}
				phpbb.clearLoadingTimeout();
				let errorText = '';

				if (typeof errorThrown === 'string' && errorThrown.length > 0) {
					errorText = errorThrown;
				} else {
					errorText = $dark.attr('data-ajax-error-text-' + textStatus);
					if (typeof errorText !== 'string' || errorText.length === 0) {
						errorText = $dark.attr('data-ajax-error-text');
					}
				}
				phpbb.alert($dark.attr('data-ajax-error-title'), errorText);
			}

			/**
			 * This is a private function used to handle the callbacks, refreshes
			 * and alert. It calls the callback, refreshes the page if necessary, and
			 * displays an alert to the user and removes it after an amount of time.
			 *
			 * It cannot be called from outside this function, and is purely here to
			 * avoid repetition of code.
			 *
			 * @param {object} res The object sent back by the server.
			 */
			function returnHandler(res) {
				phpbb.clearLoadingTimeout();

				// If a confirmation is not required, display an alert and call the
				// callbacks.
				$dark.fadeOut(phpbb.alertTime);

				if ($loadingIndicator) {
					$loadingIndicator.fadeOut(phpbb.alertTime);
				}

				const $sendStatisticsSuccess = $('<input />', {
					type: 'hidden',
					name: 'send_statistics_response',
					value: res
				});
				$sendStatisticsSuccess.appendTo('p.submit-buttons');

				// Finish actual form submission
				$form.find('input[type=submit]').click();
			}

			$loadingIndicator = phpbb.loadingIndicator();

			$.ajax({
				url: $this.attr('data-ajax-action').replace('&amp;', '&'),
				type: 'POST',
				data: 'systemdata=' + encodeURIComponent($this.find('input[name=systemdata]').val()),
				success: returnHandler,
				error: errorHandler,
				cache: false
			}).always(() => {
				if ($loadingIndicator && $loadingIndicator.is(':visible')) {
					$loadingIndicator.fadeOut(phpbb.alertTime);
				}
			});
		});
	};

	/**
	 * The following callbacks are for reording items. row_down
	 * is triggered when an item is moved down, and row_up is triggered when
	 * an item is moved up. It moves the row up or down, and deactivates /
	 * activates any up / down icons that require it (the ones at the top or bottom).
	 */
	phpbb.addAjaxCallback('row_down', res => {
		if (typeof res.success === 'undefined' || !res.success) {
			return;
		}

		const $firstTr = $(this).parents('tr');
		const $secondTr = $firstTr.next();

		$firstTr.insertAfter($secondTr);
	});

	phpbb.addAjaxCallback('row_up', res => {
		if (typeof res.success === 'undefined' || !res.success) {
			return;
		}

		const $secondTr = $(this).parents('tr');
		const $firstTr = $secondTr.prev();

		$secondTr.insertBefore($firstTr);
	});

	/**
	 * This callback replaces activate links with deactivate links and vice versa.
	 * It does this by replacing the text, and replacing all instances of "activate"
	 * in the href with "deactivate", and vice versa.
	 */
	phpbb.addAjaxCallback('activate_deactivate', res => {
		const $this = $(this);
		let newHref = $this.attr('href');

		$this.text(res.text);

		if (newHref.indexOf('deactivate') === -1) {
			newHref = newHref.replace('activate', 'deactivate');
		} else {
			newHref = newHref.replace('deactivate', 'activate');
		}

		$this.attr('href', newHref);
	});

	/**
	 * The removes the parent row of the link or form that triggered the callback,
	 * and is good for stuff like the removal of forums.
	 */
	phpbb.addAjaxCallback('row_delete', res => {
		if (res.SUCCESS !== false) {
			$(this).parents('tr').remove();
		}
	});

	/**
	 * Handler for submitting permissions form in chunks
	 * This call will submit permissions forms in chunks of 5 fieldsets.
	 */
	function submitPermissions() {
		let $form = $('form#set-permissions');
		let fieldsetList = $form.find('fieldset[id^=perm]');
		const formDataSets = [];
		let dataSetIndex = 0;
		const $submitAllButton = $form.find('input[type=submit][name^=action]')[0];
		const $submitButton = $form.find('input[type=submit][data-clicked=true]')[0];

		// Set proper start values for handling refresh of page
		let permissionSubmitSize = 0;
		let permissionRequestCount = 0;
		const forumIds = [];
		let permissionSubmitFailed = false;

		if ($submitAllButton !== $submitButton) {
			fieldsetList = $form.find('fieldset#' + $submitButton.closest('fieldset.permissions').id);
		}

		$.each(fieldsetList, (key, value) => {
			dataSetIndex = Math.floor(key / 5);
			const $fieldset = $('fieldset#' + value.id);
			if (key % 5 === 0) {
				formDataSets[dataSetIndex] = $fieldset.find('select:visible, input:not([data-name])').serialize();
			} else {
				formDataSets[dataSetIndex] += '&' + $fieldset.find('select:visible, input:not([data-name])').serialize();
			}

			// Find proper role value
			const roleInput = $fieldset.find('input[name^=role][data-name]');
			if (roleInput.val()) {
				formDataSets[dataSetIndex] += '&' + roleInput.attr('name') + '=' + roleInput.val();
			} else {
				formDataSets[dataSetIndex] += '&' + roleInput.attr('name') + '=' +
				$fieldset.find('select[name="' + roleInput.attr('name') + '"]').val();
			}
		});

		permissionSubmitSize = formDataSets.length;

		// Add each forum ID to forum ID list to preserve selected forums
		$.each($form.find('input[type=hidden][name^=forum_id]'), (key, value) => {
			if (value.name.match(/^forum_id\[([0-9]+)\]$/)) {
				forumIds.push(value.value);
			}
		});

		/**
		 * Handler for submitted permissions form chunk
		 *
		 * @param {object} res Object returned by AJAX call
		 */
		function handlePermissionReturn(res) {
			permissionRequestCount++;
			const $dark = $('#darkenwrapper');

			if (res.S_USER_WARNING) {
				phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
				permissionSubmitFailed = true;
			} else if (!permissionSubmitFailed && res.S_USER_NOTICE) {
				// Display success message at the end of submitting the form
				if (permissionRequestCount >= permissionSubmitSize) {
					const $alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
					const $alertBoxLink = $alert.find('p.alert_text > a');

					// Create form to submit instead of normal "Back to previous page" link
					if ($alertBoxLink) {
						// Remove forum_id[] from URL
						$alertBoxLink.attr('href', $alertBoxLink.attr('href').replace(/(&forum_id\[\]=[0-9]+)/g, ''));
						let previousPageForm = '<form action="' + $alertBoxLink.attr('href') + '" method="post">';
						$.each(forumIds, (key, value) => {
							previousPageForm += '<input type="text" name="forum_id[]" value="' + value + '" />';
						});
						previousPageForm += '</form>';

						$alertBoxLink.on('click', e => {
							const $previousPageForm = $(previousPageForm);
							$('body').append($previousPageForm);
							e.preventDefault();
							$previousPageForm.submit();
						});
					}

					// Do not allow closing alert
					$dark.off('click');
					$alert.find('.alert_close').hide();

					if (typeof res.REFRESH_DATA !== 'undefined') {
						setTimeout(() => {
							// Create forum to submit using POST. This will prevent
							// exceeding the maximum length of URLs
							let form = '<form action="' + res.REFRESH_DATA.url.replace(/(&forum_id\[\]=[0-9]+)/g, '') + '" method="post">';
							$.each(forumIds, (key, value) => {
								form += '<input type="text" name="forum_id[]" value="' + value + '" />';
							});
							form += '</form>';
							$form = $(form);
							$('body').append($form);

							// Hide the alert even if we refresh the page, in case the user
							// presses the back button.
							$dark.fadeOut(phpbb.alertTime, () => {
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
		$.each(formDataSets, (key, formData) => {
			$.ajax({
				url: $form.action,
				type: 'POST',
				data: formData + '&' + $submitButton.name + '=' + encodeURIComponent($submitButton.value) +
				'&creation_time=' + $form.find('input[type=hidden][name=creation_time]')[0].value +
				'&form_token=' + $form.find('input[type=hidden][name=form_token]')[0].value +
				'&' + $form.children('input[type=hidden]').serialize() +
				'&' + $form.find('input[type=checkbox][name^=inherit]').serialize(),
				success: handlePermissionReturn,
				error: handlePermissionReturn
			});
		});
	}

	$('[data-ajax]').each(() => {
		const $this = $(this);
		const ajax = $this.attr('data-ajax');

		if (ajax !== 'false') {
			const fn = (ajax === 'true') ? null : ajax;
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
	phpbb.resizeTextArea($('textarea:not(.no-auto-resize)'), {minHeight: 75});

	const $setPermissionsForm = $('form#set-permissions');
	if ($setPermissionsForm.length !== 0) {
		$setPermissionsForm.on('submit', e => {
			submitPermissions();
			e.preventDefault();
		});
		$setPermissionsForm.find('input[type=submit]').click(function () {
			$('input[type=submit]', $(this).parents($('form#set-permissions'))).removeAttr('data-clicked');
			$(this).attr('data-clicked', true);
		});
	}

	if ($('#acp_help_phpbb')) {
		phpbb.prepareSendStats();
	}
})(jQuery); // Avoid conflicts with other libraries

/* eslint-enable camelcase, no-unused-vars */
