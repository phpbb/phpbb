/* global jQuery, window, document, location, installLang, XMLHttpRequest */

/* eslint-disable camelcase, no-unused-vars, no-prototype-builtins */

/**
 * Installer's AJAX frontend handler
 */

(function ($) { // Avoid conflicts with other libraries
	'use strict';

	// Installer variables
	let pollTimer = null;
	let nextReadPosition = 0;
	let progressBarTriggered = false;
	let progressTimer = null;
	let currentProgress = 0;
	let refreshRequested = false;
	let transmissionOver = false;
	let statusCount = 0;

	// Template related variables
	const $contentWrapper = $('.install-body').find('.main');

	// Intercept form submits
	interceptFormSubmit($('#install_install'));

	/**
	 * Creates an XHR object
	 *
	 * jQuery cannot be used as the response is streamed, and
	 * as of now, jQuery does not provide access to the response until
	 * the connection is not closed.
	 *
	 * @return XMLHttpRequest
	 */
	function createXhrObject() {
		return new XMLHttpRequest();
	}

	/**
	 * Displays error, warning and log messages
	 *
	 * @param type
	 * @param messages
	 */
	function addMessage(type, messages) {
		// Get message containers
		const $errorContainer = $('#error-container');
		const $warningContainer = $('#warning-container');
		const $logContainer = $('#log-container');

		let $title;
		let $description;
		let $msgElement;
		const arraySize = messages.length;

		for (let i = 0; i < arraySize; i++) {
			$msgElement = $('<div />');
			$title = $('<strong />');
			$title.text(messages[i].title);
			$msgElement.append($title);

			if (messages[i].hasOwnProperty('description')) {
				$description = $('<p />');
				$description.html(messages[i].description);
				$msgElement.append($description);
			}

			switch (type) {
				case 'error':
					$msgElement.addClass('errorbox');
					$errorContainer.append($msgElement);
					break;
				case 'warning':
					$msgElement.addClass('warningbox');
					$warningContainer.append($msgElement);
					break;
				case 'log':
					$msgElement.addClass('log');
					$logContainer.prepend($msgElement);
					$logContainer.addClass('show_log_container');
					break;
				case 'success':
					$msgElement.addClass('successbox');
					$errorContainer.prepend($msgElement);
					break;
				default:
					// Do nothing.
			}
		}
	}

	/**
	 * Render a download box
	 */
	function addDownloadBox(downloadArray)	{
		const $downloadContainer = $('#download-wrapper');
		let $downloadBox;
		let $title;
		let $content;
		let $link;

		for (let i = 0; i < downloadArray.length; i++) {
			$downloadBox = $('<div />');
			$downloadBox.addClass('download-box');

			$title = $('<strong />');
			$title.text(downloadArray[i].title);
			$downloadBox.append($title);

			if (downloadArray[i].hasOwnProperty('msg')) {
				$content = $('<p />');
				$content.text(downloadArray[i].msg);
				$downloadBox.append($content);
			}

			$link = $('<a />');
			$link.addClass('button1');
			$link.attr('href', downloadArray[i].href);
			$link.text(downloadArray[i].download);
			$downloadBox.append($link);

			$downloadContainer.append($downloadBox);
		}
	}

	/**
	 * Render update files' status
	 */
	function addUpdateFileStatus(fileStatus)	{
		const $statusContainer = $('#file-status-wrapper');
		$statusContainer.html(fileStatus);
	}

	/**
	 * Displays a form from the response
	 *
	 * @param formHtml
	 */
	function addForm(formHtml) {
		const $formContainer = $('#form-wrapper');
		$formContainer.html(formHtml);
		const $form = $('#install_install');
		interceptFormSubmit($form);
	}

	/**
	 * Handles navigation status updates
	 *
	 * @param navObj
	 */
	function updateNavbarStatus(navObj) {
		let navID;
		let $stage;
		let $stageListItem;
		const $active = $('#activemenu');

		if (navObj.hasOwnProperty('finished')) {
			// This should be an Array
			const navItems = navObj.finished;

			for (let i = 0; i < navItems.length; i++) {
				navID = 'installer-stage-' + navItems[i];
				$stage = $('#' + navID);
				$stageListItem = $stage.parent();

				if ($active.length !== 0 && $active.is($stageListItem)) {
					$active.removeAttr('id');
				}

				$stage.addClass('completed');
			}
		}

		if (navObj.hasOwnProperty('active')) {
			navID = 'installer-stage-' + navObj.active;
			$stage = $('#' + navID);
			$stageListItem = $stage.parent();

			if ($active.length !== 0 && !$active.is($stageListItem)) {
				$active.removeAttr('id');
			}

			$stageListItem.attr('id', 'activemenu');
		}
	}

	/**
	 * Renders progress bar
	 *
	 * @param progressObject
	 */
	function setProgress(progressObject) {
		let $statusText;
		let $progressBar;
		let $progressText;
		let $progressFiller;
		let $progressFillerText;

		if (progressObject.task_name.length !== 0) {
			if (!progressBarTriggered) {
				// Create progress bar
				const $progressBarWrapper = $('#progress-bar-container');

				// Create progress bar elements
				$progressBar = $('<div />');
				$progressBar.attr('id', 'progress-bar');
				$progressText = $('<p />');
				$progressText.attr('id', 'progress-bar-text');
				$progressFiller = $('<div />');
				$progressFiller.attr('id', 'progress-bar-filler');
				$progressFillerText = $('<p />');
				$progressFillerText.attr('id', 'progress-bar-filler-text');

				$statusText = $('<p />');
				$statusText.attr('id', 'progress-status-text');

				$progressFiller.append($progressFillerText);
				$progressBar.append($progressText);
				$progressBar.append($progressFiller);

				$progressBarWrapper.append($statusText);
				$progressBarWrapper.append($progressBar);

				$progressFillerText.css('width', $progressBar.width());

				progressBarTriggered = true;
			} else if (progressObject.hasOwnProperty('restart')) {
				clearInterval(progressTimer);

				$progressFiller = $('#progress-bar-filler');
				$progressFillerText = $('#progress-bar-filler-text');
				$progressText = $('#progress-bar-text');
				$statusText = $('#progress-status-text');

				$progressText.text('0%');
				$progressFillerText.text('0%');
				$progressFiller.css('width', '0%');

				currentProgress = 0;
			} else {
				$statusText = $('#progress-status-text');
			}

			// Update progress bar
			$statusText.text(progressObject.task_name + '…');
			incrementProgressBar(Math.round(progressObject.task_num / progressObject.task_count * 100));
		}
	}

	// Set cookies
	function setCookies(cookies) {
		let cookie;

		for (let i = 0; i < cookies.length; i++) {
			// Set cookie name and value
			cookie = encodeURIComponent(cookies[i].name) + '=' + encodeURIComponent(cookies[i].value);
			// Set path
			cookie += '; path=/';
			document.cookie = cookie;
		}
	}

	// Redirects user
	function redirect(url, use_ajax) {
		if (use_ajax) {
			resetPolling();

			const xhReq = createXhrObject();
			xhReq.open('GET', url, true);
			xhReq.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhReq.send();

			startPolling(xhReq);
		} else {
			window.location.href = url;
		}
	}

	/**
	 * Parse messages from the response object
	 *
	 * @param messageJSON
	 */
	function parseMessage(messageJSON) {
		$('#loading_indicator').css('display', 'none');
		let responseObject;

		try {
			responseObject = JSON.parse(messageJSON);
		} catch (err) {
			console.log('Failed to parse JSON object\n\nMessage: ' + err.message + '\n\nServer Response: ' + messageJSON);
			resetPolling();
			return;
		}

		// Parse object
		if (responseObject.hasOwnProperty('errors')) {
			addMessage('error', responseObject.errors);
		}

		if (responseObject.hasOwnProperty('warnings')) {
			addMessage('warning', responseObject.warnings);
		}

		if (responseObject.hasOwnProperty('logs')) {
			addMessage('log', responseObject.logs);
		}

		if (responseObject.hasOwnProperty('success')) {
			addMessage('success', responseObject.success);
		}

		if (responseObject.hasOwnProperty('form')) {
			addForm(responseObject.form);
		}

		if (responseObject.hasOwnProperty('progress')) {
			setProgress(responseObject.progress);
		}

		if (responseObject.hasOwnProperty('download')) {
			addDownloadBox(responseObject.download);
		}

		if (responseObject.hasOwnProperty('file_status')) {
			addUpdateFileStatus(responseObject.file_status);
		}

		if (responseObject.hasOwnProperty('nav')) {
			updateNavbarStatus(responseObject.nav);
		}

		if (responseObject.hasOwnProperty('cookies')) {
			setCookies(responseObject.cookies);
		}

		if (responseObject.hasOwnProperty('refresh')) {
			refreshRequested = true;
		}

		if (responseObject.hasOwnProperty('redirect')) {
			redirect(responseObject.redirect.url, responseObject.redirect.use_ajax);
		}

		if (responseObject.hasOwnProperty('over')) {
			if (responseObject.over) {
				transmissionOver = true;
			}
		}
	}

	/**
	 * Processes status data
	 *
	 * @param status
	 */
	function processTimeoutResponse(status) {
		if (statusCount === 12) { // 1 minute hard cap
			status = 'fail';
		}

		if (status === 'continue') {
			refreshRequested = false;
			doRefresh();
		} else if (status === 'running') {
			statusCount++;
			$('#loading_indicator').css('display', 'block');
			setTimeout(queryInstallerStatus, 5000);
		} else {
			$('#loading_indicator').css('display', 'none');
			addMessage('error',
				[{
					title: installLang.title,
					description: installLang.msg
				}]
			);
		}
	}

	/**
	 * Queries the installer's status
	 */
	function queryInstallerStatus() {
		let url = $(location).attr('pathname');
		let lookUp = 'install/app.php';
		let position = url.indexOf(lookUp);

		if (position === -1) {
			lookUp = 'install';
			position = url.indexOf(lookUp);

			if (position === -1) {
				return false;
			}
		}

		url = url.substring(0, position) + lookUp + '/installer/status';
		$.getJSON(url, data => {
			processTimeoutResponse(data.status);
		});
	}

	/**
	 * Process updates in streamed response
	 *
	 * @param xhReq   XHR object
	 */
	function pollContent(xhReq) {
		const messages = xhReq.responseText;
		const msgSeparator = '}\n\n';
		let unprocessed;
		let messageEndIndex;
		let endOfMessageIndex;
		let message;

		do {
			unprocessed = messages.substring(nextReadPosition);
			messageEndIndex = unprocessed.indexOf(msgSeparator);

			if (messageEndIndex !== -1) {
				endOfMessageIndex = messageEndIndex + msgSeparator.length;
				message = unprocessed.substring(0, endOfMessageIndex);
				parseMessage($.trim(message));
				nextReadPosition += endOfMessageIndex;
			}
		} while (messageEndIndex !== -1);

		if (xhReq.readyState === 4) {
			$('#loading_indicator').css('display', 'none');
			resetPolling();

			const timeoutDetected = !transmissionOver;

			if (refreshRequested) {
				refreshRequested = false;
				doRefresh();
			}

			if (timeoutDetected) {
				statusCount = 0;
				queryInstallerStatus();
			}
		}
	}

	/**
	 * Animates the progress bar
	 *
	 * @param $progressText
	 * @param $progressFiller
	 * @param $progressFillerText
	 * @param progressLimit
	 */
	function incrementFiller($progressText, $progressFiller, $progressFillerText, progressLimit) {
		if (currentProgress >= progressLimit || currentProgress >= 100) {
			clearInterval(progressTimer);
			return;
		}

		const $progressBar = $('#progress-bar');

		currentProgress++;
		$progressFillerText.css('width', $progressBar.width());
		$progressFillerText.text(currentProgress + '%');
		$progressText.text(currentProgress + '%');
		$progressFiller.css('width', currentProgress + '%');
	}

	/**
	 * Wrapper function for progress bar rendering and animating
	 *
	 * @param progressLimit
	 */
	function incrementProgressBar(progressLimit) {
		const $progressFiller = $('#progress-bar-filler');
		const $progressFillerText = $('#progress-bar-filler-text');
		const $progressText = $('#progress-bar-text');
		const progressStart = $progressFiller.width() / $progressFiller.offsetParent().width() * 100;
		currentProgress = Math.floor(progressStart);

		clearInterval(progressTimer);
		progressTimer = setInterval(() => {
			incrementFiller($progressText, $progressFiller, $progressFillerText, progressLimit);
		}, 10);
	}

	/**
	 * Resets the polling timer
	 */
	function resetPolling() {
		clearInterval(pollTimer);
		nextReadPosition = 0;
	}

	/**
	 * Sets up timer for processing the streamed HTTP response
	 *
	 * @param xhReq
	 */
	function startPolling(xhReq) {
		resetPolling();
		transmissionOver = false;
		pollTimer = setInterval(() => {
			pollContent(xhReq);
		}, 250);
	}

	/**
	 * Refresh page
	 */
	function doRefresh() {
		resetPolling();

		const xhReq = createXhrObject();
		xhReq.open('GET', $(location).attr('pathname'), true);
		xhReq.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhReq.send();

		startPolling(xhReq);
	}

	/**
	 * Renders the AJAX UI layout
	 */
	function setupAjaxLayout() {
		progressBarTriggered = false;

		// Clear content
		$contentWrapper.html('');

		const $header = $('<div />');
		$header.attr('id', 'header-container');
		$contentWrapper.append($header);

		const $description = $('<div />');
		$description.attr('id', 'description-container');
		$contentWrapper.append($description);

		const $errorContainer = $('<div />');
		$errorContainer.attr('id', 'error-container');
		$contentWrapper.append($errorContainer);

		const $warningContainer = $('<div />');
		$warningContainer.attr('id', 'warning-container');
		$contentWrapper.append($warningContainer);

		const $progressContainer = $('<div />');
		$progressContainer.attr('id', 'progress-bar-container');
		$contentWrapper.append($progressContainer);

		const $logContainer = $('<div />');
		$logContainer.attr('id', 'log-container');
		$contentWrapper.append($logContainer);

		const $installerContentWrapper = $('<div />');
		$installerContentWrapper.attr('id', 'content-container');
		$contentWrapper.append($installerContentWrapper);

		const $installerDownloadWrapper = $('<div />');
		$installerDownloadWrapper.attr('id', 'download-wrapper');
		$installerContentWrapper.append($installerDownloadWrapper);

		const $updaterFileStatusWrapper = $('<div />');
		$updaterFileStatusWrapper.attr('id', 'file-status-wrapper');
		$installerContentWrapper.append($updaterFileStatusWrapper);

		const $formWrapper = $('<div />');
		$formWrapper.attr('id', 'form-wrapper');
		$installerContentWrapper.append($formWrapper);

		const $spinner = $('<div />');
		$spinner.attr('id', 'loading_indicator');
		$spinner.html('&nbsp;');
		$contentWrapper.append($spinner);
	}

	// Submits a form
	function submitForm($form, $submitBtn) {
		$form.css('display', 'none');

		const xhReq = createXhrObject();
		xhReq.open('POST', $form.attr('action'), true);
		xhReq.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhReq.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhReq.send(getFormFields($form, $submitBtn));

		// Disable language selector
		$('#language_selector :input, label').css('display', 'none');

		// Clear content
		setupAjaxLayout();
		$('#loading_indicator').css('display', 'block');

		startPolling(xhReq);
	}

	/**
	 * Add submit button to the POST information
	 *
	 * @param $form
	 * @param $submitBtn
	 *
	 * @returns {*}
	 */
	function getFormFields($form, $submitBtn) {
		let formData = $form.serialize();
		formData += ((formData.length) ? '&' : '') + encodeURIComponent($submitBtn.attr('name')) + '=';
		formData += encodeURIComponent($submitBtn.attr('value'));

		return formData;
	}

	/**
	 * Intercept form submit events and determine the submit button used
	 *
	 * @param $form
	 */
	function interceptFormSubmit($form) {
		if ($form.length === 0) {
			return;
		}

		$form.find(':submit').bind('click', function (event) {
			event.preventDefault();
			submitForm($form, $(this));
		});
	}
})(jQuery); // Avoid conflicts with other libraries

/* eslint-enable camelcase, no-unused-vars, no-prototype-builtins */
