plupload.addI18n(phpbb.plupload.i18n);
plupload.attachment_data = [];

/**
 * Returns the index of the plupload.attachment_data array where the given
 * attach id appears
 *
 * @param int id The attachment id of the file
 *
 * @return bool	Returns false if the id cannot be found
 * @return int	Returns the index in the main array where the attachment id
 * 	was found
 */
function phpbb_plupload_find_attachment_idx(id) {
	var data = plupload.attachment_data;
	for (var i = 0; i < data.length; i++) {
		if (data[i].attach_id == id) {
			return i;
		}
	}

	return false;
}

/**
 * Converts an array of objects into an object that PHP would expect as POST
 * data
 *
 * @return object An object in the form 'attachment_data[i][key]': value as
 * 	expected by the server
 */
function phpbb_plupload_attachment_data_serialize() {
	var obj = {};
	for (var i = 0; i < plupload.attachment_data.length; i++) {
		var datum = plupload.attachment_data[i];
		for (var key in datum) {
			if (!datum.hasOwnProperty(key)) {
				continue;
			}

			obj['attachment_data[' + i + '][' + key + ']'] = datum[key];
		}
	}

	return obj;
}

/**
 * Unsets all elements in an object whose keys begin with 'attachment_data['
 *
 * @param object The object to be cleared
 *
 * @return undefined
 */
function phpbb_plupload_clear_params(obj) {
	for (var key in obj) {
		if (!obj.hasOwnProperty(key) || key.indexOf('attachment_data[') !== 0) {
			continue;
		}

		delete obj[key];
	}
}

/**
 * Update hidden attachment inputs in posting form
 * Pre-existing hidden inputs will be removed by comparing the old attachment
 * data (old_data) to the new attachment data (data) that has been sent back
 * by plupload.
 *
 * @param object form Posting form
 * @param object data Current attachment_data
 * @param object old_date Previous attachment_data (before submission)
 *
 * @return void
 */
phpbb.update_hidden_attachment_inputs = function(form, data, old_data) {
	// Update already existing hidden inputs
	for (var i = 0; i < form.length; i++) {
		if (data.hasOwnProperty(form[i].name)) {
			form[i].value = data[form[i].name];
			delete data[form[i].name];
		} else if (typeof old_data !== 'undefined' && old_data.hasOwnProperty(form[i].name)) {
			var inputRegex = /\b^[a-z_]+[+[0-9]+]/;
			var inputName = inputRegex.exec(form[i].name);
			if (typeof inputName !== 'undefined' && inputName[0] !== '') {
				$("input[type='hidden'][name^='" + inputName[0] + "']").remove();
			}
		}
	}

	// Append new inputs
	for (var key in data) {
		if (!data.hasOwnProperty(key)) {
			continue;
		}

		var input = $('<input />')
			.attr('type', 'hidden')
			.attr('name', key)
			.attr('value', data[key]);
		$(form).append(input);
	}
}

jQuery(function($) {
	$(phpbb.plupload.config.element_hook).pluploadQueue(phpbb.plupload.config);
	var uploader = $(phpbb.plupload.config.element_hook).pluploadQueue();

	// Check the page for already-existing attachment data and add it to the
	// array
	var form = $(phpbb.plupload.config.form_hook)[0];
	for (var i = 0; i < form.length; i++) {
		if (form[i].name.indexOf('attachment_data[') !== 0) {
			continue;
		}
		
		var matches = form[i].name.match(/\[(\d+)\]\[([^\]]+)\]/);
		var index = matches[1];
		var property = matches[2];
		
		if (!plupload.attachment_data[index]) {
			plupload.attachment_data[index] = {};
		}
		
		plupload.attachment_data[index][property] = form[i].value;
		uploader.settings.multipart_params[form[i].name] = form[i].value;
	}

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
		up.settings.multipart_params = $.extend(
			up.settings.multipart_params,
			{'real_filename': file.name}
		);
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
		var json = {};
		try {
			json = $.parseJSON(response.response);
		} catch (e) {
			file.status = plupload.FAILED;
			file.error = 'Error parsing server response.'
		}

		if (json.error) {
			file.status = plupload.FAILED;
			file.error = json.error.message;
		} else if (file.status === plupload.DONE) {
			plupload.attachment_data = json;
			file.attachment_data = json[0];
			up.settings.multipart_params = $.extend(
				up.settings.multipart_params,
				phpbb_plupload_attachment_data_serialize()
			);
		}
	});

	/**
	 * Fires when the entire queue of files have been uploaded. It resets the
	 * 'add files' button to allow more files to be uploaded and also attaches
	 * several events to each row of the currently-uploaded files to facilitate
	 * deleting any one of the files.
	 *
	 * Deleting a file removes it from the queue and fires an ajax event to the
	 * server to tell it to remove the temporary attachment. The server
	 * responds with the updated attachment data list so that any future
	 * uploads can maintain state with the server
	 *
	 * @param object up		The plupload.Uploader object
	 * @param array files	An array of plupload.File objects that have just
	 * 	been uploaded as part of a queue
	 *
	 * @return undefined
	 */
	uploader.bind('UploadComplete', function(up, files) {
		$('.plupload_upload_status').css('display', 'none');
		$('.plupload_buttons').css('display', 'block');

		// Insert a bunch of hidden input elements containing the attachment
		// data so that the save/preview/submit buttons work as expected.
		var form = $(phpbb.plupload.config.form_hook)[0];
		var data = phpbb_plupload_attachment_data_serialize();

		phpbb.update_hidden_attachment_inputs(form, data);

		files.forEach(function(file) {
			if (file.status !== plupload.DONE) {
				var click = function(evt) {
					alert(file.error);
				}

				$('#' + file.id).attr('title', file.error);
				$('#' + file.id).click(click);

				return;
			}

			var click = function(evt) {
				$(evt.target).find('a').addClass('working');

				// The index is always found because file.attachment_data is
				// just an element of plupload.attachment_data
				var idx = phpbb_plupload_find_attachment_idx(file.attachment_data.attach_id);
				var fields = {};
				fields['delete_file[' + idx + ']'] = 1;

				var always = function() {
					$(evt.target).find('a').removeClass('working');
				};

				var done = function(response) {
					up.removeFile(file);
					plupload.attachment_data = response;
					phpbb.update_hidden_attachment_inputs(form, phpbb_plupload_attachment_data_serialize(), data);
					phpbb_plupload_clear_params(up.settings.multipart_params);
					up.settings.multipart_params = $.extend(
						up.settings.multipart_params,
						phpbb_plupload_attachment_data_serialize()
					);
				};
				
				$.ajax(phpbb.plupload.config.url, {
					type: 'POST',
					data: $.extend(fields, phpbb_plupload_attachment_data_serialize()),
					headers: {'X-PHPBB-USING-PLUPLOAD': '1'}
				})
				.always(always)
				.done(done);
			};
			
			$('#' + file.id)
			.addClass('can_delete')
			.click(click);
		});
	});
});
