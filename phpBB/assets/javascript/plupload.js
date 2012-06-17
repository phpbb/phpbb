plupload.addI18n(plupload.phpbb.i18n);
plupload.attachment_data = [];

/**
 * Returns the index of the plupload.attachment_data array where the given
 * attach id appears
 */
function plupload_find_attachment_idx(id)
{
	var data = plupload.attachment_data;
	for (var i = 0; i < data.length; i++)
	{
		if (data[i].id === id)
		{
			return i;
		}
	}

	return false;
}

/**
 * Converts an array of objects into an object that PHP would expect as POST
 * data
 */
function plupload_attachment_data_serialize()
{
	var obj = {};
	for (var i = 0; i < plupload.attachment_data.length; i++)
	{
		var datum = plupload.attachment_data[i];
		for (var key in datum)
		{
			if (!datum.hasOwnProperty(key))
			{
				continue;
			}

			obj['attachment_data[' + i + '][' + key + ']'] = datum[key];
		}
	}

	return obj;
}

/**
 * Unsets all elements in an object whose keys begin with 'attachment_data['
 */
function plupload_clear_params(obj)
{
	for (var key in obj)
	{
		if (!obj.hasOwnProperty(key) || key.indexOf('attachment_data[') !== 0)
		{
			continue;
		}

		delete obj[key];
	}
}

jQuery(function($) {
	$(plupload.phpbb.config.element_hook).pluploadQueue(plupload.phpbb.config);
	var uploader = $(plupload.phpbb.config.element_hook).pluploadQueue();

	/**
	 * Fired when a single chunk of any given file is uploaded. This parses the
	 * response from the server and checks for an error. If an error occurs it
	 * is reported to the user and the upload of this particular file is halted
	 */
	uploader.bind('ChunkUploaded', function(up, file, response) {
		if (response.chunk >= response.chunks - 1)
		{
			return;
		}

		var json = {};
		try
		{
			json = $.parseJSON(response.response);
		} catch (e) {
			if (console && console.log)
			{
				console.log('Error parsing server response.');
				console.log(response);
			}
		}
		
		if (json.error)
		{
			file.status = plupload.FAILED;
			alert(json.error.message);
			uploader.trigger('FileUploaded', up, file);
		}
	});

	/**
	 * Fires when an entire file has been uploaded. It checks for errors
	 * returned by the server otherwise parses the list of attachment data and
	 * appends it to the next file upload so that the server can maintain state
	 * with regards to the attachments in a given post
	 */
	uploader.bind('FileUploaded', function(up, file, response) {
		var json = {};
		try
		{
			json = $.parseJSON(response.response);
		} catch (e) {
			if (console && console.log)
			{
				console.log('Error parsing server response.');
				console.log(response);
			}
		}
		
		if (json.error)
		{
			file.status = plupload.FAILED;
			alert(json.error.message);
		}
		else if (file.status === plupload.DONE)
		{
			plupload.attachment_data = json;
			file.attachment_data = json[0];
			up.settings.multipart_params = $.extend(
				up.settings.multipart_params,
				plupload_attachment_data_serialize()
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
	 */
	uploader.bind('UploadComplete', function(up, files) {
		$('.plupload_upload_status').css('display', 'none');
		$('.plupload_buttons').css('display', 'block');

		files.forEach(function(file) {
			var mouseenter = function(evt)
			{
				$(evt.target).attr('class', 'plupload_delete');
				$(evt.target).css('cursor', 'pointer');
			};

			var mouseleave = function(evt)
			{
				$(evt.target).attr('class', 'plupload_done');
			};

			var click = function(evt)
			{
				var throbber = "url('" + plupload.phpbb.config.img_path + "/throbber.gif')";
				$(evt.target).find('a').css('background', throbber);
				
				var idx = plupload_find_attachment_idx(file.attachment_data.id);
				var fields = {};
				fields['delete_file[' + idx + ']'] = 1;

				var always = function()
				{
					$(evt.target).find('a').css('background', '');
				};

				var done = function(response)
				{
					up.removeFile(file);
					plupload.attachment_data = response;
					plupload_clear_params(up.settings.multipart_params);
					up.settings.multipart_params = $.extend(
						up.settings.multipart_params,
						plupload_attachment_data_serialize()
					);
				};
				
				$.ajax(plupload.phpbb.config.url, {
					type: 'POST',
					data: $.extend(fields, plupload_attachment_data_serialize()),
					headers: {'X-PHPBB-USING-PLUPLOAD': '1'}
				})
				.always(always)
				.done(done);
			};
			
			$('#' + file.id)
			.mouseenter(mouseenter)
			.mouseleave(mouseleave)
			.click(click);
		});
	});
});
