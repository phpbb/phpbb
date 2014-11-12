plupload.addI18n(phpbb.plupload.i18n);
phpbb.plupload.ids = [];

(function($) {  // Avoid conflicts with other libraries

"use strict";

/**
 * Set up the uploader.
 *
 * @return undefined
 */
phpbb.plupload.initialize = function() {
	// Initialize the Plupload uploader.
	uploader.init();

	// Set attachment data.
	phpbb.plupload.setData(phpbb.plupload.data);
	phpbb.plupload.updateMultipartParams(phpbb.plupload.getSerializedData());

	// Only execute if Plupload initialized successfully.
	uploader.bind('Init', function() {
		phpbb.plupload.form = $(phpbb.plupload.config.form_hook)[0],
		phpbb.plupload.rowTpl = $('#attach-row-tpl')[0].outerHTML;

		// Hide the basic upload panel and remove the attach row template.
		$('#attach-row-tpl, #attach-panel-basic').remove();
		// Show multi-file upload options.
		$('#attach-panel-multi').show();
	});

	uploader.bind('PostInit', function() {
		// Point out the drag-and-drop zone if it's supported.
		if (uploader.features.dragdrop) {
			$('#drag-n-drop-message').show();
		}

		// Ensure "Add files" button position is correctly calculated.
		if ($('#attach-panel-multi').is(':visible')) {
			uploader.refresh();
		}
		$('[data-subpanel="attach-panel"]').one('click', function() {
			uploader.refresh();
		});
	});
};

/**
 * Unsets all elements in the object uploader.settings.multipart_params whose keys
 * begin with 'attachment_data['
 *
 * @return undefined
 */
phpbb.plupload.clearParams = function() {
	var obj = uploader.settings.multipart_params;
	for (var key in obj) {
		if (!obj.hasOwnProperty(key) || key.indexOf('attachment_data[') !== 0) {
			continue;
		}

		delete uploader.settings.multipart_params[key];
	}
};

/**
 * Update uploader.settings.multipart_params object with new data.
 *
 * @param object obj
 * @return undefined
 */
phpbb.plupload.updateMultipartParams = function(obj) {
	uploader.settings.multipart_params = $.extend(
		uploader.settings.multipart_params,
		obj
	);
};

/**
 * Convert the array of attachment objects into an object that PHP would expect as POST data.
 *
 * @return object An object in the form 'attachment_data[i][key]': value as
 * 	expected by the server
 */
phpbb.plupload.getSerializedData = function() {
	var obj = {};
	for (var i = 0; i < phpbb.plupload.data.length; i++) {
		var datum = phpbb.plupload.data[i];
		for (var key in datum) {
			if (!datum.hasOwnProperty(key)) {
				continue;
			}

			obj['attachment_data[' + i + '][' + key + ']'] = datum[key];
		}
	}
	return obj;
};

/**
 * Get the index from the phpbb.plupload.data array where the given
 * attachment id appears.
 *
 * @param int attach_id The attachment id of the file.
 * @return bool	Returns false if the id cannot be found.
 * @return int	Returns the index of the file if it exists.
 */
phpbb.plupload.getIndex = function(attach_id) {
	var index = $.inArray(Number(attach_id), phpbb.plupload.ids);
	return (index !== -1) ? index : false;
};

/**
 * Set the data in phpbb.plupload.data and phpbb.plupload.ids arrays.
 * 
 * @param array data	Array containing the new data to use. In the form of 
 * array(index => object(property: value). Requires attach_id to be one of the object properties.
 *
 * @return undefined
 */
phpbb.plupload.setData = function(data) {
	// Make sure that the array keys are reset.
	phpbb.plupload.ids = phpbb.plupload.data = [];
	phpbb.plupload.data = data;

	for (var i = 0; i < data.length; i++) {
		phpbb.plupload.ids.push(Number(data[i].attach_id));
	}
};

/**
 * Update the attachment data in the HTML and the phpbb & phpbb.plupload objects.
 * 
 * @param array data		Array containing the new data to use.
 * @param string action		The action that required the update. Used to update the inline attachment bbcodes.
 * @param int index			The index from phpbb.plupload_ids that was affected by the action.
 * @param array downloadUrl	Optional array of download urls to update.
 * @return undefined
 */
phpbb.plupload.update = function(data, action, index, downloadUrl) {

	phpbb.plupload.updateBbcode(action, index);
	phpbb.plupload.setData(data);
	phpbb.plupload.updateRows(downloadUrl);
	phpbb.plupload.clearParams();
	phpbb.plupload.updateMultipartParams(phpbb.plupload.getSerializedData());
};

/**
 * Update the relevant elements and hidden data for all attachments.
 * 
 * @param array downloadUrl	Optional array of download urls to update.
 * @return undefined
 */
phpbb.plupload.updateRows = function(downloadUrl) {
	for (var i = 0; i < phpbb.plupload.ids.length; i++) {
		phpbb.plupload.updateRow(i, downloadUrl);
	}
};

/**
 * Insert a row for a new attachment. This expects an HTML snippet in the HTML
 * using the id "attach-row-tpl" to be present. This snippet is cloned and the
 * data for the file inserted into it. The row is then appended or prepended to
 * #file-list based on the attach_order setting.
 * 
 * @param object file	Plupload file object for the new attachment.
 * @return undefined
 */
phpbb.plupload.insertRow = function(file) {
	var row = $(phpbb.plupload.rowTpl);

	row.attr('id', file.id);
	row.find('.file-name').html(plupload.xmlEncode(file.name));
	row.find('.file-size').html(plupload.formatSize(file.size));

	if (phpbb.plupload.order == 'desc') {
		$('#file-list').prepend(row);
	} else {
		$('#file-list').append(row);
	}
};

/**
 * Update the relevant elements and hidden data for an attachment.
 * 
 * @param int index	The index from phpbb.plupload.ids of the attachment to edit.
 * @param array downloadUrl	Optional array of download urls to update. 
 * @return undefined
 */
phpbb.plupload.updateRow = function(index, downloadUrl) {
	var attach = phpbb.plupload.data[index],
		row = $('[data-attach-id="' + attach.attach_id + '"]');

	// Add the link to the file
	if (typeof downloadUrl !== 'undefined' && typeof downloadUrl[index] !== 'undefined') {
		var url = downloadUrl[index].replace('&amp;', '&'),
			link = $('<a></a>');

		link.attr('href', url).html(attach.real_filename);
		row.find('.file-name').html(link)	
	}

	row.find('textarea').attr('name', 'comment_list[' + index + ']');
	phpbb.plupload.updateHiddenData(row, attach, index);
};

/**
 * Update hidden input data for an attachment.
 *
 * @param object row	jQuery object for the attachment row.
 * @param object attach	Attachment data object from phpbb.plupload.data
 * @param int index		Attachment index from phpbb.plupload.ids
 * @return undefined
 */
phpbb.plupload.updateHiddenData = function(row, attach, index) {
	row.find('input[type="hidden"]').remove();

	for (var key in attach) {
		var input = $('<input />')
			.attr('type', 'hidden')
			.attr('name', 'attachment_data[' + index + '][' + key +']')
			.attr('value', attach[key]);
		$('textarea', row).after(input);
	}
};

/**
 * Deleting a file removes it from the queue and fires an AJAX event to the
 * server to tell it to remove the temporary attachment. The server
 * responds with the updated attachment data list so that any future
 * uploads can maintain state with the server
 *
 * @param object row	jQuery object for the attachment row.
 * @param int attachId	Attachment id of the file to be removed.
 *
 * @return undefined
 */
phpbb.plupload.deleteFile = function(row, attachId) {
	// If there's no attach id, then the file hasn't been uploaded. Simply delete the row.
	if (typeof attachId === 'undefined') {
		var file = uploader.getFile(row.attr('id'));
		uploader.removeFile(file);

		row.slideUp(100, function() {
			row.remove();
			phpbb.plupload.hideEmptyList();
		});
	}

	var index = phpbb.plupload.getIndex(attachId);
	row.find('.file-status').toggleClass('file-uploaded file-working');

	if (index === false) {
		return;
	}
	var fields = {};
	fields['delete_file[' + index + ']'] = 1;

	var always = function() {
		row.find('.file-status').removeClass('file-working');
	};

	var done = function(response) {
		if (typeof response !== 'object') {
			return;
		}

		// trigger_error() was called which likely means a permission error was encountered.
		if (typeof response.title !== 'undefined') {
			uploader.trigger('Error', {message: response.message});
			// We will have to assume that the deletion failed. So leave the file status as uploaded.
			row.find('.file-status').toggleClass('file-uploaded');

			return;
		}
		phpbb.plupload.update(response, 'removal', index);
		// Check if the user can upload files now if he had reached the max files limit.
		phpbb.plupload.handleMaxFilesReached();

		if (row.attr('id')) {
			var file = uploader.getFile(row.attr('id'));
			uploader.removeFile(file);
		}
		row.slideUp(100, function() {
			row.remove();
			// Hide the file list if it's empty now.
			phpbb.plupload.hideEmptyList();
		});
		uploader.trigger('FilesRemoved');
	};

	$.ajax(phpbb.plupload.config.url, {
		type: 'POST',
		data: $.extend(fields, phpbb.plupload.getSerializedData()),
		headers: {'X-PHPBB-USING-PLUPLOAD': '1', 'X-Requested-With': 'XMLHttpRequest'}
	})
	.always(always)
	.done(done);
};

/**
 * Check the attachment list and hide its container if it's empty.
 *
 * @return undefined
 */
phpbb.plupload.hideEmptyList = function() {
	if (!$('#file-list').children().length) {
		$('#file-list-container').slideUp(100);
	}
}

/**
 * Update the indices used in inline attachment bbcodes. This ensures that the bbcodes
 * correspond to the correct file after a file is added or removed. This should be called 
 * before the phpbb.plupload,data and phpbb.plupload.ids arrays are updated, otherwise it will
 * not work correctly.
 *
 * @param string action	The action that occurred -- either "addition" or "removal"
 * @param int index		The index of the attachment from phpbb.plupload.ids that was affected.
 *
 * @return undefined
 */
phpbb.plupload.updateBbcode = function(action, index) {
	var	textarea = $('#message', phpbb.plupload.form),
		text = textarea.val(),
		removal = (action === 'removal');

	// Return if the bbcode isn't used at all.
	if (text.indexOf('[attachment=') === -1) {
		return;
	}

	// Private function used to replace the bbcode.
	var updateBbcode = function(match, fileName) {
		// Remove the bbcode if the file was removed.
		if (removal && index === i) {
			return '';
		}
		var newIndex = i + ((removal) ? -1 : 1);
		return '[attachment=' + newIndex +']' + fileName + '[/attachment]';
	};

	// Private function used to generate search regexp
	var searchRegexp = function(index) {
		return new RegExp('\\[attachment=' + index + '\\](.*?)\\[\\/attachment\\]', 'g');
	}
	// The update order of the indices is based on the action taken to ensure that we don't corrupt
	// the bbcode index by updating it several times as we move through the loop.
	// Removal loop starts at the removed index and moves to the end of the array.
	// Addition loop starts at the end of the array and moves to the added index at 0.
	var searchLoop = function() {
		if (typeof i === 'undefined') {
			i = (removal) ? index : phpbb.plupload.ids.length - 1;
		}
		return (removal) ? (i < phpbb.plupload.ids.length): (i >= index);
	}
	var i;

	while (searchLoop()) {
		text = text.replace(searchRegexp(i), updateBbcode);
		(removal) ? i++ : i--;
	}
	textarea.val(text);
};

/**
 * Get Plupload file objects based on their upload status.
 *
 * @param int status Plupload status - plupload.DONE, plupload.FAILED, plupload.QUEUED,
 * plupload.STARTED, plupload.STOPPED
 *
 * @return Returns an array of the Plupload file objects matching the status.
 */
phpbb.plupload.getFilesByStatus = function(status) {
	var files = [];

	$.each(uploader.files, function(i, file) {
		if (file.status === status) {
			files.push(file);
		}
	});
	return files;
}

/**
 * Check whether the user has reached the maximun number of files that he's allowed
 * to upload. If so, disables the uploader and marks the queued files as failed. Otherwise
 * makes sure that the uploader is enabled.
 *
 * @return bool Returns true if the limit has been reached. False if otherwise.
 */
phpbb.plupload.handleMaxFilesReached = function() {
	// If there is no limit, the user is an admin or moderator.
	if (!phpbb.plupload.maxFiles) {
		return false;
	}

	if (phpbb.plupload.maxFiles <= phpbb.plupload.ids.length) {
		// Fail the rest of the queue.
		phpbb.plupload.markQueuedFailed(phpbb.plupload.lang.TOO_MANY_ATTACHMENTS);
		// Disable the uploader.
		phpbb.plupload.disableUploader();
		uploader.trigger('Error', {message: phpbb.plupload.lang.TOO_MANY_ATTACHMENTS});

		return true;
	} else if(phpbb.plupload.maxFiles > phpbb.plupload.ids.length) {
		// Enable the uploader if the user is under the limit
		phpbb.plupload.enableUploader();
	}
	return false;
}

/**
 * Disable the uploader
 *
 * @return undefined
 */
phpbb.plupload.disableUploader = function() {
	$('#add_files').addClass('disabled');
	uploader.disableBrowse();
}

/**
 * Enable the uploader
 *
 * @return undefined
 */
phpbb.plupload.enableUploader = function() {
	$('#add_files').removeClass('disabled');
	uploader.disableBrowse(false);
}

/**
 * Mark all queued files as failed.
 *
 * @param string error Error message to present to the user.
 * @return undefined
 */
phpbb.plupload.markQueuedFailed = function(error) {
	var files = phpbb.plupload.getFilesByStatus(plupload.QUEUED);

	$.each(files, function(i, file) {
		$('#' + file.id).find('.file-progress').hide();
		phpbb.plupload.fileError(file, error);
	});
}

/**
 * Marks a file as failed and sets the error message for it.
 *
 * @param object file	Plupload file object that failed.
 * @param string error	Error message to present to the user.
 * @return undefined
 */
phpbb.plupload.fileError = function(file, error) {
	file.status = plupload.FAILED;
	file.error = error;
	$('#' + file.id).find('.file-status').addClass('file-error').attr({'data-error-title': phpbb.plupload.lang.ERROR, 'data-error-message': error});
}




/**
 * Set up the Plupload object and get some basic data.
 */
var	uploader = new plupload.Uploader(phpbb.plupload.config);
phpbb.plupload.initialize();




/**
 * Insert inline attachment bbcode.
 */
 $('#file-list').on('click', '.file-inline-bbcode', function(e) {
	var attachId = $(this).parents('.attach-row').attr('data-attach-id'),
		index = phpbb.plupload.getIndex(attachId);

	attach_inline(index, phpbb.plupload.data[index].real_filename);	
	e.preventDefault();
});

/**
 * Delete a file.
 */
$('#file-list').on('click', '.file-delete', function(e) {
	var row = $(this).parents('.attach-row'),
		attachId = row.attr('data-attach-id');

	phpbb.plupload.deleteFile(row, attachId);
	e.preventDefault();
});

/**
 * Display the error message for a particular file when the error icon is clicked.
 */
$('#file-list').on('click', '.file-error', function(e) {
	phpbb.alert($(this).attr('data-error-title'), $(this).attr('data-error-message'));
	e.preventDefault();
});

/**
 * Fires when an error occurs.
 */
uploader.bind('Error', function(up, error) {
	error.file.name = plupload.xmlEncode(error.file.name);

	// The error message that Plupload provides for these is vague, so we'll be more specific.
	if (error.code === plupload.FILE_EXTENSION_ERROR) {
		error.message = plupload.translate('Invalid file extension:') + ' ' + error.file.name;
	} else if (error.code === plupload.FILE_SIZE_ERROR) {
		error.message = plupload.translate('File too large:') + ' ' + error.file.name;
	}
	phpbb.alert(phpbb.plupload.lang.ERROR, error.message);
});

/**
 * Fires before a given file is about to be uploaded. This allows us to
 * send the real filename along with the chunk. This is necessary because
 * for some reason the filename is set to 'blob' whenever a file is chunked
 *
 * @param object up		The plupload.Uploader object
 * @param object file	The plupload.File object that is about to be
 * 	uploaded
 *
 * @return undefined
 */
uploader.bind('BeforeUpload', function(up, file) {
	if (phpbb.plupload.handleMaxFilesReached()) {
		return;
	}

	phpbb.plupload.updateMultipartParams({'real_filename': file.name});
});

/**
 * Fired when a single chunk of any given file is uploaded. This parses the
 * response from the server and checks for an error. If an error occurs it
 * is reported to the user and the upload of this particular file is halted
 *
 * @param object up			The plupload.Uploader object
 * @param object file		The plupload.File object whose chunk has just
 * 	been uploaded
 * @param object response	The response object from the server
 *
 * @return undefined
 */
uploader.bind('ChunkUploaded', function(up, file, response) {
	if (response.chunk >= response.chunks - 1) {
		return;
	}

	var json = {};
	try {
		json = $.parseJSON(response.response);
	} catch (e) {
		file.status = plupload.FAILED;
		up.trigger('FileUploaded', file, {
			response: JSON.stringify({
				error: {
					message: 'Error parsing server response.'
				}
			})
		});
	}

	// If trigger_error() was called, then a permission error likely occurred.
	if (typeof json.title !== 'undefined') {
		json.error = {message: json.message};
	}

	if (json.error) {
		file.status = plupload.FAILED;
		up.trigger('FileUploaded', file, {
			response: JSON.stringify({
				error: {
					message: json.error.message
				}
			})
		});
	}
});

/**
 * Fires when files are added to the queue.
 *
 * @return undefined
 */
uploader.bind('FilesAdded', function(up, files) {
	// Prevent unnecessary requests to the server if the user already uploaded
	// the maximum number of files allowed.
	if (phpbb.plupload.handleMaxFilesReached()) {
		return;
	}

	// Switch the active tab if the style supports it
	if (typeof activateSubPanel == 'function') {
		activateSubPanel('attach-panel');
	}

	// Show the file list if there aren't any files currently.
	if (!$('#file-list-container').is(':visible')) {
		$('#file-list-container').show(100);
	}

	$.each(files, function(i, file) {
		phpbb.plupload.insertRow(file);
	});

	up.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " .file-progress-bar").css('width', file.percent + '%');
		$('#file-total-progress-bar').css('width', up.total.percent + '%');
	});

	// Do not allow more files to be added to the running queue.
	phpbb.plupload.disableUploader();

	// Start uploading the files once the user has selected them.
	up.start();
});


/**
 * Fires when an entire file has been uploaded. It checks for errors
 * returned by the server otherwise parses the list of attachment data and
 * appends it to the next file upload so that the server can maintain state
 * with regards to the attachments in a given post
 *
 * @param object up			The plupload.Uploader object
 * @param object file		The plupload.File object that has just been
 * 	uploaded
 * @param string response	The response string from the server
 *
 * @return undefined
 */
uploader.bind('FileUploaded', function(up, file, response) {
	var json = {},
		row = $('#' + file.id),
		error;

	// Hide the progress indicator.
	row.find('.file-progress').hide();

	try {
		json = $.parseJSON(response.response);
	} catch (e) {
		error = 'Error parsing server response.';
	}

	// If trigger_error() was called, then a permission error likely occurred.
	if (typeof json.title !== 'undefined') {
		error = json.message;
		up.trigger('Error', {message: error});

		// The rest of the queue will fail.
		phpbb.plupload.markQueuedFailed(error);
	} else if (json.error) {
		error = json.error.message;
	}

	if (typeof error !== 'undefined') {
		phpbb.plupload.fileError(file, error);
	} else if (file.status === plupload.DONE) {
		file.attachment_data = json['data'][0];

		row.attr('data-attach-id', file.attachment_data.attach_id);
		row.find('.file-inline-bbcode').show();
		row.find('.file-status').addClass('file-uploaded');
		phpbb.plupload.update(json['data'], 'addition', 0, [json['download_url']]);
	}
});

/**
 * Fires when the entire queue of files have been uploaded. 
 *
 * @param object up		The plupload.Uploader object
 * @param array files	An array of plupload.File objects that have just
 * 	been uploaded as part of a queue
 *
 * @return undefined
 */
uploader.bind('UploadComplete', function(up, files) {
	// Hide the progress bar
	setTimeout(function() {
		$('#file-total-progress-bar').fadeOut(500, function() {
			$(this).css('width', 0).show();
		});
	}, 2000);

	// Re-enable the uploader
	phpbb.plupload.enableUploader();
});

})(jQuery); // Avoid conflicts with other libraries
